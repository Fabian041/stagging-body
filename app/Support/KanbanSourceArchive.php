<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

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
            if (count($outputFiles) === 1) {
                // 1 output (bahkan dari N input gabungan) → salin ke SETIAP nama
                // asli agar fetch per PDS mana pun ketemu.
                foreach ($originals as $name) {
                    Storage::copy('temp-kanban/'.$outputFiles[0], 'kanban-source/'.$name);
                    $archived[] = $name;
                }
            } else {
                // Multi volume → <stem>_vol1.pdf, <stem>_vol2.pdf, ... berbasis
                // original pertama (masih match substring PDS di endpoint).
                $stem = pathinfo($originals[0], PATHINFO_FILENAME);
                foreach ($outputFiles as $i => $vol) {
                    $name = $stem.'_vol'.($i + 1).'.pdf';
                    Storage::copy('temp-kanban/'.$vol, 'kanban-source/'.$name);
                    $archived[] = $name;
                }
            }
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
