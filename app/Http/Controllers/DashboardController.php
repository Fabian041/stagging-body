<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Supplier;
use App\Imports\PartImport;
use App\Models\Agstar\Ia31;
use App\Imports\StockImport;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Models\ProductionPlan;
use App\Imports\ManifestImport;
use App\Models\ReceiveSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index()
    {

        $lines = [];

        // get all current qty of all internal parts 
        $data = DB::table('internal_parts')
            ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->select('lines.name', 'production_stocks.internal_part_id as id', 'internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.current_stock', 'internal_parts.standard_stock')
            ->groupBy('internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock', 'internal_parts.standard_stock')
            ->get();

        foreach ($data as $value) {
            $lineFound = false;
            // Check if line already exists in $lines array
            foreach ($lines as $line) {
                if ($line->line === $value->name) {
                    $lineFound = true;
                    $line->items[] = [
                        'id' => $value->id,
                        'part_number' => $value->part_number,
                        'back_number' => $value->back_number,
                        'qty' => $value->current_stock,
                        'standard' => $value->standard_stock,
                    ];
                    break;
                }
            }
            // If line doesn't exist, create a new object and add it to $lines array
            if (!$lineFound) {
                $lineObject = (object) [
                    'line' => $value->name,
                    'items' => [
                        [
                            'id' => $value->id,
                            'part_number' => $value->part_number,
                            'back_number' => $value->back_number,
                            'qty' => $value->current_stock,
                            'standard' => $value->standard_stock,
                        ],
                    ],
                ];
                $lines[] = $lineObject;
            }
        }

        return view('pages.dashboard', [
            'lines' => $lines,
        ]);
    }

    public function prodResult(Request $request)
    {
        $selectedDate = $request->input('date') ?? now()->toDateString(); // default hari ini

        $data = DB::table('mutations')
            ->join('internal_parts', 'mutations.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->where('mutations.type', 'supply')
            ->whereDate('mutations.created_at', $selectedDate) // ✅ ambil berdasarkan tanggal input
            ->select(
                'internal_parts.back_number',
                'mutations.serial_number',
                'mutations.type',
                'mutations.qty',
                'mutations.date',
                'lines.name as line',
                'internal_parts.id as internal_part_id'
            )
            ->orderBy('mutations.date', 'desc')
            ->get();

        // Organisasi data
        $lines = [];
        foreach ($data as $value) {
            $lineKey = $value->line;
            $backNumber = $value->back_number;

            if (!isset($lines[$lineKey])) {
                $lines[$lineKey] = [
                    'line' => $lineKey,
                    'items' => []
                ];
            }

            if (!isset($lines[$lineKey]['items'][$backNumber])) {
                $lines[$lineKey]['items'][$backNumber] = [
                    'back_number' => $backNumber,
                    'internal_part_id' => $value->internal_part_id,
                    'details' => []
                ];
            }

            $lines[$lineKey]['items'][$backNumber]['details'][] = [
                'serial_number' => $value->serial_number,
                'qty' => $value->qty,
                'date' => $value->date,
            ];
        }

        $result = [];
        foreach ($lines as $line) {
            $line['items'] = array_values($line['items']);
            $result[] = (object) $line;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20; // bebas, kamu bisa ubah sesuai kebutuhan

        $collection = collect($result);
        $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedResult = new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );


        return view('pages.production.prodResult', [
            'lines' => $paginatedResult,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function prodPlan(Request $request)
    {
        set_time_limit(90);

        // Get the date from request or use today
        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10); // 12:00 selected date
        $end = $start->copy()->addDay();      // 12:00 next day

        $backNosByLine = [
            'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18', 'D403', 'D111'],
            'AS004' => ['CI15', 'CI16', 'CI19', 'D500'],
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

        // Check if we should force refresh (triggered by the button)
        $forceRefresh = $request->has('force_refresh');

        // Check if we have fresh data in database (last 30 minutes)
        $lastUpdate = ProductionPlan::where('plan_date', $today->format('Y-m-d'))
            ->max('updated_at');

        if ($forceRefresh || !$lastUpdate || ($lastUpdate instanceof \Carbon\Carbon && $lastUpdate->diffInMinutes(now()) > 30)) {
            try {
                DB::beginTransaction();

                // Fetch new data
                $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $prodTimeByBackNo, $selectedDate);

                if ($rawData->isEmpty()) {
                    throw new \Exception("No production data available");
                }

                $processedData = $this->processRawData($rawData, $start, $end);

                // Update or create records instead of replacing
                $this->updateProductionData($processedData, $backNosByLine, $today);

                DB::commit();

                $message = 'Production data updated successfully!';
                $messageType = 'success';
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Production data processing failed: ' . $e->getMessage());

                $message = 'Failed to update data: ' . $e->getMessage();
                $messageType = 'error';

                $lastData = ProductionPlan::where('plan_date', $today->copy()->subDay()->format('Y-m-d'))
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($lastData) {
                    $message = 'Using cached data from previous day';
                    $messageType = 'warning';
                }
            }
        }

        $grouped = $this->getGroupedData($backNosByLine, $today);

        return view('pages.pulling.prodPlan', [
            'grouped' => $grouped,
            'lastUpdate' => $lastUpdate ?? now(),
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'message' => $message ?? null,
            'messageType' => $messageType ?? null
        ]);
    }

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

    protected function getGroupedData($backNosByLine, $today)
    {
        $grouped = [];
        $todayISO = $today->toDateString();
        $nextISO  = $today->copy()->addDay()->toDateString();
        $THRESH_MIN = 9*60 + 40; // 09:40

        // helper: parse "H:i" -> menit
        $toMin = function ($t) {
            if (!$t) return null;
            try {
                // handle "H:i" or "HH:mm"
                [$h, $m] = array_map('intval', explode(':', $t));
                if (!is_numeric($h) || !is_numeric($m)) return null;
                return $h*60 + $m;
            } catch (\Throwable $e) {
                return null;
            }
        };

        foreach ($backNosByLine as $line => $backNos) {
            // Ambil data plan untuk selected date (seperti existing)
            $lineData = ProductionPlan::whereDate('plan_date', $todayISO)
                ->where('line', $line)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->get();

            // --- Filter berdasar DELIVERY ---
            $isMorning = function ($item) use ($todayISO, $THRESH_MIN, $toMin) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);
                if ($dd && $tm !== null) {
                    return ($dd === $todayISO) && ($tm >= $THRESH_MIN);
                }
                // Fallback ke working time kalau delivery kosong
                try {
                    if (!$item->working_start && !$item->working_end) return false;
                    $sh = $item->working_start ? (int)\Carbon\Carbon::createFromFormat('H:i', $item->working_start)->format('H') : null;
                    $eh = $item->working_end   ? (int)\Carbon\Carbon::createFromFormat('H:i', $item->working_end)->format('H')   : null;
                    $startMorning = ($sh !== null) && ($sh >= 10 && $sh <= 22);
                    $endMorning   = ($eh !== null) && ($eh >= 10 && $eh <= 22);
                    return $startMorning || $endMorning;
                } catch (\Throwable $e) {
                    \Log::warning("Morning fallback parse error: ".$e->getMessage(), ['item_id'=>$item->id??null]);
                    return false;
                }
            };

            $isNight = function ($item) use ($nextISO, $THRESH_MIN, $toMin) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);
                if ($dd && $tm !== null) {
                    return ($dd === $nextISO) && ($tm < $THRESH_MIN);
                }
                // Fallback ke working time kalau delivery kosong
                try {
                    if (!$item->working_start && !$item->working_end) return false;
                    $sh = $item->working_start ? (int)\Carbon\Carbon::createFromFormat('H:i', $item->working_start)->format('H') : null;
                    $eh = $item->working_end   ? (int)\Carbon\Carbon::createFromFormat('H:i', $item->working_end)->format('H')   : null;
                    $startNight = ($sh !== null) && ($sh >= 0 && $sh <= 9 || $sh === 23 || $sh >= 22);
                    $endNight   = ($eh !== null) && ($eh >= 0 && $eh <= 9 || $eh === 23 || $eh >= 22);
                    return $startNight || $endNight;
                } catch (\Throwable $e) {
                    \Log::warning("Night fallback parse error: ".$e->getMessage(), ['item_id'=>$item->id??null]);
                    return false;
                }
            };

            $morningItems = $lineData->filter($isMorning);
            $nightItems   = $lineData->filter($isNight);

            // Order qty per shift
            $morningShiftQty = (int) $morningItems->sum('order_qty');
            $nightShiftQty   = (int) $nightItems->sum('order_qty');

            // Actual (direct_pulling_qty) per shift
            $morningShiftActual = (int) $morningItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));
            $nightShiftActual   = (int) $nightItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));

            $grouped[$line] = [
                // tetap tampilkan semua data di tabel
                'data' => $lineData->groupBy(function ($item) {
                    $cust = trim((string)($item->customer ?? '')) ?: '--';
                    $dock = trim((string)($item->dock ?? '')) ?: '--';
                    $time = trim((string)($item->delivery_time ?? '')) ?: '--';
                    return "{$cust}|{$dock}|{$time}";
                }),

                // KPI sesuai rule baru
                'morning_shift_qty'    => $morningShiftQty,
                'night_shift_qty'      => $nightShiftQty,
                'total_qty'            => $morningShiftQty + $nightShiftQty,

                'morning_shift_actual' => $morningShiftActual,
                'night_shift_actual'   => $nightShiftActual,
                'total_actual'         => $morningShiftActual + $nightShiftActual,
            ];
        }

        return $grouped;
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

    public function progressPulling()
    {
        // get per delivery date 
        $cycle = DB::table('loading_lists')
            ->join('loading_list_details', 'loading_list_details.loading_list_id', 'loading_lists.id')
            ->select(DB::raw('SUM(kanban_qty) as total_kanban, SUM(actual_kanban_qty) as total_actual'), 'cycle')
            ->where('delivery_date', '2023-07-11')
            ->groupBy('cycle')
            ->get();

        return view('pages.progressPulling');
    }

    public function importPart(Request $request)
    {
        Excel::import(new PartImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Part berhasil diupload!');
    }

    public function importManifest(Request $request)
    {
        Excel::import(new ManifestImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Manifest berhasil diupload!');
    }

    public function importStock(Request $request)
    {
        Excel::import(new StockImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Stock berhasil diupload!');
    }

    //Receiving Dashboard
    public function receivingDashboard()
    {
        $startOfWeek = request('start_date') ? Carbon::parse(request('start_date')) : now()->startOfWeek();
        $endOfWeek = request('end_date') ? Carbon::parse(request('end_date')) : now()->startOfWeek()->addWeeks(2)->endOfWeek();
        // dd($startOfWeek->toDateString(), $endOfWeek->toDateString());

        $area = request('area') ? request('area') : 'unit';
        $statusColorss = [
            0 => '#cccccc', // Default / tidak diketahui
            1 => '#007bff', // Terdaftar
            2 => '#f52899', // Dikirim
            3 => '#ffc107', // Diterima Sebagian
            4 => '#28a745', // Diterima Semua
            5 => '#fd7e14', // Pengiriman Sebagian
        ];

        $statusColors = [
            0 => '#cccccc', // Default / tidak diketahui
            1 => '#cccccc', // Terdaftar
            2 => '#cccccc', // Dikirim
            3 => '#cccccc', // Diterima Sebagian
            4 => '#28a745', // Diterima Semua
            5 => '#fd7e14', // Pengiriman Sebagian
        ];

        // JOIN external_deliveries ke suppliers agar dapat nama supplier
        $deliveries = DB::table('external_deliveries')
            ->join('suppliers', 'external_deliveries.supplier_code', '=', 'suppliers.code')
            ->select(
                'external_deliveries.*',
                'suppliers.name as supplier_name'
            )
            ->whereBetween('delivery_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('area', 'LIKE', '%' . $area . '%') // Filter berdasarkan area
            ->get();

        $seriesData = [];

        foreach ($deliveries as $delivery) {
            $date = \Carbon\Carbon::parse($delivery->delivery_date);

            // Lewati data jika tidak termasuk minggu ini
            if (!$date->between($startOfWeek, $endOfWeek)) {
                continue;
            }

            // Format "00:HH:MM", ambil HH dan MM
            $timeParts = explode(':', $delivery->delivery_time);
            $hour = (int)($timeParts[1] ?? 0);
            $minute = (int)($timeParts[2] ?? 0);

            $start = $date->copy()->setTime($hour, $minute);
            $end = $start->copy()->addMinutes(120);

            $status = $delivery->status ?? 0;
            $color = $statusColors[$status] ?? '#cccccc';

            $seriesData[] = [
                'x' => $delivery->supplier_name,
                'y' => [$start->timestamp * 1000, $end->timestamp * 1000],
                'fillColor' => $color,
                'pick_list' => $delivery->pick_list, // ini penting untuk buka modal
            ];
        }

        $series = [[
            'name' => 'Pengiriman Aktual',
            'data' => $seriesData
        ]];
        $now = now();

        if ($now->between($startOfWeek, $endOfWeek)) {
            $annotations = $now->timestamp * 1000;
        } else {
            $annotations = null;
        }

        return view('pages.dashboard_receiving', [
            'series' => $series,
            'annotationTimestamp' => $annotations
        ]);
    }
    function applyTimeToDate(Carbon $date, string $time)
    {
        [$hour, $minute, $second] = explode(':', $time);
        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }

    public function getReceivingData(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $day = $request->input('day');

        $query = DB::table('receive_schedules')
            ->join('suppliers', 'receive_schedules.supplier_id', '=', 'suppliers.id')
            ->select('receive_schedules.*', 'suppliers.name as supplier_name');

        if ($supplierId) {
            $query->where('receive_schedules.supplier_id', $supplierId);
        }

        if ($day) {
            $query->where('receive_schedules.day', $day);
        }

        return response()->json($query->get());
    }

    public function showModal(Request $request)
    {
        $request->validate([
            'pick_list' => 'required|string',
        ]);
        $pickList = $request->pick_list;
        // $data = DB::connection('mssql_external')
        //     ->table('IAA1NT as a')
        //     ->where('a.CHR_NUB_NYSJNO', $pickList)
        //     ->select(
        //         'a.CHR_COD_OMSS as supplier_code',
        //         'a.CHR_COD_HINB as part_number',
        //         'a.CHR_NUB_SBNG as back_number',
        //         'a.DEC_SUR_SHSU as qty_ordered',
        //         'a.DEC_SUR_HSSU as qty_confirmed',
        //         'a.CHR_INF_HTTN as uom',
        //         'a.CHR_NUB_NYSJNO as pick_list'
        //     )
        //     ->get();
        // ambil picklist dari IA31NT terus dapat dec_cod_binid cari cod_binid di IA16NT
        $ia31nt = DB::connection('mssql_external')
            ->table('IA31NT as a')
            ->where('a.CHR_NUB_NYSJNO', $pickList)
            ->select(
                'a.DEC_COD_BINID as flight',
            )
            ->first();

        $data = DB::connection('mssql_external')
            ->table('IA16NT as a')
            ->where('a.DEC_COD_BINID', $ia31nt->flight)
            ->select(
                'a.CHR_NUB_MLNO as supplier_code',
                'a.CHR_COD_HINB as part_number',
                'a.DEC_SUR_KKSUH as qty_ordered',
                'a.CHR_INF_HTTN as uom'
            )
            ->get()
            ->map(function ($item) use ($pickList) {
                $item->pick_list = $pickList;

                $iaa1nt = DB::connection('mssql_external')
                    ->table('IAA1NT as a')
                    ->where('a.CHR_COD_HINB', $item->part_number)
                    ->select(
                        'a.CHR_NUB_SBNG as back_number',
                    )
                    ->first();

                $item->back_number = $iaa1nt->back_number; // Menambahkan custom2 dengan part_number
                return $item;
            });


        return response()->json($data);
    }

    public function kbnCheck()
    {
        return view('pages.production.kbnCheck');
    }

    public function kbnCheckSubmit(Request $request)
    {
        $request->validate([
            'back_number' => 'required',
            'serial_number' => 'required',
        ]);

        // Ambil data internal part berdasarkan back number
        $internalPart = InternalPart::where('back_number', $request->back_number)->first();

        // Jika tidak ditemukan, kembalikan dengan error
        if (!$internalPart) {
            return back()->with([
                'error' => "Back Number <strong>{$request->back_number}</strong> tidak ditemukan."
            ])->withInput();
        }

        // Ambil kanban berdasarkan internal_part_id dan serial_number
        $kanban = DB::table('kanbans')
            ->where('internal_part_id', $internalPart->id)
            ->where('serial_number', $request->serial_number)
            ->first();

        // Jika kanban tidak ditemukan
        if (!$kanban) {
            return view('pages.production.kbnCheck', compact('internalPart'))
                ->with([
                    'back_number' => $request->back_number,
                    'serial_number' => $request->serial_number,
                    'error' => "Serial Number <strong>{$request->serial_number}</strong> tidak ditemukan untuk Back Number <strong>{$request->back_number}</strong>."
                ]);
        }

        // Tampilkan hasil jika ditemukan
        return view('pages.production.kbnCheck', compact('internalPart', 'kanban'))
            ->with([
                'back_number' => $request->back_number,
                'serial_number' => $request->serial_number
            ]);
    }
}
