<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Part;
use Illuminate\Http\Request;

class PullingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.pulling.index');
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
        //
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
    
    public function customerCheck($customer)
    {
        // check customer 
        $check = Customer::select('name')->where('code', $customer)->first();
        if(!$check){
            return [
                'status' => 'error',
                'message' => 'Customer tidak ditemukan'
            ];
        }

        return [
            'status' => 'success',
            'customer' => $check->name
        ];
    }

    public function internalCheck($internal)
    {
        // check internal 
        $check = Part::select('part_number','back_number')->where('part_number', $internal)->first();
        if(!$check){
            return [
                'status' => 'error',
                'message' => 'Part atau Kanban tidak ditemukan'
            ];
        }

        return [
            'status' => 'success',
            'customer' => $check->part_number
        ];
    }
}
