<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\ProductionPlan;
use Illuminate\Support\Facades\DB;

trait prodPlanOps
{
    protected function fetchWithLaravelDB($today, $start, $allBackNos, $prodTimeByBackNo, $selectedDate)
    {
        try {
            $deliveryDate = $selectedDate->format('Ymd');
            $nextDay = $selectedDate->copy()->addDay()->format('Ymd');

            $excludedCustomers = [
                'TMMIN ASSY PLANT',
                'ADM SERVICE PART DIVISION',
                'TMMIN SERVICE PARTS DIVISION',
                'TAM SPARE PART DIVISION (DAIHATSU)',
                'PT MITSUBISHI MOTORS KRAMAYUDHA SALES ID'
            ];

            $query = DB::connection('mssql_external')
                ->table('TT_GIG_SYKMEISAI')
                ->select(
                    'CHR_MEI_NOUNYU as customer',
                    'CHR_COD_UKEIRE as dock',
                    'INT_NUB_NOUBIN as cycle',
                    DB::raw('RTRIM(CHR_COD_SEBANGOU) as back_no'),
                    DB::raw('RTRIM(CHR_COD_SEBANGOU_TOK) as back_no_tok'),
                    'INT_SUR_SYUUYOU as qty_per_pallet',
                    'INT_SUR_JYUCYUU as order_qty',
                    'CHR_TIM_SYUKKA',
                    'CHR_COD_TKS_NOUBAN as dn_number',
                    'CHR_NGP_NOUNYU as delivery_date'
                )
                ->whereNotNull('CHR_TIM_SYUKKA')
                ->where(function ($query) use ($deliveryDate, $nextDay) {
                    $threshold = '104000'; // 09:40:00

                    $query->where(function ($q) use ($deliveryDate, $threshold) {
                            $q->where('CHR_NGP_NOUNYU', $deliveryDate)
                            ->where('CHR_TIM_SYUKKA', '>=', $threshold); // mulai 09:40 di deliveryDate
                        })
                        ->orWhere(function ($q) use ($nextDay, $threshold) {
                            $q->where('CHR_NGP_NOUNYU', $nextDay)
                            ->where('CHR_TIM_SYUKKA', '<', $threshold);  // < 09:40 di nextDay
                        });
                })
                ->whereNotIn('CHR_MEI_NOUNYU', $excludedCustomers)
                // Penting: WHERE IN menyesuaikan kolom berdasarkan dock
                ->where(function ($q) use ($allBackNos) {
                    $q->where(function ($qq) use ($allBackNos) {
                            // non-6I → pakai CHR_COD_SEBANGOU
                            $qq->where('CHR_COD_UKEIRE', '<>', '6I')
                            ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU)"), $allBackNos);
                        })
                    ->orWhere(function ($qq) use ($allBackNos) {
                            // 6I → pakai CHR_COD_SEBANGOU_TOK
                            $qq->where('CHR_COD_UKEIRE', '6I')
                            ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU_TOK)"), $allBackNos);
                        });
                });

            return $query->get()->map(function ($item) use ($prodTimeByBackNo) {
                // Tentukan sumber back_no sesuai dock
                $rawBackNo = (trim($item->dock) === '6I')
                    ? ($item->back_no_tok ?? '')
                    : ($item->back_no ?? '');

                $backNo = trim($rawBackNo);

                // Time handling
                $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                $time = $deliveryDate->copy()->setTime(
                    substr($timeStr, 0, 2),
                    substr($timeStr, 2, 2),
                    substr($timeStr, 4, 2)
                );

                return (object)[
                    'customer'        => $item->customer,
                    'dock'            => $item->dock,
                    'cycle'           => $item->cycle,
                    'back_no'         => $backNo,
                    'qty_per_pallet'  => $item->qty_per_pallet,
                    'order_qty'       => $item->order_qty,
                    'dn_number'       => $item->dn_number,
                    'formatted_time'  => $time->format('H:i'),
                    'time_sort'       => $time->timestamp,
                    'prod_time'       => $prodTimeByBackNo[$backNo] ?? '00:00',
                    'delivery_date'   => $item->delivery_date,
                ];
            });
        } catch (\Exception $e) {
            \Log::warning('Laravel DB parameterized query failed: ' . $e->getMessage());

            try {
                $backNosString = implode("','", $allBackNos->map(fn($item) => trim($item))->toArray());
                $date = $selectedDate->format('Ymd');
                $nextDate = $selectedDate->copy()->addDay()->format('Ymd');
                $excludedCustomersString = implode("','", [
                    'TMMIN ASSY PLANT',
                    'ADM SERVICE PART DIVISION',
                    'TMMIN SERVICE PARTS DIVISION',
                    'TAM SPARE PART DIVISION (DAIHATSU)',
                    'PT MITSUBISHI MOTORS KRAMAYUDHA SALES ID',
                ]);

                // Penting: SELECT back_no_tok & WHERE IN kondisional sesuai dock
                $sql = "SELECT 
                            CHR_MEI_NOUNYU as customer,
                            CHR_COD_UKEIRE as dock,
                            INT_NUB_NOUBIN as cycle,
                            RTRIM(CHR_COD_SEBANGOU) as back_no,
                            RTRIM(CHR_COD_SEBANGOU_TOK) as back_no_tok,
                            INT_SUR_SYUUYOU as qty_per_pallet,
                            INT_SUR_JYUCYUU as order_qty,
                            CHR_TIM_SYUKKA,
                            CHR_COD_TKS_NOUBAN as dn_number,
                            CHR_NGP_NOUNYU as delivery_date
                        FROM TT_GIG_SYKMEISAI WITH (NOLOCK)
                        WHERE CHR_TIM_SYUKKA IS NOT NULL
                        AND (
                                (CHR_NGP_NOUNYU = '{$date}' AND CHR_TIM_SYUKKA >= '104000')
                            OR (CHR_NGP_NOUNYU = '{$nextDate}' AND CHR_TIM_SYUKKA < '104000')
                        )
                        AND CHR_MEI_NOUNYU NOT IN ('{$excludedCustomersString}')
                        AND (
                                (CHR_COD_UKEIRE = '6I'  AND RTRIM(CHR_COD_SEBANGOU_TOK) IN ('{$backNosString}'))
                            OR (CHR_COD_UKEIRE <> '6I' AND RTRIM(CHR_COD_SEBANGOU)     IN ('{$backNosString}'))
                        )
                        ORDER BY CHR_COD_SEBANGOU";

                return collect(DB::connection('mssql_external')->select($sql))->map(function ($item) use ($prodTimeByBackNo) {
                    $dock = trim($item->dock ?? '');
                    $rawBackNo = ($dock === '6I') ? ($item->back_no_tok ?? '') : ($item->back_no ?? '');
                    $backNo = trim($rawBackNo);

                    $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                    $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                    $time = $deliveryDate->copy()->setTime(
                        substr($timeStr, 0, 2),
                        substr($timeStr, 2, 2),
                        substr($timeStr, 4, 2)
                    );

                    return (object)[
                        'customer'        => $item->customer,
                        'dock'            => $item->dock,
                        'cycle'           => $item->cycle,
                        'back_no'         => $backNo,
                        'qty_per_pallet'  => $item->qty_per_pallet,
                        'order_qty'       => $item->order_qty,
                        'dn_number'       => $item->dn_number,
                        'formatted_time'  => $time->format('H:i'),
                        'time_sort'       => $time->timestamp,
                        'prod_time'       => $prodTimeByBackNo[$backNo] ?? '00:00',
                        'delivery_date'   => $item->delivery_date
                    ];
                });
            } catch (\Exception $e) {
                \Log::warning('Laravel DB raw query failed: ' . $e->getMessage());
                return collect();
            }
        }
    }

    protected function processRawData($rawData, $start, $end)
    {
        return $rawData
            ->groupBy(function ($item) {
                // Paksa cast ke string dulu sebelum di-trim untuk mencegah error
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
        // Filter hanya back_no yang ada di mapping dan kelompokkan per line efektif
        $itemsByLine = $processedData
            ->filter(fn($it) => $this->isAllowedBackNo($it->back_no, $backNosByLine))
            ->groupBy(fn($it) => $this->resolveLineForItem($it, $backNosByLine));

        foreach ($itemsByLine as $line => $items) {
            $startWorkingTime = $today->copy()->setTime(6, 0, 0);

            // Urutkan per waktu, lalu kelompokkan per DN
            $lineData = $items->sortBy('time_sort')->groupBy('dn_number');

            foreach ($lineData as $dnNumber => $group) {
                $group = $group->sortBy('back_no')->values();
                $customer     = $group->first()->customer;
                $deliveryTime = $group->first()->formatted_time;

                // Hitung working time untuk grup
                $currentWorkingTime = $startWorkingTime->copy();
                $workingEnd = $currentWorkingTime->format('H:i'); // inisialisasi

                foreach ($group as $it) {
                    [$mm, $ss] = explode(':', $it->prod_time);
                    $prodSeconds = ((int) $mm * 60) + (int) $ss;
                    $totalSeconds = $prodSeconds * (int) $it->order_qty;

                    $workingStart   = $currentWorkingTime->format('H:i');
                    $workingEnd     = $currentWorkingTime->copy()->addSeconds($totalSeconds)->format('H:i');
                    $workingDuration= gmdate('H:i:s', $totalSeconds);

                    $currentWorkingTime->addSeconds($totalSeconds);
                }

                // Hitung balance time untuk grup (pakai akhir terakhir di grup)
                $lastEnd  = Carbon::createFromFormat('H:i', $workingEnd);
                $delivery = Carbon::createFromFormat('H:i', $deliveryTime);

                if ($delivery->lt($lastEnd)) {
                    $delivery->addDay();
                }

                $balanceSeconds   = $delivery->diffInSeconds($lastEnd, true);
                $isNegative       = $balanceSeconds < 0;
                $formattedBalance = gmdate('H:i', abs($balanceSeconds));
                $balanceTime      = $isNegative ? "-$formattedBalance" : $formattedBalance;

                // Simpan setiap item dalam grup
                $currentWorkingTime = $startWorkingTime->copy();
                foreach ($group as $it) {
                    [$mm, $ss]  = explode(':', $it->prod_time);
                    $prodSeconds= ((int) $mm * 60) + (int) $ss;
                    $totalSec   = $prodSeconds * (int) $it->order_qty;

                    $baseLine   = $this->getBaseLineByBackNo($it->back_no, $backNosByLine) ?? $line; // untuk cleanup jika pindah

                    // Cari record eksisting di line tujuan dulu
                    $existingRecord = ProductionPlan::where([
                        'plan_date' => $today->format('Y-m-d'),
                        'line'      => $line,
                        'customer'  => $customer,
                        'back_no'   => $it->back_no,
                        'dn_number' => $it->dn_number
                    ])->first();

                    // Jika belum ada (mungkin sebelumnya tersimpan di line lain), cari tanpa filter line
                    if (!$existingRecord) {
                        $existingRecord = ProductionPlan::where([
                            'plan_date' => $today->format('Y-m-d'),
                            'customer'  => $customer,
                            'back_no'   => $it->back_no,
                            'dn_number' => $it->dn_number
                        ])->first();
                    }

                    $updateData = [
                        'dock'             => $it->dock,
                        'cycle'            => $it->cycle,
                        'order_qty'        => $it->order_qty,
                        'prod_time'        => $it->prod_time,
                        'working_start'    => $currentWorkingTime->format('H:i'),
                        'working_end'      => $currentWorkingTime->copy()->addSeconds($totalSec)->format('H:i'),
                        'working_duration' => gmdate('H:i:s', $totalSec),
                        'delivery_time'    => $deliveryTime,
                        'delivery_date'    => \Carbon\Carbon::createFromFormat('Ymd', $it->delivery_date)->format('Y-m-d'),
                        'balance_time'     => $balanceTime,
                        'updated_at'       => now(),
                    ];

                    // Preserve actuals jika ada
                    if ($existingRecord) {
                        $updateData['direct_pulling_qty'] = $existingRecord->direct_pulling_qty;
                        $updateData['stock_chute_qty']    = $existingRecord->stock_chute_qty;
                    } else {
                        $updateData['direct_pulling_qty'] = 0;
                        $updateData['stock_chute_qty']    = 0;
                        $updateData['created_at']         = now();
                    }

                    try {
                        // Simpan ke line tujuan (efektif)
                        ProductionPlan::updateOrCreate(
                            [
                                'plan_date' => $today->format('Y-m-d'),
                                'line'      => $line,
                                'customer'  => $customer,
                                'back_no'   => $it->back_no,
                                'dn_number' => $it->dn_number
                            ],
                            $updateData
                        );

                        // Jika item ini pindah line (base != efektif), hapus record lama agar tidak dobel
                        if ($baseLine !== $line) {
                            ProductionPlan::where([
                                'plan_date' => $today->format('Y-m-d'),
                                'line'      => $baseLine,
                                'customer'  => $customer,
                                'back_no'   => $it->back_no,
                                'dn_number' => $it->dn_number
                            ])->delete();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to update ProductionPlan: '.$e->getMessage(), [
                            'line' => $line,
                            'customer' => $customer,
                            'back_no' => $it->back_no,
                            'dn_number' => $it->dn_number
                        ]);
                    }

                    $currentWorkingTime->addSeconds($totalSec);
                }

                // Update startWorkingTime untuk grup berikutnya di line ini
                $startWorkingTime = $currentWorkingTime;
            }
        }
    }
    
    protected function getBaseLineByBackNo(string $backNo, array $backNosByLine): ?string
    {
        foreach ($backNosByLine as $line => $backs) {
            if (in_array(strtoupper(trim($backNo)), array_map(fn($b) => strtoupper(trim($b)), $backs), true)) {
                return $line;
            }
        }
        return null;
    }

    /** Apakah back_no termasuk dalam mapping yang diizinkan */
    protected function isAllowedBackNo(string $backNo, array $backNosByLine): bool
    {
        return (bool) $this->getBaseLineByBackNo($backNo, $backNosByLine);
    }

    /** Line efektif per item (boleh override aturan base) */
    protected function resolveLineForItem(object $item, array $backNosByLine): string
    {
        $base = $this->getBaseLineByBackNo($item->back_no, $backNosByLine) ?? 'AS003';

        $backNo   = strtoupper(trim((string) $item->back_no));
        $customer = strtoupper(trim((string) $item->customer));
        $dock     = strtoupper(trim((string) $item->dock));

        // OVERRIDE: CI13 + ADM ENGINE PLANT + EXP -> AS004
        if ($backNo === 'CI13' && $customer === 'ADM ENGINE PLANT' && $dock === 'EXP') {
            return 'AS004';
        }

        return $base;
    }
}