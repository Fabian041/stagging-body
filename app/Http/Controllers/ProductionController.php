<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Line;

use App\Models\Part;
use App\Models\Mutation;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.production.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $partNumber = $request->partNumber;

        // double check to master sample
        $internalPart = InternalPart::where('part_number', $partNumber)->first();

        // get customer internalPart based on internal internalPart id
        $customerPart = CustomerPart::select('qty_per_kanban')->where('internalPart_id', $internalPart->id)->first();
        
        if(!$internalPart){
            return [
                'status' => 'error',
                'message' => 'Part Tidak Sesuai Dengan Sample!'
            ];
        }

        try {
            DB::beginTransaction();
            // insert into mutation table
            Mutation::create([
                'part_id' => $internalPart->id,
                'qty' => $customerPart->qty_per_box,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }

        return [
            'status' => 'success',
            'message' => 'Part Sesuai Dengan Sample'
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * check available line on table.
     *
     * @param  string  $line
     */
    public function lineCheck($line)
    {
        $line = Line::where('name', $line)->first();
        if (!$line) {
            return [
                'status' => 'error',
                'message' => 'Line tidak ditemukan'
            ];
        }

        return [
            "status" => 'success',
            "line" => $line->name,
        ];
    }

    /**
     * check available line on table.
     *
     * @param  string  $line
     */
    public function sampleCheck($line, $sample)
    {
        // get line id
        $line = Line::select('id')->where('name', $line)->first();

        // check if the sample is in the correct line id
        $sampleCheck = InternalPart::where('line_id', $line->id)
                    ->where('part_number', $sample)
                    ->first();

        if (!$sampleCheck) {
            return [
                'status' => 'error',
                'message' => 'Master sample tidak ditemukan'
            ];
        }

        return [
            "status" => 'success',
            "sample" => $sample,
        ];
    }    
}
