<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateInternalParts extends Command
{
    protected $signature = 'internal-parts:clean-duplicates {--dry-run=1}';

    protected $description = 'Clean duplicate internal_parts by part_number and back_number, keeping the row used in customer_parts';

    public function handle()
    {
        $dryRun = (int) $this->option('dry-run') === 1;

        $this->info($dryRun ? 'DRY RUN MODE - Tidak ada data yang diubah.' : 'EXECUTE MODE - Data akan diubah.');
        $this->warn('NOTE: Tabel kanbans tidak akan disentuh.');

        $duplicates = DB::table('internal_parts')
            ->select(
                'part_number',
                'back_number',
                DB::raw('COUNT(*) as total'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as ids')
            )
            ->whereNotNull('part_number')
            ->whereNotNull('back_number')
            ->groupBy('part_number', 'back_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('Tidak ada duplicate internal_parts.');
            return Command::SUCCESS;
        }

        $this->info('Total group duplicate ditemukan: ' . $duplicates->count());

        foreach ($duplicates as $duplicate) {
            $partIds = collect(explode(',', $duplicate->ids))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->toArray();

            if (count($partIds) <= 1) {
                continue;
            }

            $keepId = DB::table('customer_parts')
                ->whereIn('internal_part_id', $partIds)
                ->select(
                    'internal_part_id',
                    DB::raw('COUNT(*) as total_customer_parts')
                )
                ->groupBy('internal_part_id')
                ->orderByDesc('total_customer_parts')
                ->orderBy('internal_part_id')
                ->value('internal_part_id');

            if (!$keepId) {
                $this->warn("SKIP: {$duplicate->part_number} | {$duplicate->back_number} tidak ada yang terdaftar di customer_parts.");
                continue;
            }

            $deleteIds = collect($partIds)
                ->reject(fn ($id) => (int) $id === (int) $keepId)
                ->values()
                ->toArray();

            if (empty($deleteIds)) {
                continue;
            }

            $this->line('');
            $this->info("Part Number : {$duplicate->part_number}");
            $this->info("Back Number : {$duplicate->back_number}");
            $this->info("Keep ID     : {$keepId}");
            $this->warn("Delete IDs  : " . implode(', ', $deleteIds));

            if ($dryRun) {
                continue;
            }

            DB::beginTransaction();

            try {
                $this->mergeCustomerParts((int) $keepId, $deleteIds);
                $this->mergeProductionStocks((int) $keepId, $deleteIds);

                DB::table('internal_parts')
                    ->whereIn('id', $deleteIds)
                    ->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                $this->error("Gagal proses duplicate {$duplicate->part_number} | {$duplicate->back_number}");
                $this->error($th->getMessage());

                return Command::FAILURE;
            }
        }

        $this->info('');
        $this->info($dryRun ? 'DRY RUN selesai.' : 'Duplicate internal_parts berhasil dibersihkan.');

        return Command::SUCCESS;
    }

    private function mergeCustomerParts(int $keepId, array $deleteIds): void
    {
        $duplicateCustomerParts = DB::table('customer_parts')
            ->whereIn('internal_part_id', $deleteIds)
            ->get();

        foreach ($duplicateCustomerParts as $customerPart) {
            $exists = DB::table('customer_parts')
                ->where('internal_part_id', $keepId)
                ->where('customer_id', $customerPart->customer_id)
                ->where('part_number', $customerPart->part_number)
                ->exists();

            if ($exists) {
                DB::table('customer_parts')
                    ->where('id', $customerPart->id)
                    ->delete();
            } else {
                DB::table('customer_parts')
                    ->where('id', $customerPart->id)
                    ->update([
                        'internal_part_id' => $keepId,
                        'updated_at'       => now(),
                    ]);
            }
        }
    }

    private function mergeProductionStocks(int $keepId, array $deleteIds): void
    {
        $allIds = array_merge([$keepId], $deleteIds);

        $totalStock = DB::table('production_stocks')
            ->whereIn('internal_part_id', $allIds)
            ->sum('current_stock');

        DB::table('production_stocks')
            ->whereIn('internal_part_id', $deleteIds)
            ->delete();

        DB::table('production_stocks')->updateOrInsert(
            [
                'internal_part_id' => $keepId,
            ],
            [
                'current_stock' => $totalStock,
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );
    }
}