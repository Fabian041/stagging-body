<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Arsip hasil generate kanban ke storage/app/kanban-source/ dengan nama file
 * asli (bukan hash), agar bisa di-fetch dari dashboard delivery berdasarkan
 * nama PDS / manifest yang tampil di bar split.
 *
 * Dipanggil di job SETELAH output selesai dibuat (bukan saat upload), sehingga
 * yang tersimpan adalah file HASIL generate, bukan mentahan upload.
 */
class KanbanSourceArchive
{
    /**
     * @param  string  $jobId        UUID job (untuk baca meta.json)
     * @param  string[]  $outputFiles Nama file hasil di temp-kanban
     * @return string[]  Nama file yang berhasil diarsipkan
     */
    public static function archiveJobOutputs(string $jobId, array $outputFiles): array
    {
        $originals = self::originalNamesForJob($jobId);
        $outputFiles = array_values(array_filter(array_map(
            static fn ($v) => basename((string) $v),
            $outputFiles
        ), static fn ($v) => $v !== '' && $v !== '.' && $v !== '..'));

        if (empty($originals) || empty($outputFiles)) {
            try {
                logger()->warning('KanbanSourceArchive skip (data kosong)', [
                    'job' => $jobId,
                    'originals' => count($originals),
                    'outputs' => count($outputFiles),
                ]);
            } catch (\Throwable $ignored) {
            }

            return [];
        }

        $archived = [];

        try {
            // Aturan arsip: SELALU 1 file dengan nama asli (replace, tanpa _vol).
            // Multi volume digabung berurutan; halaman dibalik bila meta reverse.
            // Pipeline split tidak diubah — semua dilakukan di sini.
            $reverse = self::metaReverseForJob($jobId);
            $single = self::buildSingleOrderedTemp($jobId, $outputFiles, $reverse);
            if ($single === null) {
                throw new \RuntimeException('Gagal menyiapkan file arsip tunggal.');
            }
            if (count($outputFiles) === 1) {
                // 1 output (bahkan dari N input gabungan) → salin ke SETIAP nama
                // asli agar fetch per PDS mana pun ketemu.
                foreach ($originals as $name) {
                    Storage::copy('temp-kanban/'.$single, 'kanban-source/'.$name);
                    $archived[] = $name;
                }
            } else {
                // Multi volume → gabung jadi 1 file bernama original pertama.
                $name = $originals[0];
                Storage::copy('temp-kanban/'.$single, 'kanban-source/'.$name);
                $archived[] = $name;
            }
            // Bersihkan sisa _vol lama se-stem + temp gabungan/reverse.
            self::cleanupStaleVolumes($archived);
            if ($single !== $outputFiles[0]) {
                @unlink(storage_path('app/temp-kanban/'.$single));
            }
            // Catat nama arsip agar endpoint status menyajikan file arsip
            // (sudah reverse) untuk tombol download/preview, bukan file temp.
            self::recordArchivedInfo($jobId, $archived);
        } catch (\Throwable $e) {
            // best-effort: kegagalan arsip tidak boleh menggagalkan job, tapi dicatat
            try {
                logger()->warning('KanbanSourceArchive gagal', [
                    'job' => $jobId,
                    'error' => get_class($e).': '.$e->getMessage(),
                ]);
            } catch (\Throwable $ignored) {
            }
        }

        if (! empty($archived)) {
            try {
                logger()->info('KanbanSourceArchive OK', ['job' => $jobId, 'files' => $archived]);
            } catch (\Throwable $ignored) {
            }
        }

        return $archived;
    }

    /**
     * Flag reverse dari meta job (ditulis form upload, default ON).
     */
    protected static function metaReverseForJob(string $jobId): bool
    {
        try {
            $metaPath = storage_path('app/temp-kanban/'.basename($jobId).'.meta.json');
            if (! is_file($metaPath)) {
                return true;
            }
            $meta = json_decode((string) @file_get_contents($metaPath), true);
            if (! is_array($meta) || ! array_key_exists('reverse', $meta)) {
                return true;
            }

            return (bool) $meta['reverse'];
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Bangun 1 file temp berurutan dari N output volume.
     * Forward: vol1 hal1..N, vol2 hal1..N, ... (1 pass).
     * Reverse: volN halN..1, ..., vol1 halN..1 (1 pass, hasil global terbalik).
     * Tanpa rewrite bila 1 output + forward (perilaku lama persis).
     *
     * @return string|null Nama file temp (atau nama output asli bila tanpa rewrite)
     */
    protected static function buildSingleOrderedTemp(string $jobId, array $outputFiles, bool $reverse): ?string
    {
        $dir = storage_path('app/temp-kanban');
        if (count($outputFiles) === 1 && ! $reverse) {
            return $outputFiles[0];
        }

        if (self::memoryToBytes((string) ini_get('memory_limit')) < 512 * 1024 * 1024) {
            @ini_set('memory_limit', '512M');
        }

        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban Source Archive');

        $ordered = $reverse ? array_reverse($outputFiles) : $outputFiles;
        foreach ($ordered as $vol) {
            $path = $dir.'/'.basename((string) $vol);
            if (! is_file($path)) {
                continue;
            }
            $n = $output->setSourceFile($path);
            if ($reverse) {
                for ($p = $n; $p >= 1; $p--) {
                    self::importPageInto($output, $p);
                }
            } else {
                for ($p = 1; $p <= $n; $p++) {
                    self::importPageInto($output, $p);
                }
            }
        }

        $name = 'kanban_final_'.basename($jobId).'.pdf';
        $output->Output($dir.'/'.$name, 'F');

        return is_file($dir.'/'.$name) ? $name : null;
    }

    protected static function importPageInto(Fpdi $output, int $pageNo): void
    {
        $tpl = $output->importPage($pageNo);
        $size = $output->getTemplateSize($tpl);
        $w = (float) ($size['width'] ?? 0);
        $h = (float) ($size['height'] ?? 0);
        if ($w <= 0 || $h <= 0) {
            return;
        }
        $output->AddPage($w > $h ? 'L' : 'P', [$w, $h]);
        $output->useTemplate($tpl, 0, 0, $w, $h);
    }

    protected static function memoryToBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '' || $val === '-1') {
            return PHP_INT_MAX;
        }
        $num = (int) $val;
        switch (strtolower(substr($val, -1))) {
            case 'g':
                return $num * 1024 * 1024 * 1024;
            case 'm':
                return $num * 1024 * 1024;
            case 'k':
                return $num * 1024;
            default:
                return $num;
        }
    }

    /**
     * Hapus sisa <stem>_volN.pdf di kanban-source (aturan: arsip tanpa _vol).
     */
    protected static function cleanupStaleVolumes(array $archivedNames): void
    {
        try {
            $stems = [];
            foreach ($archivedNames as $name) {
                $stem = pathinfo(basename((string) $name), PATHINFO_FILENAME);
                if ($stem !== '') {
                    $stems[$stem] = true;
                }
            }
            if (empty($stems)) {
                return;
            }
            foreach (Storage::files('kanban-source') as $path) {
                $base = basename($path);
                foreach ($stems as $stem => $_) {
                    if (preg_match('/^'.preg_quote($stem, '/').'_vol\d+\.pdf$/i', $base)) {
                        Storage::delete($path);
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    /**
     * Catat nama file arsip ke <job>.info.json agar endpoint status bisa
     * menyajikan URL kanban-source (sudah reverse) untuk tombol download/preview.
     */
    protected static function recordArchivedInfo(string $jobId, array $archived): void
    {
        try {
            $id = basename($jobId);
            if ($id === '' || $id === '.' || $id === '..' || empty($archived)) {
                return;
            }
            $path = storage_path('app/temp-kanban/'.$id.'.info.json');
            $info = [];
            if (is_file($path)) {
                $decoded = json_decode((string) @file_get_contents($path), true);
                if (is_array($decoded)) {
                    $info = $decoded;
                }
            }
            $info['archived'] = array_values($archived);
            @file_put_contents($path, json_encode($info));
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    /**
     * Pecah isi marker .done (string nama file ATAU JSON {volumes:[...]}).
     *
     * @return string[]
     */
    public static function parseDoneMarker(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && ! empty($decoded['volumes']) && is_array($decoded['volumes'])) {
                return array_values(array_filter(array_map(
                    static fn ($v) => basename((string) $v),
                    $decoded['volumes']
                )));
            }
        } catch (\Throwable $e) {
            // bukan JSON → nama file tunggal
        }

        return [basename($raw)];
    }

    /**
     * @return string[] Nama arsip (sanitasi) dari meta job.
     */
    protected static function originalNamesForJob(string $jobId): array
    {
        $id = basename($jobId);
        if ($id === '' || $id === '.' || $id === '..') {
            return [];
        }

        try {
            $metaPath = storage_path('app/temp-kanban/'.$id.'.meta.json');
            if (! is_file($metaPath)) {
                return [];
            }
            $meta = json_decode((string) @file_get_contents($metaPath), true);
            if (! is_array($meta)) {
                return [];
            }

            // Flow upload langsung: meta['originals'] = [stored => original].
            // Nama arsip = key (nama simpan yg sudah disanitasi = nama asli).
            if (! empty($meta['originals']) && is_array($meta['originals'])) {
                return array_values(array_unique(array_filter(array_map(
                    static fn ($v) => basename((string) $v),
                    array_keys($meta['originals'])
                ))));
            }

            // Flow chunked: meta['original_files'] = [fi => namaAsli].
            if (! empty($meta['original_files']) && is_array($meta['original_files'])) {
                $out = [];
                foreach ($meta['original_files'] as $nm) {
                    if (! is_string($nm) || trim($nm) === '') {
                        continue;
                    }
                    $out[] = self::sanitizeName($nm);
                }

                return array_values(array_unique($out));
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        return [];
    }

    public static function sanitizeName(string $name): string
    {
        $base = basename(str_replace('\\', '/', trim($name)));
        $stem = pathinfo($base, PATHINFO_FILENAME);
        $stem = (string) preg_replace('/[^A-Za-z0-9._-]+/', '_', $stem);
        $stem = trim($stem, '._');
        if ($stem === '') {
            $stem = 'kanban';
        }

        return substr($stem, 0, 120).'.pdf';
    }
}
