<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Imports\PartImport;
use App\Imports\StockImport;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Imports\ManifestImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {

        dd(Auth::check());
        $lines = [];
        
         // get all current qty of all internal parts 
        $data = DB::table('internal_parts')
            ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->select('lines.name','production_stocks.internal_part_id as id','internal_parts.part_number','internal_parts.back_number', 'production_stocks.current_stock', 'internal_parts.standard_stock')
            ->groupBy('internal_parts.part_number','internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock', 'internal_parts.standard_stock')
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
                
        return view('pages.dashboard',[
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
}
