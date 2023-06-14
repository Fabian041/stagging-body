<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Part;
use App\Models\Pulling;
use App\Models\Customer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $customer = $request->customer;
        $loadingList = $request->loadingList;
        $pdsNumber = $request->pdsNumber;
        $cycle = $request->cycle;

        // get customer id
        $customerId = Customer::select('id')->where('name', $customer)->first();

        try {
            DB::beginTransaction();

            // insert into pulling
            Pulling::create([
                'customer_id' => $customerId->id,
                'loading_list' => $loadingList,
                'pds_number' => $pdsNumber,
                'pulling_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'cycle' => (int) $cycle
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
        
        return [
            'status' => 'success',
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
    
    public function customerCheck($customer)
    {
        // check customer 
        $check = Customer::where('code', $customer)->first();
        if(!$check){
            return [
                'status' => 'error',
                'message' => 'Customer tidak ditemukan'
            ];
        }

        return [
            'status' => 'success',
            'customer' => $check->name,
            'first' => $check->char_first,
            'length' => $check->char_length,
            'total' => $check->char_total
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

    public function post(Request $request)
    {
        $loadingLists = $request->loadingList;
        $token = $request->token;
        $data = [];
        $result = [];
        
        // loop the loading list & restructure the array
        foreach($loadingLists as $loadingList => $items){
            array_push($data, (object) ['loading_list_number' => $loadingList]);
            // check if items belongs to loading list based on index of the array
            foreach($items as $item => $val){

                if(array_key_exists($loadingList, $loadingLists) && array_key_exists($item, $loadingLists[$loadingList])){
                    $result [] = [
                        'part_number_internal' => $val['part_number_internal'],
                        'part_number_customer' => $val['part_number_customer'],
                        'serial_number' => $val['serial_number']
                    ];
                }
            }
            $data[count($data) - 1]->items = (object) $result;
            $result = [];
        }
        // initialize new client
        $client = new Client();
        
        // post data
        for($i = 0; $i<count($data); $i++){
            $response = $client->post('http://api-dea-dev/api/v1/kanbans',[
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'body' => $data[$i],
            ]);
        }

        return ['status' => $response];
    }
}
