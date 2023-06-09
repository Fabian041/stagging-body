<?php

namespace App\Http\Controllers;

use App\Imports\PartImport;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Imports\ManifestImport;
use Illuminate\Support\Facades\DB;
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
            ->select('lines.name','production_stocks.internal_part_id as id','internal_parts.part_number','internal_parts.back_number', 'production_stocks.current_stock')
            ->groupBy('internal_parts.part_number','internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock')
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
}
