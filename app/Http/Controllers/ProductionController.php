<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Monolog\Logger;

use App\Models\Line;
use App\Models\Part;
use App\Models\Mutation;
use PhpMqtt\ClientBuilder;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
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
        $server   = 'broker.emqx.io';
        $port     = 1883;
        $clientId = '1234';
        $username = 'fabian';
        $password = '1234';
        $clean_session = false;
        $mqtt_version = MqttClient::MQTT_3_1_1;

        $connectionSettings = (new ConnectionSettings())
            ->setUsername($username)
            ->setPassword($password)
            ->setKeepAliveInterval(60)
            ->setLastWillTopic('test')
            ->setLastWillMessage('client disconnect')
            ->setLastWillQualityOfService(1);

        $mqtt = new MqttClient($server, $port, $clientId, $mqtt_version);

        try {
            $mqtt->connect($connectionSettings, $clean_session);
            printf("Client connected\n");

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

    public function test()
    {
        $data[] = [
            'line' => "AS526",
            'items' => [
                [
                    'back_number' => 'MP22',
                    'qty' => 129
                ],
                [
                    'back_number' => 'KP46',
                    'qty' => 80
                ]
            ],
            
        ];
        
        $this->mqttConnect('prod/quantity' , $data);
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

        // get line of internal part based on internal part id
        $line = Line::select('name')->where('id', $internalPart->line_id)->first();

        // get customer internalPart based on internal internalPart id
        $customerPart = CustomerPart::select('qty_per_kanban')->where('internal_part_id', $internalPart->id)->first();
        
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
                'internal_part_id' => $internalPart->id,
                'kanban_seri' => $seri,
                'qty' => $customerPart->qty_per_kanban,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $data[] = [
                'line' => $line->name,
                'back_number' => $internalPart->back_number,
                'qty' => $customerPart->qty_per_kanban
            ];

            $this->mqttConnect('prod/qty' , $data);

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
