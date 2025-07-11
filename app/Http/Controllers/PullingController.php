<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Part;
use App\Models\Kanban;
use GuzzleHttp\Client;
use App\Models\Pulling;
use App\Models\Customer;
use App\Models\Mutation;
use App\Models\SkidDetail;
use App\Models\LoadingList;
use Illuminate\Support\Str;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\KanbanAfterProd;
use App\Models\LoadingListDetail;
use App\Models\KanbanAfterPulling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    public function convertPartNumber($loadingList, $customerPart)
    {
        // get part number length
        $codeLength = strlen($customerPart);

        // check last two digit of partNumber 
        $lastDigit = substr($customerPart, -2);

        $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        if (!$loadingListId) {
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!'
            ];
        }

        // check part number customer length
        if ($codeLength == 12) {
            // TMMIN
            if ($lastDigit != '00') {
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            } else {
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
            }
        } else if ($codeLength == 10) {
            if ($loadingListId->customer_id == 14) {
                // SUZUKI
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
            } else {
                if ($loadingListId->customer_id == 6) {
                    // MMKI
                    $convertedPartNumber = $customerPart;
                } else {
                    // TBINA
                    $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
                }
            }
        } else if ($codeLength == 13) {
            // SUZUKI
            if ($lastDigit != '000') {
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -3);
            } else {
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -3);
            }
        } else {
            $convertedPartNumber = $customerPart;
        }

        return $convertedPartNumber;
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

        return ['status' => 'success'];
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
        if (!$check) {
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

    public function internalCheck($internal, $isinternal = 0)
    {
        // check internal
        $internal = InternalPart::with('customerPart', 'line')->where('part_number', $internal)->first();
        if ($isinternal == 0) {
            DB::beginTransaction();
            // insert into mutation table
            Mutation::create([
                'internal_part_id' => $internal->id,
                'serial_number' => 'XXXX',
                'type' => 'checkout',
                'qty' => 0,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        }
        if (!$internal) {
            return [
                'status' => 'error',
                'message' => 'Part atau Kanban tidak ditemukan!'
            ];
        }

        return [
            'status' => 'success',
            'partNumber' => $internal->part_number,
            'backNumber' => $internal->back_number,
            'target' => $internal->customerPart->qty_per_kanban ?? 0,
            'line' => $internal->line->name ?? 'Tidak ada',
            'photo' => $internal->photo,
        ];
    }

    public function mutation(Request $request)
    {
        $internal = $request->internalPart;
        $seri = $request->serialNumber;
        $qty = $request->qty_per_kbn;

        // get internal part id
        $internalPart = InternalPart::where('part_number', $internal)->first();

        $kanban = Kanban::where('internal_part_id', $internalPart->id)
            ->where('serial_number', $seri)
            ->first();

        if(!$kanban){
            return [
                'status' => 'error',
                'message' => 'Kanban tidak terdaftar!'
            ]; 
        }

            if($kanban->status == 0){
                return [
                    'status' => 'error',
                    'message' => 'Kanban belum di scan produksi!'
                ]; 
            }else if($kanban->status == 2){
                return [
                    'status' => 'error',
                    'message' => 'Kanban sudah di scan!'
                ];
            }

        if (!$internalPart) {
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

            // update status kanbans table
            $kanban->update([
                'status' => 2
            ]);

            // commented for temporary
            // $result = [];

            // // get all current qty of all internal parts 
            // $data = DB::table('internal_parts')
            //         ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            //         ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            //         ->select('lines.name','production_stocks.internal_part_id as id','internal_parts.part_number','internal_parts.back_number', 'production_stocks.current_stock')
            //         ->groupBy('internal_parts.part_number','internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock')
            //         ->get();

            //         foreach ($data as $value) {
            //             $lineFound = false;
            //             // Check if line already exists in $lines array
            //             foreach ($result as $line) {
            //                 if ($line->line === $value->name) {
            //                     $lineFound = true;
            //                     $line->items[] = [
            //                         'id' => $value->id,
            //                         'part_number' => $value->part_number,
            //                         'back_number' => $value->back_number,
            //                         'qty' => $value->current_stock,
            //                     ];
            //                     break;
            //                 }
            //             }
            //             // If line doesn't exist, create a new object and add it to $result array
            //             if (!$lineFound) {
            //                 $lineObject = (object) [
            //                     'line' => $value->name,
            //                     'items' => [
            //                         [
            //                             'id' => $value->id,
            //                             'part_number' => $value->part_number,
            //                             'back_number' => $value->back_number,
            //                             'qty' => $value->current_stock,
            //                         ],
            //                     ],
            //                 ];
            //                 $result[] = $lineObject;
            //             }
            //         }

            //     $this->mqttConnect('prod/quantity' , $result);

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
        foreach ($loadingLists as $loadingList => $items) {
            array_push($data, (object) ['loading_list_number' => $loadingList]);
            // check if items belongs to loading list based on index of the array
            foreach ($items as $item => $val) {
                if (array_key_exists($loadingList, $loadingLists) && array_key_exists($item, $loadingLists[$loadingList])) {
                    $result[] = [
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
        for ($i = 0; $i < count($data); $i++) {
            $response = $client->post('https://dea-dev.aiia.co.id/api/v1/kanbans', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $data[$i],
            ]);
        }

        for ($i = 0; $i < count($data); $i++) {
            foreach ($data[$i]->items as $key => $value) {
                // get actual kanban scanned based on same kanban cust for all cust
                $kanbans = array_count_values(array_column(json_decode(json_encode($data[$i]->items), true), 'part_number_cust'));

                foreach ($kanbans as $kanban_cust => $actual_scanned) {
                    $lastDigit = substr($kanban_cust, -2);
                    $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $data[$i]->loading_list_number)->first();

                    // check part number customer length
                    if (strlen($kanban_cust) == 12) {
                        // TMMIN
                        if ($lastDigit != '00') {
                            $convertedPartNumber = substr($kanban_cust, 0, 5) . '-' . substr($kanban_cust, 5, 5) . '-' . substr($kanban_cust, -2);
                        } else {
                            $convertedPartNumber = substr(substr_replace($kanban_cust, '-', 5, 0), 0, -2);
                        }
                    } else if (strlen($kanban_cust) == 10) {
                        if ($loadingListId->customer_id == 14) {
                            // SUZUKI
                            $convertedPartNumber = substr_replace($kanban_cust, '-', 5, 0) . '-' . '000';
                        } else {
                            if ($loadingListId->customer_id == 6) {
                                // MMKI
                                $convertedPartNumber = $kanban_cust;
                            } else {
                                // TBINA
                                $convertedPartNumber = substr_replace($kanban_cust, '-', 5, 0);
                            }
                        }
                    } else if (strlen($kanban_cust) == 13) {
                        // SUZUKI
                        if ($lastDigit != '000') {
                            $convertedPartNumber = substr($kanban_cust, 0, 5) . '-' . substr($kanban_cust, 5, 5) . '-' . substr($kanban_cust, -3);
                        } else {
                            $convertedPartNumber = substr(substr_replace($kanban_cust, '-', 5, 0), 0, -3);
                        }
                    } else {
                        $convertedPartNumber = $kanban_cust;
                    }

                    // get customer part id
                    $customerPart = CustomerPart::select('id')
                        ->where('part_number', $convertedPartNumber)
                        ->where('customer_id', $loadingListId->customer_id)
                        ->first();

                    // get kanban_qty
                    $kanban_qty = LoadingListDetail::select('kanban_qty')
                        ->where('loading_list_id', $loadingListId->id)
                        ->where('customer_part_id', $customerPart->id)
                        ->first();

                    if ($actual_scanned < $kanban_qty->kanban_qty) {
                        $kanban_qty->update([
                            'actual_kanban_qty' => $actual_scanned
                        ]);
                    }
                }
            };
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
        if (!$internalPart) {
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
        if (!$kanban) {
            return response()->json([
                'status' => 'kanbanNotExist',
                'message' => 'Kanban tidak terdaftar'
            ], 404);
        }

        // check if kanban already scanned by production
        $kanbanAfterProd = KanbanAfterProd::where('kanban_id', $kanban->id);
        if (!$kanbanAfterProd->first()) {
            return response()->json([
                'status' => 'notScanned',
                'message' => 'Kanban belum discan produksi!'
            ], 404);
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

        // (temporary)
        $qty = CustomerPart::select('qty_per_kanban')->where('internal_part_id', $internalPart->id)->first();

        // check if kanban exist
        $kanban = Kanban::select('id')
            ->where('internal_part_id', $internalPart->id)
            ->where('serial_number', $seri)
            ->first();

        // check if kanban already scanned by production
        $kanbanAfterProd = KanbanAfterProd::where('kanban_id', $kanban->id)->get();

        try {
            DB::beginTransaction();

            // delete kanban id at kanban after prod table
            if ($kanban) {
                KanbanAfterProd::where('kanban_id', $kanban->id)->update([
                    'kanban_id' => null
                ]);
            }

            // create data at kanban after pulls table
            foreach ($kanbanAfterProd as $kanbanAfterProd) {
                KanbanAfterPulling::create([
                    'kanban_id' => $kanbanAfterProd->kanban_id,
                    'internal_part_id' => $kanbanAfterProd->internal_part_id,
                    'code' => $kanbanAfterProd->code,
                    'npk' => auth()->user()->npk,
                    'date' => Carbon::now()->format('Y-m-d')
                ]);
            }

            // (temporary)
            // for ($i = 0; $i < $qty->qty_per_kanban; $i++){
            //     KanbanAfterPulling::create([
            //         'kanban_id' => $kanban->id,
            //         'internal_part_id' => $internalPart->id,
            //         'code' => Carbon::now()->format('Ymd') . Str::random(7),
            //         'npk' => auth()->user()->npk,
            //         'date' => Carbon::now()->format('Y-m-d')
            //     ]);
            // }

            return response()->json([
                'status' => 'success',
                'data' => $kanban->id
            ], 200);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function edclAuth($username, $password)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-apihub-key' => env('API_TMMIN_KEY'),
            ])->post(env('API_TMMIN') . 'auth/login', [
                'username' => $username,
                'password' => $password,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Authentication failed',
                    'status' => $response->status(),
                    'message' => $response->json(), // Log for debugging
                ], $response->status());
            }

            $data = $response->json(); // Convert response to array
            $accessToken = $data['data']['accessToken'] ?? null; // Safely retrieve accessToken

            return $accessToken; // Output the token

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Authentication failed, please try again'], 500);
        }
    }

    public function edcl($skid, $manifest, $itemNo, $seqNo, $customerPart, $originalBarcode, $loadingList, $customer)
    {
        // Authenticate and get the token
        $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD')) ?? null;

        if (!$token) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        // get loading list id
        $loadingListId = LoadingList::select('id')->where('number', $loadingList)->first();
        if (!$loadingListId) {
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!'
            ];
        }

        // get customer part id
        $customerPartId = CustomerPart::with('customer')
            ->where('part_number', $this->convertPartNumber($loadingList, $customerPart))
            ->whereHas('customer', function ($query) use ($customer) {
                $query->where('name', $customer);
            })
            ->first();

        // dd($customer);

        if (!$customerPartId) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer tidak terdaftar!'
            ];
        }

        // Prepare the data for the API request
        $data = [
            [
                "supplierCode" => env('SUPPLIER_CODE'),
                "supplierPlant" => "2",
                "skidNo" => "SKD" . $manifest . "00" . $skid, // Replace with actual value if needed
                "manifestNo" => $manifest,
                "itemNo" => $itemNo,
                "seqNo" => $seqNo,
                "kanbanId" => $originalBarcode
            ]
        ];

        // Required fields for validation
        $requiredFields = [
            'manifestNo' => 'Manifest number is required',
            'itemNo' => 'Item number is required',
            'seqNo' => 'Sequence number is required',
            'kanbanId' => 'Original barcode (kanbanId) is required',
        ];

        // Check each required field
        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($data[0][$field])) {
                return response()->json(['error' => $errorMessage], 400);
            }
        }

        // Send the request to the EDCL API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'x-apihub-key' => env('API_TMMIN_KEY'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(env('API_TMMIN') . 'ManifestCompleteness/confirm', $data);

        // get loading list detail table
        $loadingListDetailId = LoadingListDetail::select('id')
            ->where('loading_list_id', $loadingListId->id)
            ->where('customer_part_id', $customerPartId->id)
            ->first();

        if (!$loadingListDetailId) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer / loading list tidak sesuai!'
            ];
        }

        // Process the response
        if ($response['message'] === 'Success - Confirm Manifest') {
            try {
                DB::beginTransaction();

                // Check if the kanban_id already exists
                $existingSkid = SkidDetail::where('kanban_id', $data[0]['kanbanId'])->exists();

                if (!$existingSkid) {
                    // Insert into skid_details if kanban_id is unique
                    SkidDetail::create([
                        'loading_list_detail_id' => $loadingListDetailId->id,
                        'skid_no' => $data[0]['skidNo'],
                        'item_no' => $data[0]['itemNo'],
                        'serial' => $data[0]['seqNo'],
                        'kanban_id' => $data[0]['kanbanId'],
                        'message' => $response['message'],
                    ]);
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                return response()->json([
                    'error' => $th->getMessage(),
                    'status' => 500
                ], 500);
            }

            // Handle successful response
            return response()->json([
                'status' => 'success',
                'message' => $response['message'],
                'data' => $response['data']['successes']
            ], $response['status']);
        } elseif ($response['message'] === 'Failed - Confirm Manifest') {
            // log into database
            SkidDetail::create([
                'loading_list_detail_id' => $loadingListDetailId->id,
                'skid_no' => $data[0]['skidNo'],
                'item_no' => $data[0]['itemNo'],
                'serial' => $data[0]['seqNo'],
                'kanban_id' => $data[0]['kanbanId'],
                'message' => $response['data']['faileds'][0]['message'],
            ]);

            // Handle failed response
            return response()->json([
                'status' => 'error',
                'message' => $response['data']['faileds'][0]['message'],
                'data' => $response['data']['faileds']
            ], $response['status']);
        } else {
            // Handle unexpected response
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected response',
                'data' => []
            ], 500);
        }
    }

    public function edclCancel($id)
    {
        // Authenticate and get the token
        $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD')) ?? null;

        if (!$token) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        // get skid detail 
        $skidData = SkidDetail::where('id', $id)->first();

        $data = [
            [
                "supplierCode" => env('SUPPLIER_CODE'),
                "supplierPlant" => "2",
                "skidNo" =>  $skidData->skid_no, // Replace with actual value if needed
                "manifestNo" => substr($skidData->skid_no, 3, 10),
                "itemNo" => (int) $skidData->item_no, // Replace with actual value if needed
                "seqNo" => (int) $skidData->serial, // Replace with actual value
                "kanbanId" => $skidData->kanban_id, // Replace with actual value if needed
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'x-apihub-key' => env('API_TMMIN_KEY'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(env('API_TMMIN') . 'ManifestCompleteness/cancel', $data);

        // Process the response
        if ($response['message'] === 'Success - Cancel Manifest') {
            try {
                DB::beginTransaction();

                // delete row in skid details
                $skidData->delete();

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => $response['message'],
                    'data' => $response['data']['successes']
                ], $response['status']);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();

                return response()->json([
                    'error' => $th->getMessage(),
                    'status' => 500
                ], 500);
            }
        } elseif ($response['message'] === 'Failed - Cancel Manifest') {
            // Handle failed response
            return response()->json([
                'status' => 'error',
                'message' => $response['data']['faileds'][0]['message'],
                'data' => $response['data']['faileds']
            ], $response['status']);
        } else {
            // Handle unexpected response
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected response',
                'data' => []
            ], 500);
        }
    }
}
