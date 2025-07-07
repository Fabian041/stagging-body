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
