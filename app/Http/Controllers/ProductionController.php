<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Monolog\Logger;

use App\Models\Line;
use App\Models\Part;
use App\Models\Kanban;
use App\Models\Mutation;
use App\Models\Injection;
use PhpMqtt\ClientBuilder;
use Illuminate\Support\Str;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\KanbanAfterProd;
use App\Models\ProductionStock;
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

    public function as523()
    {
        return view('pages.production.as523');
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
        if (!$internalPart) {
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
        $kanban = Kanban::where('internal_part_id', $internalPart->id)
                    ->where('serial_number', $seri)
                    ->first();
        
        if(!$kanban){
            return [
                'status' => 'error',
                'message' => 'Kanban tidak terdaftar!'
            ]; 
        }

        if($kanban->status == 1){
            return [
                'status' => 'error',
                'message' => 'Kanban Sudah di scan!'
            ]; 
        }

        try {
            DB::beginTransaction();
            // insert into mutation table
            Mutation::create([
                'internal_part_id' => $internalPart->id,
                'serial_number' => $seri,
                'type' => 'supply',
                'qty' => $customerPart->qty_per_kanban,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            // update status in kanbans table
            $kanban->update([
                'status' => 1
            ]);

            $result = [];

            // get all current qty of all internal parts 
            $data = DB::table('internal_parts')
                ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
                ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
                ->select('lines.name', 'production_stocks.internal_part_id as id', 'internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.current_stock')
                ->groupBy('internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock')
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

            $this->mqttConnect('prod/quantity', $data);

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Part Sesuai Dengan Sample',
                'qty' => $customerPart->qty_per_kanban
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['message' => $th->getMessage()];
        }
    }

    public function adjust(Request $request)
    {
        $prod = ProductionStock::where('internal_part_id', $request->internal_part_id)->first();
        $current_stock = $prod->current_stock;
        $actual_stock = $request->current_stock;

        if ($actual_stock < $current_stock) {
            $type = 'checkout';
            $qty = $current_stock - $actual_stock;
        } else {
            $type = 'supply';
            $qty = $actual_stock - $current_stock;
        }

        try {
            DB::beginTransaction();

            if ($request->standard_stock !== null) {
                InternalPart::where('id', $request->internal_part_id)
                    ->update([
                        'standard_stock' => $request->standard_stock,
                    ]);
            }

            if ($actual_stock !== null) {
                Mutation::create([
                    'internal_part_id' => $request->internal_part_id,
                    'serial_number' => 'xxxx',
                    'qty' => $qty,
                    'type' => $type,
                    'npk' => auth()->user()->npk,
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Updated Successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error: ' . $th->getMessage());
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

    public function post(Request $request)
    {
        $validatedData = $request->validate([
            'read_code' => 'required',
            'line' => 'required',
            'npk' => 'required',
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $record = Injection::create($validatedData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Record created successfully',
                'data' => $record,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create record',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines || count($lines) < 2) {
            return response()->json(['message' => 'CSV kosong atau tidak valid.'], 400);
        }

        $header = str_getcsv(trim($lines[0]), ',');
        $rows = array_slice($lines, 1);
        $processed = [];
        $errors = [];

        foreach ($rows as $lineNumber => $line) {
            $fields = str_getcsv(trim($line), ',');
            $fields = array_slice($fields, 0, count($header)); // hapus kolom kosong di akhir

            if (count($header) !== count($fields)) {
                // Simpan error detail untuk dilaporkan atau di-log
                $errors[] = [
                    'line' => $lineNumber + 2, // +2 karena 0 based dan header baris 1
                    'content' => $line,
                    'message' => 'Jumlah kolom tidak sama dengan header'
                ];
                continue; // lewati baris bermasalah
            }

            $row = array_combine($header, $fields);

            $createdAt = $row['Time'] ?? null;
            if (!$createdAt) continue;

            // Cek apakah sudah ada berdasarkan created_at
            $existing = Injection::where('created_at', $createdAt)->first();

            $data = [
                'npk' => $row['NPK'] ?? null,
                'line' => $row['LINE'] ?? null,
                'status' => $row['STATUS'] ?? null,
                'readcode' => $row['READCODE'] ?? null,
                'created_at' => $createdAt,
            ];

            if ($existing) {
                $existing->update($data);
                $processed[] = ['action' => 'updated', 'created_at' => $createdAt];
            } else {
                Injection::create($data);
                $processed[] = ['action' => 'inserted', 'created_at' => $createdAt];
            }
        }

        return response()->json([
            'message' => 'CSV berhasil diproses.',
            'processed' => $processed,
            'errors' => $errors, // kirim error untuk dilihat client/debug
        ]);
    }

    public function scan($line)
    {
        // Ambil data line
        $data = DB::table('line_qty_temp')->where('line', $line)->first();

        if ($data) {
            // Jika sudah mencapai atau melebihi target, jangan increment
            if ($data->target !== null && $data->qty >= $data->target) {
                return response()->json([
                    'status' => 'done',
                    'message' => "Target tercapai untuk line: $line (qty: $data->qty, target: $data->target)",
                ]);
            }

            // Increment qty dan update timestamp
            DB::table('line_qty_temp')
                ->where('line', $line)
                ->update([
                    'qty' => DB::raw('qty + 1'),
                    'updated_at' => now(),
                ]);
        } else {
            // Insert baru dengan qty = 1
            DB::table('line_qty_temp')->insert([
                'line' => $line,
                'qty' => 1,
                'target' => null, // default jika belum diset
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Qty incremented for line: $line",
        ]);
    }

    public function getCurrentScanCount($line)
    {
        $record = DB::table('line_qty_temp')
            ->where('line', $line)
            ->first();

        return response()->json([
            'status' => 'success',
            'total_scan' => $record->qty ?? 0,
        ]);
    }
    
    public function updateScanTarget($line,$target)
    {
        $exists = DB::table('line_qty_temp')->where('line', $line)->exists();

        if ($exists) {
            // Increment qty dan update timestamp
            DB::table('line_qty_temp')
                ->where('line', $line)
                ->update([
                    'target' => $target,
                    'updated_at' => now(),
                ]);
        } 

        return response()->json([
            'status' => 'success',
            'message' => "Target for line: $line updated",
        ]);
    }

    public function resetScanCount($line)
    {
        DB::table('line_qty_temp')
            ->where('line', $line)
            ->update([
                'qty'        => 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Counter for line {$line} has been reset.",
        ]);
    }
}
