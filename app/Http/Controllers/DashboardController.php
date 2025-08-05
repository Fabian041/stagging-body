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
        $start = $today->copy()->addHours(12); // 12:00 selected date
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
                ->where(function($query) use ($deliveryDate, $nextDay) {
                    // Records from today with time >= 120000
                    $query->where(function($q) use ($deliveryDate) {
                        $q->where('CHR_NGP_NOUNYU', $deliveryDate)
                            ->where('CHR_TIM_SYUKKA', '>=', '120000');
                    })
                    // OR records from tomorrow with time < 120000
                    ->orWhere(function($q) use ($nextDay) {
                        $q->where('CHR_NGP_NOUNYU', $nextDay)
                            ->where('CHR_TIM_SYUKKA', '<', '120000');
                    });
                })
                ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU)"), $allBackNos);

            return $query->get()->map(function ($item) use ($today, $start, $prodTimeByBackNo) {
                $item->back_no = trim($item->back_no);

                $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                $time = $today->copy()->setTime(
                    substr($timeStr, 0, 2),
                    substr($timeStr, 2, 2),
                    substr($timeStr, 4, 2)
                );

                // Adjust date based on delivery date
                $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                if ($deliveryDate->gt($today)) {
                    $time->addDay();
                }

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
            \Log::warning('Laravel DB parameterized query failed: ' . $e->getMessage());
            
            try {
                $backNosString = implode("','", $allBackNos->map(fn($item) => trim($item))->toArray());
                $date = $selectedDate->format('Ymd');
                $nextDate = $selectedDate->copy()->addDay()->format('Ymd');
                
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
                            (CHR_NGP_NOUNYU = '{$date}' AND CHR_TIM_SYUKKA >= '120000')
                            OR 
                            (CHR_NGP_NOUNYU = '{$nextDate}' AND CHR_TIM_SYUKKA < '120000')
                        )
                        AND RTRIM(CHR_COD_SEBANGOU) IN ('{$backNosString}')
                    ORDER BY CHR_COD_SEBANGOU";
                
                return collect(DB::connection('mssql_external')->select($sql))->map(function ($item) use ($today, $start, $prodTimeByBackNo) {
                    $item->back_no = trim($item->back_no);
                    
                    $timeStr = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                    $time = $today->copy()->setTime(
                        substr($timeStr, 0, 2),
                        substr($timeStr, 2, 2),
                        substr($timeStr, 4, 2)
                    );

                    $deliveryDate = Carbon::createFromFormat('Ymd', $item->delivery_date);
                    if ($deliveryDate->gt($today)) {
                        $time->addDay();
                    }

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
                \Log::warning('Laravel DB raw query failed: ' . $e->getMessage());
                return collect();
            }
        }
    }

    protected function processRawData($rawData, $start, $end)
    {
        return $rawData
            ->groupBy(function ($item) {
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
                    
                    // $updateData = [
                    //     'dock' => $item->dock,
                    //     'cycle' => $item->cycle,
                    //     'order_qty' => $item->order_qty,
                    //     'prod_time' => $item->prod_time,
                    //     'working_start' => $currentWorkingTime->format('H:i'),
                    //     'working_end' => $currentWorkingTime->copy()->addSeconds($totalSeconds)->format('H:i'),
                    //     'working_duration' => gmdate('H:i:s', $totalSeconds),
                    //     'delivery_time' => $deliveryTime,
                    //     'delivery_date' => \Carbon\Carbon::createFromFormat('Ymd', $item->delivery_date)->format('Y-m-d'),
                    //     'balance_time' => $balanceTime,
                    //     'updated_at' => now()
                    // ];
                    
                    $updateData = [
                        'dock' => $item->dock,
                        'cycle' => $item->cycle,
                        'order_qty' => $item->order_qty,
                        'prod_time' => $item->prod_time,
                        'working_start' => null,
                        'working_end' => null,
                        'working_duration' => gmdate('H:i:s', $totalSeconds),
                        'delivery_time' => $deliveryTime,
                        'delivery_date' => \Carbon\Carbon::createFromFormat('Ymd', $item->delivery_date)->format('Y-m-d'),
                        'balance_time' => null,
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
                    
                    $currentWorkingTime->addSeconds($totalSeconds);
                }
                
                $startWorkingTime = $currentWorkingTime;
            }
        }
    }

    protected function getGroupedData($backNosByLine, $today)
    {
        $grouped = [];
        
        foreach ($backNosByLine as $line => $backNos) {
            $lineData = ProductionPlan::where('plan_date', $today->format('Y-m-d'))
                ->where('line', $line)
                ->orderBy('delivery_date') // or whatever column determines the original order
                ->orderBy('delivery_time') // or whatever column determines the original order
                ->get()
                ->groupBy(function($item) {
                    return $item->customer . '|' . $item->delivery_time;
                });
                
            $grouped[$line] = $lineData;
        }
        
        return $grouped;
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
        $endOfWeek = request('end_date') ? Carbon::parse(request('end_date')) : now()->endOfWeek();
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
        $data = DB::connection('mssql_external')
            ->table('IAA1NT as a')
            ->where('a.CHR_NUB_NYSJNO', $pickList)
            ->select(
                'a.CHR_COD_OMSS as supplier_code',
                'a.CHR_COD_HINB as part_number',
                'a.CHR_NUB_SBNG as back_number',
                'a.DEC_SUR_SHSU as qty_ordered',
                'a.DEC_SUR_HSSU as qty_confirmed',
                'a.CHR_INF_HTTN as uom',
                'a.CHR_NUB_NYSJNO as pick_list'
            )
            ->get();
        //dummy data
        // $data = [
        //     (object)[
        //         'supplier_code' => 'SUP123',
        //         'part_number' => 'PN123',
        //         'back_number' => 'BN123',
        //         'qty_ordered' => 100,
        //         'qty_confirmed' => 80,
        //         'uom' => 'pcs',
        //         'pick_list' => $pickList
        //     ],
        //     [
        //         'supplier_code' => 'SUP456',
        //         'part_number' => 'PN456',
        //         'back_number' => 'BN456',
        //         'qty_ordered' => 200,
        //         'qty_confirmed' => 150,
        //         'uom' => 'pcs',
        //         'pick_list' => $pickList
        //     ]
        // ];
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
