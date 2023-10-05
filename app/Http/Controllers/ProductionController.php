<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Monolog\Logger;

use App\Models\Line;
use App\Models\Part;
use App\Models\Kanban;
use App\Models\Mutation;
use PhpMqtt\ClientBuilder;
use Illuminate\Support\Str;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\KanbanAfterProd;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\View;
use PhpMqtt\Client\ConnectionSettings;


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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $partNumber = $request->partNumber;
        $seri = $request->seri;

        // double check to master sample
        $internalPart = InternalPart::where('part_number', $partNumber)->first();
        if(!$internalPart){
            return [
                'status' => 'error',
                'message' => 'Part Tidak Sesuai Dengan Sample!'
            ];
        }

        // get line of internal part based on internal part id
        $line = Line::select('name')->where('id', $internalPart->line_id)->first();

        // get customer internalPart based on internal internalPart id
        $customerPart = CustomerPart::select('qty_per_kanban')->where('internal_part_id', $internalPart->id)->first();

        // get kanban_id based on internal part id
        // $kanban = Kanban::select('id')
        //             ->where('internal_part_id', $internalPart->id)
        //             ->where('serial_number', $seri)
        //             ->first();
        // if(!$kanban){
        //     return [
        //         'status' => 'error',
        //         'message' => 'Kanban tidak terdaftar!'
        //     ]; 
        // }

        // check if kanban after prod is empty
        
        
        // try {
        //     DB::beginTransaction();
        //     // insert into mutation table
        //     Mutation::create([
        //         'internal_part_id' => $internalPart->id,
        //         'serial_number' => $seri,
        //         'type' => 'supply',
        //         'qty' => $customerPart->qty_per_kanban,
        //         'npk' => auth()->user()->npk,
        //         'date' => Carbon::now()->format('Y-m-d H:i:s')
        //     ]);

        //     // insert into kanban after prod
        //     for($i=0; $i<$customerPart->qty_per_kanban; $i++){

        //         $randomString = Str::rand(7);
        //         $currDate = Carbon::now()->format('Ymd');

        //         KanbanAfterProd::create([
        //             'kanban_id' => $kanban->id,
        //             'internal_part_id' => $internalPart->id,
        //             'code' => $currDate . $randomString,
        //             'npk' => auth()->user()->npk,
        //             'date' => Carbon::now()->format('Y-m-d')
        //         ]);
        //     }

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

            $this->mqttConnect('prod/quantity' , $data);

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Part Sesuai Dengan Sample'
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
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
