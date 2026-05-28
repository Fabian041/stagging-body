<?php

namespace App\Services;

use App\Models\MasterAlamatChute;
use Illuminate\Support\Str;

/**
 * Impor ke tabel master_alamat_chutes.
 * Kolom: Part no customer | Back No | Part name | Alamat Chute
 */
class MasterAlamatChuteImporter
{
    public int $imported = 0;

    /** @var array<int, string> */
    public array $errors = [];

    /** Urutan kolom jika header kosong / tidak terbaca (A, B, C, D). */
    private const POSITIONAL_KEYS = ['part_no_customer', 'back_no', 'part_name', 'alamat_chute'];

    /**
     * @param  array<int, array<int, mixed>>  $rows  Baris 0 = header, berikutnya data.
     * @return array{imported: int, errors: array<int, string>}
     */
    public function processSheetRows(array $rows): array
    {
        $this->imported = 0;
        $this->errors = [];

        if ($rows === []) {
            return ['imported' => 0, 'errors' => $this->errors];
        }

        $headerRow = $rows[0] ?? [];
        $slugHeaders = $this->buildSlugHeaders($headerRow);
        $usePositionalOnly = $this->isPlaceholderHeaders($slugHeaders);

        for ($r = 1; $r < count($rows); $r++) {
            $line = $r + 1;
            $rawRow = $rows[$r] ?? [];

            if ($usePositionalOnly) {
                $assoc = $this->mapPositionalColumns($rawRow);
            } else {
                $assoc = $this->combineHeaderRow($slugHeaders, $rawRow);
            }

            $assoc = $this->normalizeKeys($assoc);
            $assoc = $this->applyHeaderAliases($assoc);

            if ($this->rowIsEmpty($assoc)) {
                continue;
            }

            $partNoCustomer = $this->stringCell($assoc, [
                'part_no_customer',
                'part_no_cust',
                'part_number_customer',
                'part_customer',
                'nomor_part_customer',
                'part_no_customer',
            ]);
            if ($partNoCustomer === null || $partNoCustomer === '') {
                $this->errors[] = "Baris {$line}: kolom Part no customer wajib diisi.";

                continue;
            }

            $backNo = $this->stringCell($assoc, ['back_no', 'back_number', 'back']) ?? '';
            $partName = $this->stringCell($assoc, ['part_name', 'nama_part', 'nama_part_name']) ?? '';

            $alamat = $this->stringCell($assoc, ['alamat_chute', 'alamat', 'chute', 'alamat_chute']);
            if ($alamat === null || $alamat === '') {
                $this->errors[] = "Baris {$line}: kolom Alamat Chute wajib diisi.";

                continue;
            }
            if (strlen($alamat) > 100) {
                $this->errors[] = "Baris {$line}: Alamat Chute maksimal 100 karakter.";

                continue;
            }

            if (strlen($partNoCustomer) > 100) {
                $this->errors[] = "Baris {$line}: Part no customer maksimal 100 karakter.";

                continue;
            }
            if (strlen($backNo) > 50) {
                $this->errors[] = "Baris {$line}: Back No maksimal 50 karakter.";

                continue;
            }
            if (strlen($partName) > 150) {
                $this->errors[] = "Baris {$line}: Part name maksimal 150 karakter.";

                continue;
            }

            try {
                MasterAlamatChute::updateOrCreate(
                    [
                        'part_no_customer' => $partNoCustomer,
                        'back_no' => $backNo,
                        'part_name' => $partName,
                    ],
                    ['alamat_chute' => $alamat]
                );

                $fresh = MasterAlamatChute::query()
                    ->where('part_no_customer', $partNoCustomer)
                    ->where('back_no', $backNo)
                    ->where('part_name', $partName)
                    ->first();

                if (! $fresh || trim((string) $fresh->alamat_chute) !== $alamat) {
                    $this->errors[] = "Baris {$line}: data tidak terbaca kembali dari database setelah simpan.";

                    continue;
                }

                $this->imported++;
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$line}: gagal menyimpan ke database — ".$e->getMessage();
            }
        }

        return ['imported' => $this->imported, 'errors' => $this->errors];
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array<int, string>
     */
    private function buildSlugHeaders(array $headerRow): array
    {
        $slugHeaders = [];
        foreach ($headerRow as $ci => $h) {
            $h = trim((string) $h);
            $h = preg_replace('/^\x{FEFF}/u', '', $h);
            $h = ltrim($h, "\xEF\xBB\xBF");
            if ($h === '') {
                $slugHeaders[$ci] = '_col_'.$ci;

                continue;
            }
            $slug = Str::slug($h, '_');
            $slugHeaders[$ci] = $slug !== '' ? $slug : '_col_'.$ci;
        }

        return $slugHeaders;
    }

    /**
     * @param  array<int, string>  $slugHeaders
     */
    private function isPlaceholderHeaders(array $slugHeaders): bool
    {
        if ($slugHeaders === []) {
            return true;
        }

        foreach ($slugHeaders as $s) {
            if (! preg_match('/^_col_\d+$/', (string) $s)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, mixed>  $rawRow
     * @return array<string, mixed>
     */
    private function mapPositionalColumns(array $rawRow): array
    {
        $assoc = [];
        foreach (self::POSITIONAL_KEYS as $i => $key) {
            $assoc[$key] = $rawRow[$i] ?? null;
        }

        return $assoc;
    }

    /**
     * @param  array<int, string>  $slugHeaders
     * @param  array<int, mixed>  $rawRow
     * @return array<string|int, mixed>
     */
    private function combineHeaderRow(array $slugHeaders, array $rawRow): array
    {
        $assoc = [];
        foreach ($slugHeaders as $ci => $key) {
            $assoc[$key] = $rawRow[$ci] ?? null;
        }

        return $assoc;
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @return array<string|int, mixed>
     */
    private function applyHeaderAliases(array $row): array
    {
        if ($this->cell($row, ['part_no_customer']) === null || $this->cell($row, ['part_no_customer']) === '') {
            foreach ([
                'part_no_cust',
                'part_number_customer',
                'part_customer',
                'nomor_part_customer',
                'part_no_customer',
            ] as $alt) {
                if (! array_key_exists($alt, $row)) {
                    continue;
                }
                $v = $row[$alt];
                if ($v !== null && $v !== '') {
                    $row['part_no_customer'] = $v;
                    break;
                }
            }
        }

        if ($this->cell($row, ['back_no']) === null || $this->cell($row, ['back_no']) === '') {
            foreach (['back_number', 'back'] as $alt) {
                if (! array_key_exists($alt, $row)) {
                    continue;
                }
                $v = $row[$alt];
                if ($v !== null && $v !== '') {
                    $row['back_no'] = $v;
                    break;
                }
            }
        }

        if ($this->cell($row, ['part_name']) === null || $this->cell($row, ['part_name']) === '') {
            foreach (['nama_part', 'nama_part_name'] as $alt) {
                if (! array_key_exists($alt, $row)) {
                    continue;
                }
                $v = $row[$alt];
                if ($v !== null && $v !== '') {
                    $row['part_name'] = $v;
                    break;
                }
            }
        }

        if ($this->cell($row, ['alamat_chute']) === null || $this->cell($row, ['alamat_chute']) === '') {
            foreach (['alamat', 'chute', 'alamat_chute'] as $alt) {
                if (! array_key_exists($alt, $row)) {
                    continue;
                }
                $v = $row[$alt];
                if ($v !== null && $v !== '') {
                    $row['alamat_chute'] = $v;
                    break;
                }
            }
        }

        return $row;
    }

    /**
     * @param  array<int|string, mixed>  $arr
     * @return array<int|string, mixed>
     */
    private function normalizeKeys(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (! is_string($k)) {
                $out[$k] = $v;

                continue;
            }
            $nk = strtolower(trim($k));
            $nk = preg_replace('/^\x{FEFF}/u', '', $nk);
            $nk = ltrim($nk, "\xEF\xBB\xBF");
            $nk = preg_replace('/\s+/u', '_', $nk);
            $out[$nk] = $v;
        }

        return $out;
    }

    /**
     * @param  array<string|int, mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '' && $v !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function cell(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }
            $v = $row[$key];
            if ($v === null || $v === '') {
                continue;
            }

            return $v;
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function stringCell(array $row, array $keys): ?string
    {
        $v = $this->cell($row, $keys);
        if ($v === null) {
            return null;
        }

        return trim((string) $v);
    }
}
