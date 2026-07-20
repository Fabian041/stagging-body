<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Kanban;
use App\Models\Line;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PartImport implements ToCollection, WithHeadingRow, WithStartRow
{
    public int $newInternalParts = 0;
    public int $newCustomerParts = 0;
    public int $duplicateRows = 0;
    public int $invalidRows = 0;

    private array $messages = [];
    private array $affectedInternalPartIds = [];
    private array $lineIdCache = [];
    private array $customerIdCache = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $excelRow = $index + $this->startRow();

                $partName           = $this->clean($row['part_name'] ?? '');
                $partNumberInternal = $this->clean($row['part_no_aiia'] ?? '');
                $partNumberCustomer = $this->clean($row['part_no_customer'] ?? '');
                $backNumberInternal = $this->clean($row['aiia_back_no'] ?? '');
                $backNumberCustomer = $this->clean($row['cust_back_no'] ?? '');
                $qtyPerKanban       = $row['qty_per_kbn'] ?? null;
                $lineName           = $this->clean($row['line_name'] ?? '');
                $customerName       = $this->clean($row['customer_name'] ?? '');

                if (
                    $partNumberInternal === '' ||
                    $backNumberInternal === '' ||
                    $partNumberCustomer === '' ||
                    $lineName === '' ||
                    $customerName === ''
                ) {
                    $this->invalidRows++;
                    $this->messages[] = "Baris {$excelRow}: data wajib belum lengkap.";
                    continue;
                }

                $lineId = $this->resolveLineId($lineName);
                if ($lineId === null) {
                    $this->invalidRows++;
                    $this->messages[] = "Baris {$excelRow}: line \"{$lineName}\" tidak ditemukan.";
                    continue;
                }

                $customerId = $this->resolveCustomerId($customerName);
                if ($customerId === null) {
                    $this->invalidRows++;
                    $this->messages[] = "Baris {$excelRow}: customer \"{$customerName}\" tidak ditemukan.";
                    continue;
                }

                $internalPart = InternalPart::where('part_number', $partNumberInternal)
                    ->where('back_number', $backNumberInternal)
                    ->first();

                if (!$internalPart) {
                    $internalPart = InternalPart::create([
                        'line_id'      => $lineId,
                        'part_number'  => $partNumberInternal,
                        'back_number'  => $backNumberInternal,
                        'part_name'    => $partName,
                        'photo'        => $backNumberInternal . '.jpeg',
                    ]);

                    $this->newInternalParts++;
                    $this->affectedInternalPartIds[] = $internalPart->id;
                }

                $customerPartExists = CustomerPart::where('internal_part_id', $internalPart->id)
                    ->where('customer_id', $customerId)
                    ->where('part_number', $partNumberCustomer)
                    ->where(function ($query) use ($backNumberCustomer) {
                        if ($backNumberCustomer === '') {
                            $query->whereNull('back_number')
                                ->orWhere('back_number', '');
                        } else {
                            $query->where('back_number', $backNumberCustomer);
                        }
                    })
                    ->exists();

                if ($customerPartExists) {
                    $this->duplicateRows++;
                    $this->messages[] = "Baris {$excelRow}: part {$partNumberInternal} / {$backNumberInternal} sudah terdaftar.";
                    continue;
                }

                CustomerPart::create([
                    'internal_part_id' => $internalPart->id,
                    'customer_id'      => $customerId,
                    'part_number'      => $partNumberCustomer,
                    'back_number'      => $backNumberCustomer !== '' ? $backNumberCustomer : null,
                    'qty_per_kanban'   => $qtyPerKanban,
                ]);

                $this->newCustomerParts++;
                $this->affectedInternalPartIds[] = $internalPart->id;
            }

            $this->affectedInternalPartIds = array_values(array_unique($this->affectedInternalPartIds));

            $this->updatePhotos($this->affectedInternalPartIds);
            $this->seedMissingKanban($this->affectedInternalPartIds);
            $this->ensureProductionStock($this->affectedInternalPartIds);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function updatePhotos(array $internalPartIds): void
    {
        if (empty($internalPartIds)) {
            return;
        }

        InternalPart::whereIn('id', $internalPartIds)
            ->whereNull('photo')
            ->update([
                'photo' => DB::raw("CONCAT(back_number, '.jpeg')")
            ]);
    }

    private function seedMissingKanban(array $internalPartIds): void
    {
        if (empty($internalPartIds)) {
            return;
        }

        $target = 3000;
        $now = now();

        $internalParts = InternalPart::whereIn('id', $internalPartIds)->get();

        foreach ($internalParts as $part) {
            $existingSerials = Kanban::where('internal_part_id', $part->id)
                ->pluck('serial_number')
                ->map(fn ($serial) => (int) $serial)
                ->toArray();

            $existingSerials = array_flip($existingSerials);
            $insertData = [];

            for ($i = 1; $i <= $target; $i++) {
                if (isset($existingSerials[$i])) {
                    continue;
                }

                $insertData[] = [
                    'serial_number'    => sprintf('%04d', $i),
                    'internal_part_id' => $part->id,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                if (count($insertData) >= 500) {
                    Kanban::insert($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                Kanban::insert($insertData);
            }
        }
    }

    private function ensureProductionStock(array $internalPartIds): void
    {
        if (empty($internalPartIds)) {
            return;
        }

        foreach ($internalPartIds as $internalPartId) {
            $exists = DB::table('production_stocks')
                ->where('internal_part_id', $internalPartId)
                ->exists();

            if (!$exists) {
                DB::table('production_stocks')->insert([
                    'internal_part_id' => $internalPartId,
                    'current_stock'    => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    private function clean($value): string
    {
        return trim((string) $value);
    }

    private function resolveLineId(string $lineName): ?int
    {
        $key = mb_strtolower($lineName);

        if (array_key_exists($key, $this->lineIdCache)) {
            return $this->lineIdCache[$key];
        }

        $lineId = Line::query()
            ->where('name', $lineName)
            ->value('id');

        $this->lineIdCache[$key] = $lineId ? (int) $lineId : null;

        return $this->lineIdCache[$key];
    }

    private function resolveCustomerId(string $customerName): ?int
    {
        $key = mb_strtolower($customerName);

        if (array_key_exists($key, $this->customerIdCache)) {
            return $this->customerIdCache[$key];
        }

        $customerId = Customer::query()
            ->where('name', $customerName)
            ->value('id');

        $this->customerIdCache[$key] = $customerId ? (int) $customerId : null;

        return $this->customerIdCache[$key];
    }

    public function hasImportedData(): bool
    {
        return $this->newInternalParts > 0 || $this->newCustomerParts > 0;
    }

    public function isAllDuplicate(): bool
    {
        return $this->duplicateRows > 0
            && $this->newInternalParts === 0
            && $this->newCustomerParts === 0
            && $this->invalidRows === 0;
    }

    public function hasWarnings(): bool
    {
        return $this->duplicateRows > 0 || $this->invalidRows > 0;
    }

    public function getSummaryMessage(): string
    {
        return "Import Master Part selesai. Part baru: {$this->newInternalParts}, customer part baru: {$this->newCustomerParts}, duplikat dilewati: {$this->duplicateRows}, baris tidak valid: {$this->invalidRows}.";
    }

    public function getUserMessages(): array
    {
        $messages = array_slice($this->messages, 0, 10);

        if (count($this->messages) > 10) {
            $messages[] = 'Dan ' . (count($this->messages) - 10) . ' pesan lainnya.';
        }

        return $messages;
    }

    public function startRow(): int
    {
        return 3;
    }
}