<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Supplier;
use App\Imports\PartImport;
use App\Models\Agstar\Ia31;
use App\Traits\prodPlanOps;
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
    use prodPlanOps;
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

    private array $backNosByLine = [
        'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18', 'D403', 'D111'],
        'AS004' => ['CI15', 'CI16', 'CI19', 'D500'],
    ];

    /** Waktu produksi per back_no (samakan dengan SSE) */
    private array $prodTimeByBackNo = [
        'CI11' => '00:34',
        'CI12' => '00:34',
        'CI13' => '00:40',
        'CI14' => '00:34',
        'CI15' => '00:39',
        'CI16' => '00:40',
        'CI17' => '00:40',
        'CI18' => '00:40',
        'CI19' => '00:37',
        'D403' => '00:40',
        'D111' => '00:34',
        'D500' => '00:37'
    ];

    public function prodPlan(Request $request)
    {
        set_time_limit(90);

        // Get the date from request or use today
        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10); // 12:00 selected date
        $end   = $start->copy()->addDay();     // 12:00 next day

        $allBackNos   = collect($this->backNosByLine)->flatten()->unique()->values();
        $forceRefresh = $request->has('force_refresh');

        // Cek data terakhir (30 menit)
        $lastUpdate = ProductionPlan::where('plan_date', $today->format('Y-m-d'))->max('updated_at');

        if ($forceRefresh || !$lastUpdate || ($lastUpdate instanceof \Carbon\Carbon && $lastUpdate->diffInMinutes(now()) > 30)) {
            try {
                DB::beginTransaction();

                // === Panggil pipeline dari TRAIT ===
                $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
                if ($rawData->isEmpty()) {
                    throw new \Exception("No production data available");
                }

                $processedData = $this->processRawData($rawData, $start, $end);
                $this->updateProductionData($processedData, $this->backNosByLine, $today);

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

        // Tabel + KPI (tetap di controller)
        $grouped = $this->getGroupedData($this->backNosByLine, $today);

        return view('pages.pulling.prodPlan', [
            'grouped'      => $grouped,
            'lastUpdate'   => $lastUpdate ?? now(),
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'message'      => $message ?? null,
            'messageType'  => $messageType ?? null,
        ]);
    }

    /** Khusus penyusunan data tabel + KPI shift (delivery-based 09:40 rule) */
    protected function getGroupedData($backNosByLine, $today)
    {
        $grouped   = [];
        $todayISO  = $today->toDateString();
        $nextISO   = $today->copy()->addDay()->toDateString();
        $THRESH_MIN= 9*60 + 40; // 09:40

        $toMin = function ($t) {
            if (!$t) return null;
            try {
                [$h, $m] = array_map('intval', explode(':', $t));
                if (!is_numeric($h) || !is_numeric($m)) return null;
                return $h*60 + $m;
            } catch (\Throwable $e) {
                return null;
            }
        };

        foreach ($backNosByLine as $line => $backNos) {
            $lineData = ProductionPlan::whereDate('plan_date', $todayISO)
                ->where('line', $line)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->get();

            $isMorning = function ($item) use ($todayISO, $THRESH_MIN, $toMin) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);
                if ($dd && $tm !== null) {
                    // Morning = delivery di HARI INI, time >= 09:40
                    return ($dd === $todayISO) && ($tm >= $THRESH_MIN);
                }
                // Fallback pakai working time jika delivery kosong
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
                    // Night = delivery di HARI BESOK, time < 09:40
                    return ($dd === $nextISO) && ($tm < $THRESH_MIN);
                }
                // Fallback pakai working time jika delivery kosong
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

            $morningShiftQty    = (int) $morningItems->sum('order_qty');
            $nightShiftQty      = (int) $nightItems->sum('order_qty');
            $morningShiftActual = (int) $morningItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));
            $nightShiftActual   = (int) $nightItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));

            $grouped[$line] = [
                'data' => $lineData->groupBy(function ($item) {
                    $cust = trim((string)($item->customer ?? '')) ?: '--';
                    $time = trim((string)($item->delivery_time ?? '')) ?: '--';
                    $dock = trim((string)($item->dock ?? '')) ?: '--';
                    return "{$cust}|{$time}|{$dock}";
                }),
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
