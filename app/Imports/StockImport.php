<?php

namespace App\Imports;

use App\Models\InternalPart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StockImport implements ToCollection
{
    public int $updatedRows = 0;
    public int $notFoundRows = 0;
    public int $invalidRows = 0;
    public int $skippedRows = 0;

    private array $messages = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $header = $this->findHeaderRow($rows);

            if (!$header) {
                throw new \Exception('Header Excel tidak ditemukan. Pastikan ada kolom: Back Number, Standard Stock, Current Stock.');
            }

            $headerRowIndex = $header['row_index'];
            $columns = $header['columns'];

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex <= $headerRowIndex) {
                    continue;
                }

                $excelRow = $rowIndex + 1;

                $backNumber = $this->cleanCell($row[$columns['back_number']] ?? null);

                if ($backNumber === '') {
                    $this->skippedRows++;
                    continue;
                }

                $standardStockRaw = isset($columns['standard_stock'])
                    ? ($row[$columns['standard_stock']] ?? null)
                    : null;

                $currentStockRaw = isset($columns['current_stock'])
                    ? ($row[$columns['current_stock']] ?? null)
                    : null;

                $hasStandardStock = $this->isFilled($standardStockRaw);
                $hasCurrentStock  = $this->isFilled($currentStockRaw);

                if (!$hasStandardStock && !$hasCurrentStock) {
                    $this->invalidRows++;
                    $this->messages[] = "Baris {$excelRow}: Standard Stock dan Current Stock kosong.";
                    continue;
                }

                $internalPart = InternalPart::where('back_number', $backNumber)
                    ->whereIn('id', function ($query) {
                        $query->select('internal_part_id')
                            ->from('customer_parts');
                    })
                    ->orderBy('id')
                    ->first();

                if (!$internalPart) {
                    $internalPart = InternalPart::where('back_number', $backNumber)
                        ->orderBy('id')
                        ->first();
                }

                if (!$internalPart) {
                    $this->notFoundRows++;
                    $this->messages[] = "Baris {$excelRow}: Back Number {$backNumber} tidak ditemukan di Master Part.";
                    continue;
                }

                if ($hasStandardStock) {
                    $internalPart->update([
                        'standard_stock' => $this->toInteger($standardStockRaw),
                    ]);
                }

                if ($hasCurrentStock) {
                    $exists = DB::table('production_stocks')
                        ->where('internal_part_id', $internalPart->id)
                        ->exists();

                    if ($exists) {
                        DB::table('production_stocks')
                            ->where('internal_part_id', $internalPart->id)
                            ->update([
                                'current_stock' => $this->toInteger($currentStockRaw),
                                'updated_at'    => now(),
                            ]);
                    } else {
                        DB::table('production_stocks')->insert([
                            'internal_part_id' => $internalPart->id,
                            'current_stock'    => $this->toInteger($currentStockRaw),
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                $this->updatedRows++;
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function findHeaderRow(Collection $rows): ?array
    {
        foreach ($rows as $rowIndex => $row) {
            $columns = [];

            foreach ($row as $columnIndex => $cellValue) {
                $headerName = $this->normalizeHeader($cellValue);

                if (in_array($headerName, [
                    'back_number',
                    'back_no',
                    'back_num',
                    'backnumber',
                    'back',
                    'bn',
                ])) {
                    $columns['back_number'] = $columnIndex;
                }

                if (in_array($headerName, [
                    'standard_stock',
                    'standar_stock',
                    'std_stock',
                    'standard',
                    'standar',
                ])) {
                    $columns['standard_stock'] = $columnIndex;
                }

                if (in_array($headerName, [
                    'current_stock',
                    'curr_stock',
                    'actual_stock',
                    'stock',
                    'current',
                    'qty',
                ])) {
                    $columns['current_stock'] = $columnIndex;
                }
            }

            if (
                isset($columns['back_number']) &&
                (
                    isset($columns['standard_stock']) ||
                    isset($columns['current_stock'])
                )
            ) {
                return [
                    'row_index' => $rowIndex,
                    'columns'   => $columns,
                ];
            }
        }

        return null;
    }

    private function normalizeHeader($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim($value, '_');

        return $value;
    }

    private function cleanCell($value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function isFilled($value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function toInteger($value): int
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $value = preg_replace('/[^0-9\-]/', '', (string) $value);

        return (int) $value;
    }

    public function hasUpdatedData(): bool
    {
        return $this->updatedRows > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->notFoundRows > 0 || $this->invalidRows > 0;
    }

    public function getSummaryMessage(): string
    {
        return "Import Stock selesai. Data berhasil diupdate: {$this->updatedRows}, back number tidak ditemukan: {$this->notFoundRows}, baris tidak valid: {$this->invalidRows}.";
    }

    public function getUserMessages(): array
    {
        $messages = array_slice($this->messages, 0, 10);

        if (count($this->messages) > 10) {
            $messages[] = 'Dan ' . (count($this->messages) - 10) . ' pesan lainnya.';
        }

        return $messages;
    }
}