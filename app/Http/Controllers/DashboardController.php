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
    
    public function prodPlan()
    {
        $today = now()->startOfDay();
        $start = $today->copy()->addHours(6);
        $end = $start->copy()->addDay();

        $backNosByLine = [
            'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18'],
            'AS004' => ['CI15', 'CI16', 'CI19'],
        ];

        $prodTimes = [
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

        $rawData = DB::connection('mssql_external')
            ->table('TT_GIG_SYKMEISAI')
            ->select(
                'CHR_MEI_NOUNYU as customer',
                'INT_NUB_NOUBIN as cycle',
                'CHR_COD_SEBANGOU as back_no',
                'INT_SUR_SYUUYOU as qty_per_pallet',
                'INT_SUR_JYUCYUU as order_qty',
                'CHR_TIM_SYUKKA'
            )
            ->where('CHR_NGP_NOUNYU', now()->format('Ymd'))
            ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU)"), $allBackNos)
            ->limit(1000)
            ->get()
            ->map(function ($item) use ($prodTimes) {
                $item->back_no = trim($item->back_no);
                $item->formatted_time = $prodTimes[$item->back_no] ?? '--';

                $raw = str_pad($item->CHR_TIM_SYUKKA, 6, '0', STR_PAD_LEFT);
                $hour = substr($raw, 0, 2);
                $minute = substr($raw, 2, 2);
                $item->delivery_time = "$hour:$minute";

                return $item;
            })
            ->groupBy(function ($item) {
                return $item->customer . '|' . $item->back_no . '|' . $item->cycle . '|' . $item->delivery_time;
            })
            ->map(function ($group) {
                $first = $group->first();
                $first->order_qty = $group->sum('order_qty');
                return $first;
            })
            ->values(); // reset keys

        // ✳️ Grouping dan penggabungan berdasarkan 3 key: back_no, delivery_time, customer
        $groupedData = [];

        foreach ($rawData as $item) {
            $key = $item->customer . '|' . $item->back_no . '|' . $item->delivery_time;

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = clone $item;
            } else {
                $groupedData[$key]->order_qty += $item->order_qty;
            }
        }

        $groupedCollection = collect($groupedData)->values();

        $grouped = [];

        foreach ($backNosByLine as $line => $backNos) {
            $grouped[$line] = $groupedCollection
                ->whereIn('back_no', $backNos)
                ->groupBy(fn($item) => $item->customer);
        }

        return view('pages.pulling.prodPlan', [
            'grouped' => $grouped
        ]);
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
        $statusColors = [
            0 => '#cccccc', // Default / tidak diketahui
            1 => '#007bff', // Terdaftar
            2 => '#f52899', // Dikirim
            3 => '#ffc107', // Diterima Sebagian
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
}
