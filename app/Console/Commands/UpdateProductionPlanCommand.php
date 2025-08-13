<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ProductionPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductionPlanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:plan {--date=} {--days=1} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update production plan data automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting production plan update...');
        
        try {
            $specificDate = $this->option('date');
            $days = (int) $this->option('days');
            $forceRefresh = $this->option('force');
            
            if ($specificDate) {
                // Update specific date
                $date = Carbon::parse($specificDate)->startOfDay();
                $this->updateProductionPlan($date, $forceRefresh);
            } else {
                // Update multiple days from today
                for ($i = 0; $i < $days; $i++) {
                    $date = now()->startOfDay()->addDays($i);
                    $this->updateProductionPlan($date, $forceRefresh);
                }
            }
            
            $this->info('Production plan update completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Production plan update failed: ' . $e->getMessage());
            Log::error('Production plan command failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }

    /**
     * Update production plan for a specific date
     */
    protected function updateProductionPlan(Carbon $selectedDate, bool $forceRefresh = false)
    {
        $this->info("Processing date: " . $selectedDate->format('Y-m-d'));
        
        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10); // 12:00 selected date
        $end = $start->copy()->addDay();      // 12:00 next day

        $backNosByLine = [
            'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18'],
            'AS004' => ['CI15', 'CI16', 'CI19'],
        ];

        $prodTimeByBackNo = [
            'CI11' => '00:34',
            'CI12' => '00:34',
            'CI13' => '00:40',
            'CI14' => '00:34',
            'CI15' => '00:39',
            'CI16' => '00:40',
            'CI17' => '00:40',
            'CI18' => '00:40',
            'CI19' => '00:37',
        ];

        $allBackNos = collect($backNosByLine)->flatten()->unique()->values();

        // Check if we have fresh data in database (last 30 minutes)
        $lastUpdate = ProductionPlan::where('plan_date', $today->format('Y-m-d'))
            ->max('updated_at');

        if ($forceRefresh || !$lastUpdate || ($lastUpdate instanceof \Carbon\Carbon && $lastUpdate->diffInMinutes(now()) > 30)) {
            $this->line("Fetching fresh data for " . $selectedDate->format('Y-m-d') . "...");
            
            try {
                DB::beginTransaction();

                // Fetch new data
                $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $prodTimeByBackNo, $selectedDate);

                if ($rawData->isEmpty()) {
                    $this->warn("No production data available for " . $selectedDate->format('Y-m-d'));
                    return;
                }

                $this->info("Processing " . $rawData->count() . " records...");
                $processedData = $this->processRawData($rawData, $start, $end);

                // Update or create records
                $this->updateProductionData($processedData, $backNosByLine, $today);

                DB::commit();
                $this->info("Successfully updated production data for " . $selectedDate->format('Y-m-d'));
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Production data processing failed for ' . $selectedDate->format('Y-m-d') . ': ' . $e->getMessage());
                $this->error('Failed to update data for ' . $selectedDate->format('Y-m-d') . ': ' . $e->getMessage());
                
                // Try to use cached data from previous day
                $lastData = ProductionPlan::where('plan_date', $today->copy()->subDay()->format('Y-m-d'))
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($lastData) {
                    $this->warn('Using cached data from previous day for ' . $selectedDate->format('Y-m-d'));
                }
            }
        } else {
            $this->info("Data for " . $selectedDate->format('Y-m-d') . " is fresh (updated: " . $lastUpdate->format('Y-m-d H:i:s') . ")");
        }
    }

    // Copy all the helper methods from your original controller
    protected function fetchWithLaravelDB($today, $start, $allBackNos, $prodTimeByBackNo, $selectedDate)
    {
        try {
            $deliveryDate = $selectedDate->format('Ymd');
            $nextDay = $selectedDate->copy()->addDay()->format('Ymd');

            $excludedCustomers = [
                'ADM SERVICE PART DIVISION',
                'TMMIN SERVICE PARTS DIVISION',
                'TAM SPARE PART DIVISION (DAIHATSU)',
                'PT MISTUBISHI MOTORS KRAMAYUDHA SALES ID'
            ];

            $query = DB::connection('mssql_external')
                ->table('TT_GIG_SYKMEISAI')
                ->select(
                    'CHR_MEI_NOUNYU as customer',
                    'CHR_COD_UKEIRE as dock',
                    'INT_NUB_NOUBIN as cycle',
                    DB::raw('RTRIM(CHR_COD_SEBANGOU) as back_no'),
                    'INT_SUR_SYUUYOU as qty_per_pallet',
                    'INT_SUR_JYUCYUU as order_qty',
                    'CHR_TIM_SYUKKA',
                    'CHR_COD_TKS_NOUBAN as dn_number',
                    'CHR_NGP_NOUNYU as delivery_date'
                )
                ->whereNotNull('CHR_TIM_SYUKKA')
                ->where(function ($query) use ($deliveryDate, $nextDay) {
                    $query->where(function ($q) use ($deliveryDate) {
                        $q->where('CHR_NGP_NOUNYU', $deliveryDate)
                            ->where('CHR_TIM_SYUKKA', '>=', '100000');
                    })
                        ->orWhere(function ($q) use ($nextDay) {
                            $q->where('CHR_NGP_NOUNYU', $nextDay)
                                ->where('CHR_TIM_SYUKKA', '<', '104000');
                        });
                })
                ->whereNotIn('CHR_MEI_NOUNYU', $excludedCustomers)
                ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU)"), $allBackNos);

            return $query->get()->map(function ($item) use ($selectedDate, $prodTimeByBackNo) {
                $item->back_no = trim($item->back_no);

                $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);

                // Use the delivery date to determine which date to use as base
                $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                $time = $deliveryDate->copy()->setTime(
                    substr($timeStr, 0, 2),
                    substr($timeStr, 2, 2),
                    substr($timeStr, 4, 2)
                );

                return (object)[
                    'customer' => $item->customer,
                    'dock' => $item->dock,
                    'cycle' => $item->cycle,
                    'back_no' => $item->back_no,
                    'qty_per_pallet' => $item->qty_per_pallet,
                    'order_qty' => $item->order_qty,
                    'dn_number' => $item->dn_number,
                    'formatted_time' => $time->format('H:i'),
                    'time_sort' => $time->timestamp,
                    'prod_time' => $prodTimeByBackNo[$item->back_no] ?? '00:00',
                    'delivery_date' => $item->delivery_date
                ];
            });
        } catch (\Exception $e) {
            Log::warning('Laravel DB parameterized query failed: ' . $e->getMessage());

            try {
                $backNosString = implode("','", $allBackNos->map(fn($item) => trim($item))->toArray());
                $date = $selectedDate->format('Ymd');
                $nextDate = $selectedDate->copy()->addDay()->format('Ymd');
                $excludedCustomersString = implode("','", [
                    'ADM SERVICE PART DIVISION',
                    'TMMIN SERVICE PARTS DIVISIONS',
                    'TAM SPARE PART DIVISION (DAIHATSU)',
                    'PT MISTUBISHI MOTORS KRAMAYUDHA SALES ID'
                ]);

                $sql = "SELECT 
                        CHR_MEI_NOUNYU as customer,
                        CHR_COD_UKEIRE as dock,
                        INT_NUB_NOUBIN as cycle,
                        RTRIM(CHR_COD_SEBANGOU) as back_no,
                        INT_SUR_SYUUYOU as qty_per_pallet,
                        INT_SUR_JYUCYUU as order_qty,
                        CHR_TIM_SYUKKA,
                        CHR_COD_TKS_NOUBAN as dn_number,
                        CHR_NGP_NOUNYU as delivery_date
                    FROM TT_GIG_SYKMEISAI WITH (NOLOCK)
                    WHERE CHR_TIM_SYUKKA IS NOT NULL
                        AND (
                            (CHR_NGP_NOUNYU = '{$date}' AND CHR_TIM_SYUKKA >= '100000')
                            OR 
                            (CHR_NGP_NOUNYU = '{$nextDate}' AND CHR_TIM_SYUKKA < '104000')
                        )
                        AND CHR_MEI_NOUNYU NOT IN ('{$excludedCustomersString}')
                        AND RTRIM(CHR_COD_SEBANGOU) IN ('{$backNosString}')
                    ORDER BY CHR_COD_SEBANGOU";

                return collect(DB::connection('mssql_external')->select($sql))->map(function ($item) use ($selectedDate, $prodTimeByBackNo) {
                    $item->back_no = trim($item->back_no);

                    $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                    $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                    $time = $deliveryDate->copy()->setTime(
                        substr($timeStr, 0, 2),
                        substr($timeStr, 2, 2),
                        substr($timeStr, 4, 2)
                    );

                    return (object)[
                        'customer' => $item->customer,
                        'dock' => $item->dock,
                        'cycle' => $item->cycle,
                        'back_no' => $item->back_no,
                        'qty_per_pallet' => $item->qty_per_pallet,
                        'order_qty' => $item->order_qty,
                        'dn_number' => $item->dn_number,
                        'formatted_time' => $time->format('H:i'),
                        'time_sort' => $time->timestamp,
                        'prod_time' => $prodTimeByBackNo[$item->back_no] ?? '00:00',
                        'delivery_date' => $item->delivery_date
                    ];
                });
            } catch (\Exception $e) {
                Log::warning('Laravel DB raw query failed: ' . $e->getMessage());
                return collect();
            }
        }
    }

    protected function processRawData($rawData, $start, $end)
    {
        return $rawData
            ->groupBy(function ($item) {
                $dock = trim((string) $item->dock);

                if ($dock === '6I') {
                    return $item->delivery_date . '|' . $item->formatted_time . '|' . $item->back_no;
                }

                return $item->dn_number . '|' . $item->back_no;
            })
            ->map(function ($group) {
                $first = $group->first();
                $first->order_qty = $group->sum('order_qty');
                return $first;
            })
            ->values();
    }

    protected function updateProductionData($processedData, $backNosByLine, $today)
    {
        foreach ($backNosByLine as $line => $backNos) {
            $startWorkingTime = $today->copy()->setTime(6, 0, 0);

            $lineData = $processedData
                ->filter(function ($item) use ($backNos) {
                    return in_array($item->back_no, $backNos);
                })
                ->sortBy('time_sort')
                ->groupBy('dn_number');

            foreach ($lineData as $dnNumber => $group) {
                $group = $group->sortBy('back_no')->values();
                $customer = $group->first()->customer;
                $deliveryTime = $group->first()->formatted_time;

                // Calculate working times for the group
                $currentWorkingTime = $startWorkingTime->copy();
                foreach ($group as $item) {
                    [$mm, $ss] = explode(':', $item->prod_time);
                    $prodSeconds = ((int)$mm * 60) + (int)$ss;
                    $totalSeconds = $prodSeconds * (int)$item->order_qty;

                    $workingStart = $currentWorkingTime->format('H:i');
                    $workingEnd = $currentWorkingTime->copy()->addSeconds($totalSeconds)->format('H:i');
                    $workingDuration = gmdate('H:i:s', $totalSeconds);

                    $currentWorkingTime->addSeconds($totalSeconds);
                }

                // Calculate balance time for the group
                $lastEnd = Carbon::createFromFormat('H:i', $workingEnd);
                $delivery = Carbon::parse($deliveryTime);

                if ($delivery->lt($lastEnd)) {
                    $delivery->addDay();
                }

                $balanceSeconds = $delivery->diffInSeconds($lastEnd, true);
                $isNegative = $balanceSeconds < 0;
                $formattedBalance = gmdate('H:i', abs($balanceSeconds));
                $balanceTime = $isNegative ? "-$formattedBalance" : $formattedBalance;

                // Update or create each record
                $currentWorkingTime = $startWorkingTime->copy();
                foreach ($group as $item) {
                    [$mm, $ss] = explode(':', $item->prod_time);
                    $prodSeconds = ((int)$mm * 60) + (int)$ss;
                    $totalSeconds = $prodSeconds * (int)$item->order_qty;

                    // Get existing record to preserve quantities
                    $existingRecord = ProductionPlan::where([
                        'plan_date' => $today->format('Y-m-d'),
                        'line' => $line,
                        'customer' => $customer,
                        'back_no' => $item->back_no,
                        'dn_number' => $item->dn_number
                    ])->first();

                    $updateData = [
                        'dock' => $item->dock,
                        'cycle' => $item->cycle,
                        'order_qty' => $item->order_qty,
                        'prod_time' => $item->prod_time,
                        'working_start' => $currentWorkingTime->format('H:i'),
                        'working_end' => $currentWorkingTime->copy()->addSeconds($totalSeconds)->format('H:i'),
                        'working_duration' => gmdate('H:i:s', $totalSeconds),
                        'delivery_time' => $deliveryTime,
                        'delivery_date' => Carbon::createFromFormat('Ymd', $item->delivery_date)->format('Y-m-d'),
                        'balance_time' => $balanceTime,
                        'updated_at' => now()
                    ];

                    // Preserve existing quantities if they exist
                    if ($existingRecord) {
                        $updateData['direct_pulling_qty'] = $existingRecord->direct_pulling_qty;
                        $updateData['stock_chute_qty'] = $existingRecord->stock_chute_qty;
                    } else {
                        $updateData['direct_pulling_qty'] = 0;
                        $updateData['stock_chute_qty'] = 0;
                        $updateData['created_at'] = now();
                    }

                    try {
                        ProductionPlan::updateOrCreate(
                            [
                                'plan_date' => $today->format('Y-m-d'),
                                'line' => $line,
                                'customer' => $customer,
                                'back_no' => $item->back_no,
                                'dn_number' => $item->dn_number
                            ],
                            $updateData
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to update production plan record: ' . $e->getMessage(), [
                            'plan_date' => $today->format('Y-m-d'),
                            'line' => $line,
                            'back_no' => $item->back_no,
                            'dn_number' => $item->dn_number
                        ]);
                    }

                    $currentWorkingTime->addSeconds($totalSeconds);
                }
                $startWorkingTime = $currentWorkingTime;
            }
        }
    }
}