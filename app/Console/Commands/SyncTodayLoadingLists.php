<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\LoadingList;
use App\Models\LoadingListDetail;
use App\Models\PisScan;
use App\Models\PisScanDetail;
use App\Support\ConvertsCustomerPartNumber;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncTodayLoadingLists extends Command
{
    use ConvertsCustomerPartNumber;

    protected $signature = 'sync:loading-lists
                            {date? : Delivery date (Y-m-d), default hari ini}
                            {--days=1 : Jumlah hari berurutan yang disinkronkan}
                            {--web : Return JSON untuk dashboard}';

    protected $description = 'Import loading list berdasarkan tanggal dari SQL Server ke database lokal';

    public function handle(): int
    {
        $date = $this->syncDate();
        if (! $date) {
            return Command::FAILURE;
        }

        try {
            $days = max(1, min(31, (int) $this->option('days')));
            $dateValues = [];
            for ($offset = 0; $offset < $days; $offset++) {
                $syncDate = $date->copy()->addDays($offset);
                $dateValues[] = $syncDate->toDateString();
                $dateValues[] = $syncDate->format('Ymd');
                $dateValues[] = $syncDate->format('Y/m/d');
            }

            $rows = DB::connection('mssql_external')
                ->table('TT_GIG_SYKMEISAI')
                ->select([
                    'CHR_COD_SYKMNO',
                    'CHR_COD_TKS_NOUBAN',
                    'CHR_NGP_NOUNYU',
                    'CHR_NGP_SYUKKA',
                    'INT_NUB_NOUBIN',
                    'CHR_COD_TOKISAKI',
                    'CHR_MEI_NOUNYU',
                    'CHR_NUB_HINMOKU_TOK',
                    'CHR_NUB_HMK_HYP',
                    'INT_SUR_NOUSIJI',
                    'INT_SUR_SYUUYOU',
                    'INT_SUR_JYUCYUU',
                    'INT_NUB_MSEQ',
                ])
                ->whereIn('CHR_NGP_NOUNYU', $dateValues)
                ->where('CHR_COD_SYKMNO', 'like', 'C%')
                ->orderBy('CHR_COD_SYKMNO')
                ->orderBy('INT_NUB_MSEQ')
                ->get();

            $loadingLists = $this->buildLoadingLists($rows);
            $summary = $this->importLoadingLists($loadingLists);
            Cache::forget('loading_list_pds_count_v2');

            if ($this->option('web')) {
                $this->line((string) json_encode([
                    'loading_list_count' => count($loadingLists),
                    'skipped_items' => $summary['failures'],
                ]));

                return Command::SUCCESS;
            }

            $this->info('Tanggal sync: '.$date->toDateString());
            $this->table(
                ['LL baru', 'LL diperbarui', 'LL tetap', 'Detail baru', 'Detail diperbarui', 'Detail tetap', 'Dilewati'],
                [[
                    $summary['loading_lists_created'],
                    $summary['loading_lists_updated'],
                    $summary['loading_lists_unchanged'],
                    $summary['details_created'],
                    $summary['details_updated'],
                    $summary['details_unchanged'],
                    count($summary['failures']),
                ]]
            );

            foreach (array_slice($summary['failures'], 0, 20) as $failure) {
                $this->warn(implode(' | ', array_filter([
                    $failure['loading_list'] ?? null,
                    $failure['part_number_int'] ?? null,
                    $failure['part_number_cust'] ?? null,
                    $failure['reason'],
                ])));
            }

            $skipped = count($summary['failures']);
            if ($skipped > 0) {
                $this->warn("Catatan sync: {$skipped} data dilewati.");
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Import loading list dari SQL Server gagal: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    private function syncDate(): ?Carbon
    {
        $value = trim((string) $this->argument('date'));
        if ($value === '') {
            return now()->startOfDay();
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (Throwable $e) {
            $date = null;
        }

        if (! $date || $date->format('Y-m-d') !== $value) {
            $this->error('Tanggal harus menggunakan format Y-m-d, contoh: 2026-07-22.');

            return null;
        }

        return $date;
    }

    private function buildLoadingLists($rows): array
    {
        return $rows
            ->groupBy(fn ($row) => trim((string) $row->CHR_COD_SYKMNO))
            ->map(function ($items, $number) {
                $first = $items->first();

                return [
                    'number' => $number,
                    'pds_number' => trim((string) $first->CHR_COD_TKS_NOUBAN),
                    'cycle' => trim((string) $first->INT_NUB_NOUBIN),
                    'customer_code' => trim((string) $first->CHR_COD_TOKISAKI),
                    'delivery_date' => $this->normalizeSourceDate($first->CHR_NGP_NOUNYU),
                    'shipping_date' => $this->normalizeSourceDate($first->CHR_NGP_SYUKKA),
                    'items' => $items->map(fn ($item) => [
                        'part_number_int' => trim((string) $item->CHR_NUB_HMK_HYP),
                        'part_number_cust' => trim((string) $item->CHR_NUB_HINMOKU_TOK),
                        'total_qty' => (int) $item->INT_SUR_JYUCYUU,
                        'qty_per_kanban' => (int) $item->INT_SUR_SYUUYOU,
                        'actual_kanban_qty' => 0,
                        'total_kanban_qty' => (int) $item->INT_SUR_NOUSIJI,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeSourceDate($value): string
    {
        $value = trim((string) $value);

        if (preg_match('/^\d{8}$/', $value)) {
            return substr($value, 0, 4).'-'.substr($value, 4, 2).'-'.substr($value, 6, 2);
        }

        return str_replace('/', '-', $value);
    }

    private function importLoadingLists(array $loadingLists): array
    {
        $summary = [
            'loading_lists_created' => 0,
            'loading_lists_updated' => 0,
            'loading_lists_unchanged' => 0,
            'details_created' => 0,
            'details_updated' => 0,
            'details_unchanged' => 0,
            'failures' => [],
        ];

        foreach ($loadingLists as $source) {
            $number = trim((string) ($source['number'] ?? ''));
            $customerCode = trim((string) ($source['customer_code'] ?? ''));
            $pdsNumber = trim((string) ($source['pds_number'] ?? ''));

            if ($number === '' || $customerCode === '') {
                $summary['failures'][] = [
                    'loading_list' => $number ?: null,
                    'reason' => 'Nomor loading list atau customer code kosong',
                ];

                continue;
            }

            $customer = $this->resolveCustomer($customerCode, $pdsNumber);
            if (! $customer) {
                $summary['failures'][] = [
                    'loading_list' => $number,
                    'reason' => "Customer {$customerCode} tidak ditemukan",
                ];

                continue;
            }

            DB::transaction(function () use ($source, $number, $pdsNumber, $customer, &$summary) {
                if (in_array((string) $customer->name, PisScan::DELIVERY_CUSTOMER_NAMES, true)) {
                    $this->syncPisScan($source, $number, $pdsNumber, $customer, $summary);

                    return;
                }

                $loadingList = LoadingList::firstOrNew(['number' => $number]);
                $wasNew = ! $loadingList->exists;

                $loadingList->fill([
                    'pds_number' => $pdsNumber ?: null,
                    'cycle' => $source['cycle'] ?? null,
                    'customer_id' => $customer->id,
                    'delivery_date' => $source['delivery_date'] ?? null,
                    'shipping_date' => $source['shipping_date'] ?? null,
                ]);

                $wasChanged = $loadingList->isDirty();
                $loadingList->save();

                if ($wasNew) {
                    $summary['loading_lists_created']++;
                } elseif ($wasChanged) {
                    $summary['loading_lists_updated']++;
                } else {
                    $summary['loading_lists_unchanged']++;
                }

                $this->syncDetails($loadingList, $source['items'], $summary);
            });
        }

        return $summary;
    }

    private function resolveCustomer(string $customerCode, string $pdsNumber): ?Customer
    {
        $query = Customer::select('id', 'name')->where('code', $customerCode);

        if ($customerCode === '7A00022' && str_contains($pdsNumber, 'KK11')) {
            $query->where('name', 'like', '%SUZUKI RKK11%');
        }

        return $query->first();
    }

    private function syncPisScan(
        array $source,
        string $number,
        string $pdsNumber,
        Customer $customer,
        array &$summary
    ): void {
        $pisScan = PisScan::firstOrNew(['loading_list_number' => $number]);
        $wasNew = ! $pisScan->exists;

        $pisScan->fill([
            'pds_number' => $pdsNumber ?: null,
            'cycle' => $source['cycle'] ?? null,
            'customer_id' => $customer->id,
            'delivery_date' => $source['delivery_date'] ?? null,
            'shipping_date' => $source['shipping_date'] ?? null,
        ]);

        $wasChanged = $pisScan->isDirty();
        $pisScan->save();

        if ($wasNew) {
            $summary['loading_lists_created']++;
        } elseif ($wasChanged) {
            $summary['loading_lists_updated']++;
        } else {
            $summary['loading_lists_unchanged']++;
        }

        // Bersihkan hasil sync lama supaya satu loading list hanya punya satu sumber lokal.
        LoadingList::where('number', $number)->delete();

        $existingDetails = PisScanDetail::where('pis_scan_id', $pisScan->id)
            ->get()
            ->keyBy(function (PisScanDetail $detail) {
                return trim((string) $detail->part_number_int).'|'.trim((string) $detail->part_number_cust);
            });

        foreach ($source['items'] as $item) {
            $key = trim((string) $item['part_number_int']).'|'.trim((string) $item['part_number_cust']);
            $targetQty = (int) $item['total_kanban_qty'];
            $detail = $existingDetails->get($key);

            if (! $detail) {
                $detail = PisScanDetail::create([
                    'pis_scan_id' => $pisScan->id,
                    'part_number_int' => $item['part_number_int'],
                    'part_number_cust' => $item['part_number_cust'],
                    'target_qty' => $targetQty,
                    'scanned_qty' => 0,
                    'remaining_qty' => $targetQty,
                ]);
                $existingDetails->put($key, $detail);
                $summary['details_created']++;

                continue;
            }

            $detail->fill([
                'target_qty' => $targetQty,
                'remaining_qty' => max(0, $targetQty - (int) $detail->scanned_qty),
            ]);

            if ($detail->isDirty()) {
                $detail->save();
                $summary['details_updated']++;
            } else {
                $summary['details_unchanged']++;
            }
        }
    }

    private function syncDetails(LoadingList $loadingList, array $items, array &$summary): void
    {
        $internalParts = InternalPart::whereIn(
            'part_number',
            collect($items)->pluck('part_number_int')->unique()->all()
        )->get(['id', 'part_number'])->keyBy(fn ($part) => trim((string) $part->part_number));

        $resolved = [];
        foreach ($items as $item) {
            $internalPart = $internalParts->get($item['part_number_int']);
            if (! $internalPart) {
                $summary['failures'][] = [
                    'loading_list' => $loadingList->number,
                    'part_number_int' => $item['part_number_int'],
                    'part_number_cust' => $item['part_number_cust'],
                    'reason' => 'Internal part tidak ditemukan',
                ];

                continue;
            }

            $resolved[] = $item + [
                'internal_part_id' => $internalPart->id,
                'converted_customer_number' => $this->convertCustomerPartNumber(
                    $item['part_number_cust'],
                    $loadingList->customer_id
                ),
            ];
        }

        if (empty($resolved)) {
            return;
        }

        $customerParts = CustomerPart::where('customer_id', $loadingList->customer_id)
            ->whereIn('internal_part_id', collect($resolved)->pluck('internal_part_id')->unique()->all())
            ->whereIn('part_number', collect($resolved)->pluck('converted_customer_number')->unique()->all())
            ->get(['id', 'internal_part_id', 'part_number'])
            ->keyBy(fn ($part) => $part->internal_part_id.'|'.trim((string) $part->part_number));

        $mapped = [];
        foreach ($resolved as $item) {
            $customerPart = $customerParts->get(
                $item['internal_part_id'].'|'.$item['converted_customer_number']
            );

            if (! $customerPart) {
                $summary['failures'][] = [
                    'loading_list' => $loadingList->number,
                    'part_number_int' => $item['part_number_int'],
                    'part_number_cust' => $item['converted_customer_number'],
                    'reason' => 'Customer part tidak ditemukan',
                ];

                continue;
            }

            $mapped[] = $item + ['customer_part_id' => $customerPart->id];
        }

        $existingDetails = LoadingListDetail::where('loading_list_id', $loadingList->id)
            ->whereIn('customer_part_id', collect($mapped)->pluck('customer_part_id')->unique()->all())
            ->get()
            ->keyBy('customer_part_id');

        foreach ($mapped as $item) {
            $detail = $existingDetails->get($item['customer_part_id']);
            if (! $detail) {
                $detail = LoadingListDetail::create([
                    'loading_list_id' => $loadingList->id,
                    'customer_part_id' => $item['customer_part_id'],
                    'kanban_qty' => $item['total_kanban_qty'],
                    'qty_per_kanban' => $item['qty_per_kanban'],
                    'total_qty' => $item['total_qty'],
                    'actual_kanban_qty' => 0,
                ]);
                $existingDetails->put($item['customer_part_id'], $detail);
                $summary['details_created']++;

                continue;
            }

            $detail->fill([
                'kanban_qty' => $item['total_kanban_qty'],
                'qty_per_kanban' => $item['qty_per_kanban'],
                'total_qty' => $item['total_qty'],
            ]);

            if ($detail->isDirty()) {
                $detail->save();
                $summary['details_updated']++;
            } else {
                $summary['details_unchanged']++;
            }
        }
    }
}
