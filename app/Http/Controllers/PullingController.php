<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Part;
use App\Models\Kanban;
use GuzzleHttp\Client;
use App\Models\Pulling;
use App\Models\Customer;
use App\Models\Mutation;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\KanbanAfterProd;
use App\Models\KanbanAfterPulling;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\ConnectionSettings;

class PullingController extends Controller
{
    public function mqttConnect($topic, $message)
    {
        $server   = '172.18.3.70';
        $port     = 1883;
        $clientId = '1234';
        // $username = 'fabian';
        // $password = '1234';
        $clean_session = false;
        $mqtt_version = MqttClient::MQTT_3_1_1;

        $connectionSettings = (new ConnectionSettings())
            // ->setUsername($username)
            // ->setPassword($password)
            ->setKeepAliveInterval(600)
            ->setConnectTimeout(10)
            ->setLastWillTopic('test')
            ->setLastWillMessage('client disconnect')
            ->setLastWillQualityOfService(1);

        $mqtt = new MqttClient($server, $port, $clientId, $mqtt_version);

        try {
            $mqtt->connect($connectionSettings, $clean_session);
            
            $mqtt->publish(
                // topic
                $topic,
                // payload
                json_encode($message),
                // qos
                0,
                // retain
                false
            );
            sleep(1);
            
        } catch (\Exception $e) {
            // Handle the exception appropriately
            echo "Exception: " . $e->getMessage() . "\n";
        } finally {
            $mqtt->disconnect();
        }
    }
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

    public function mutation(Request $request)
    {
        $internal = $request->internalPart;
        $seri = $request->serialNumber;
        $qty = $request->qty_per_kbn;
        
        // get internal part id
        $internalPart = InternalPart::where('part_number', $internal)->first();

        if(!$internalPart){
            return [
                'status' => 'notExists',
                'message' => 'Part atau Kanban tidak ditemukan!'
            ];
        }

        try {
            DB::beginTransaction();
            // insert into mutation table
            Mutation::create([
                'internal_part_id' => $internalPart->id,
                'serial_number' => $seri,
                'type' => 'checkout',
                'qty' => $qty,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        $result = [];
        
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
                    foreach ($result as $line) {
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
                    // If line doesn't exist, create a new object and add it to $result array
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
                        $result[] = $lineObject;
                    }
                }
                    
            $this->mqttConnect('prod/quantity' , $result);
            
            DB::commit();

            return response()->json([
                'status' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
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
                        'part_number_int' => $val['part_number_internal'],
                        'part_number_cust' => $val['part_number_customer'],
                        'serial_number' => $val['serial_number']
                    ];
                }
            }
            $data[count($data) - 1]->items = (object) $result;
            $result = [];
        }
        // initialize new client
        $client = new Client([
            'verify' => false, // Temporarily disabling SSL verification
        ]);
        
        // post data
        for($i = 0; $i<count($data); $i++){
            $response = $client->post('https://dea-dev.aiia.co.id/api/v1/kanbans',[
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $data[$i],
            ]);
        }

        return ['status' => $response];
    }

    public function kanbanCheck(Request $request)
    {
        //get all req
        $internal = $request->internal;
        $seri = $request->seri;

        // get internal part number id
        $internalPart = InternalPart::select('id')->where('part_number', $internal)->first();
        if(!$internalPart){
            return response()->json([
                'status' => 'partNotExist',
                'message' => 'Part number tidak terdaftar'
            ], 404);
        }

        // check if kanban exist
        $kanban = Kanban::select('id')
                    ->where('internal_part_id', $internalPart->id)
                    ->where('serial_number', $seri)
                    ->first();
        if(!$kanban){
            return response()->json([
                'status' => 'kanbanNotExist',
                'message' => 'Kanban tidak terdaftar'
            ], 404);
        }

        // check if kanban already scanned by production
        $kanbanAfterProd = KanbanAfterProd::where('kanban_id', $kanban->id);
        if(!$kanbanAfterProd->first()){
            return response()->json([
                'status' => 'notScanned',
                'message' => 'Kanban belum discan produksi'
            ],404);
        }

        return ['status' => 'success'];
    }

    public function kanbanAfterPull(Request $request)
    {
        //get all req
        $internal = $request->internal;
        $seri = $request->seri;

        // get internal part number id
        $internalPart = InternalPart::select('id')->where('part_number', $internal)->first();

        // check if kanban exist
        $kanban = Kanban::select('id')
                    ->where('internal_part_id', $internalPart->id)
                    ->where('serial_number', $seri)
                    ->first();

        // check if kanban already scanned by production
        $kanbanAfterProd = KanbanAfterProd::where('kanban_id', $kanban->id)->first();
        
        try {
            DB::beginTransaction();

            // delete kanban id at kanban after prod table
            $kanbanAfterProd->update([
                'kanban_id' => null
            ]);

            // create data at kanban after pulls table
            KanbanAfterPulling::create([
                'kanban_id' => $kanban->id,
                'internal_part_id' => $internalPart->id,
                'code' => $kanbanAfterProd->code,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $kanban->id
            ],200);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ],500);
        }
    }
}
