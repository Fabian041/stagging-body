<?php

namespace App\Http\Controllers;

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
        $dayMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7];
        $startOfWeek = now()->startOfWeek(); // Senin
        $endOfWeek = now()->endOfWeek();     // Minggu

        $seriesData = [];

        $suppliers = Supplier::with('schedules')->get();

        foreach ($suppliers as $supplier) {
            $name = $supplier->name;

            if ($supplier->schedule_type === 'daily') {
                $schedule = $supplier->schedules->first(); // hanya satu jadwal
                if (!$schedule) continue;

                foreach ($dayMap as $day => $i) {
                    $start = $startOfWeek->copy()->addDays($i - 1)->setTimeFromTimeString($schedule->time);
                    $end = (clone $start)->addMinutes(120);

                    $seriesData[] = [
                        'x' => $name,
                        'y' => [$start->timestamp * 1000, $end->timestamp * 1000]
                    ];
                }
            } elseif ($supplier->schedule_type === 'daily_2x') {
                $schedules = $supplier->schedules;
                foreach ($dayMap as $day => $i) {
                    foreach ($schedules as $sched) {
                        $start = $startOfWeek->copy()->addDays($i - 1)->setTimeFromTimeString($sched->time);
                        $end = (clone $start)->addMinutes(120);
                        $seriesData[] = [
                            'x' => $name,
                            'y' => [$start->timestamp * 1000, $end->timestamp * 1000]
                        ];
                    }
                }
            } elseif ($supplier->schedule_type === 'custom') {
                foreach ($supplier->schedules as $sched) {
                    $dayIndex = $dayMap[$sched->day] ?? null;
                    if (!$dayIndex) continue;

                    $start = $startOfWeek->copy()->addDays($dayIndex - 1)->setTimeFromTimeString($sched->time);
                    $end = (clone $start)->addMinutes(120);
                    $seriesData[] = [
                        'x' => $name,
                        'y' => [$start->timestamp * 1000, $end->timestamp * 1000]
                    ];
                }
            }
        }

        $series = [[
            'name' => 'Jadwal Pengiriman',
            'data' => $seriesData
        ]];

        $annotations = now()->startOfDay()->timestamp * 1000; // ApexCharts pakai miliseconds


        return view('pages.dashboard_receiving', [
            'series' => $series,
            'annotationTimestamp' => $annotations
        ]);
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
