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
use App\Models\ProductionPlan;
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
            if($loadingListId->customer_id == 14 || $loadingListId->customer_id == 22){
                // SUZUKI
                // $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
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

    public function settingIndex()
    {
        // Get current line assignments
        $lineAssignments = [
            'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18'],
            'AS004' => ['CI15', 'CI16', 'CI19'],
        ];

        // Get current cycle times
        $cycleTimes = [
            'CI11' => '00:34',
            'CI12' => '00:34',
            'CI13' => '00:40',
            'CI14' => '00:34',
            'CI15' => '00:39',
            'CI16' => '00:40',
            'CI17' => '00:40',
            'CI18' => '00:40',
            'CI19' => '00:37',
        ];

        return view('pages.pulling.setting', [
            'lineAssignments' => $lineAssignments,
            'cycleTimes' => $cycleTimes,
        ]);
    }

    public function settingUpdate(Request $request)
    {
        $request->validate([
            'line_assignments' => 'required|array',
            'cycle_times' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // Update line assignments in your configuration or database
            // This would depend on how you're storing these settings
            // For now, we'll just return the updated values
            
            // Example if storing in database:
            // foreach ($request->line_assignments as $line => $backNos) {
            //     Setting::updateOrCreate(
            //         ['key' => 'line_assignments_'.$line],
            //         ['value' => json_encode($backNos)]
            //     );
            // }

            // Update cycle times
            // foreach ($request->cycle_times as $backNo => $time) {
            //     Setting::updateOrCreate(
            //         ['key' => 'cycle_time_'.$backNo],
            //         ['value' => $time]
            //     );
            // }

            DB::commit();

            return redirect()->back()->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function reorderProduction(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'line' => 'required|string|in:AS003,AS004',
            'new_order' => 'required|array',
            'new_order.*.id' => 'required|exists:production_plans,id'
        ]);
    
        try {
            DB::beginTransaction();
            
            $date = Carbon::parse($validated['date'])->format('Y-m-d');
            $line = $validated['line'];
            $startWorkingTime = Carbon::parse($date)->setTime(6, 0, 0); // Start at 6:00 AM
            
            foreach ($validated['new_order'] as $orderItem) {
                $item = ProductionPlan::findOrFail($orderItem['id']);
                
                // Verify this item belongs to the selected date and line
                if ($item->plan_date != $date || $item->line != $line) {
                    throw new \Exception("Item {$item->id} doesn't belong to selected date/line");
                }
                
                // Calculate production duration in seconds
                [$mm, $ss] = explode(':', $item->prod_time);
                $prodSeconds = ((int)$mm * 60) + (int)$ss;
                $totalSeconds = $prodSeconds * (int)$item->order_qty;
                
                // Set working times
                $workingStart = $startWorkingTime->format('H:i');
                $workingEnd = $startWorkingTime->copy()->addSeconds($totalSeconds)->format('H:i');
                $workingDuration = gmdate('H:i:s', $totalSeconds);
                
                // Calculate balance time (time until delivery)
                $deliveryTime = Carbon::parse($item->delivery_time);
                $endTime = $startWorkingTime->copy()->addSeconds($totalSeconds);
                
                if ($deliveryTime->lt($endTime)) {
                    $deliveryTime->addDay();
                }
                
                $balanceSeconds = $deliveryTime->diffInSeconds($endTime);
                $balanceTime = ($balanceSeconds < 0 ? '-' : '') . gmdate('H:i', abs($balanceSeconds));
                
                // Update the item (without sequence)
                $item->update([
                    'working_start' => $workingStart,
                    'working_end' => $workingEnd,
                    'working_duration' => $workingDuration,
                    'balance_time' => $balanceTime
                ]);
                
                $startWorkingTime->addSeconds($totalSeconds);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Production sequence updated successfully',
                'data' => ProductionPlan::where('plan_date', $date)
                                    ->where('line', $line)
                                    ->orderBy('working_start') // Order by working time instead
                                    ->get()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update production sequence: ' . $e->getMessage()
            ], 500);
        }
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

    public function customerCheck($customer, $pds = null)
    {
        if ($customer == '7A00022' && $pds && str_contains($pds, 'KK11')) {
            $check = Customer::where('code', $customer)
                            ->where('name', 'like', '%SUZUKI RKK11%')
                            ->first();
        } else {
            $check = Customer::where('code', $customer)->first();
        }

        if (!$check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'customer' => $check->name,
            'first' => $check->char_first,
            'length' => $check->char_length,
            'total' => $check->char_total
        ]);
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
        $data = $request->validate([
            'loadingList'  => 'required|string',
            'customerPart' => 'required|string',
            'internalPart' => 'required|string',
            'serialNumber' => 'required|string',
            'qty_per_kbn'  => 'required|numeric|min:1',
        ]);

        $data['qty_per_kbn'] = (int) round($data['qty_per_kbn']);

        return DB::transaction(function () use ($data) {
            // 1) Ambil Loading List
            $loadingList = LoadingList::select('id','number','customer_id')
                ->where('number', $data['loadingList'])
                ->first();

            if (!$loadingList) {
                return response()->json([
                    'status'  => 'notExists',
                    'message' => 'Loading list tidak terdaftar!',
                ]);
            }

            // 2) Konversi part number customer (pakai fungsi yang sudah ada)
            $converted = $this->convertPartNumber($loadingList->number, $data['customerPart']);
            if (is_array($converted) && ($converted['status'] ?? null) === 'notExists') {
                // Mengikuti kontrak fungsi Anda yang kadang return array error
                return response()->json($converted, 404);
            }
            $convertedPartNumber = is_string($converted) ? $converted : $data['customerPart'];

            // 3) Ambil internal_part & customer_part (minim query)
            $internalPart = InternalPart::select('id','part_number')
                ->where('part_number', $data['internalPart'])
                ->first();

            if (!$internalPart) {
                return response()->json([
                    'status'  => 'notExists',
                    'message' => 'Part atau Kanban tidak ditemukan!',
                ]);
            }

            $customerPart = DB::table('customer_parts')
                ->where('internal_part_id', $internalPart->id)
                ->where('part_number', $convertedPartNumber)
                ->select('id')
                ->first();

            if (!$customerPart) {
                return response()->json([
                    'status'  => 'notExists',
                    'message' => 'Part number customer tidak terdaftar!',
                    'data'    => [
                        'int'  => $data['internalPart'],
                        'cust' => $convertedPartNumber,
                    ],
                ]);
            }

            // 4) Validasi kanban-nya
            $kanban = Kanban::where('internal_part_id', $internalPart->id)
                ->where('serial_number', $data['serialNumber'])
                ->first();

            if (!$kanban) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Kanban tidak terdaftar!',
                ]);
            }

            // 5) Ambil row LoadingListDetail sekali saja (untuk return info)
            $lldQuery = LoadingListDetail::query()
                ->where('loading_list_id', $loadingList->id)
                ->where('customer_part_id', $customerPart->id);

            $lld = $lldQuery->select('id','kanban_qty','actual_kanban_qty')->lockForUpdate()->first();
            if (!$lld) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Detail loading list tidak ditemukan untuk part ini.',
                ]);
            }

            // 6) Atomic increment: hanya increment jika actual < target (mencegah over-scan)
            $updated = LoadingListDetail::where('id', $lld->id)
                ->whereColumn('actual_kanban_qty', '<', 'kanban_qty')
                ->increment('actual_kanban_qty');

            if ($updated === 0) {
                // Tidak bertambah -> sudah penuh
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Kanban sudah penuh',
                ]);
            }

            // 7) Catat mutasi
            Mutation::create([
                'internal_part_id' => $internalPart->id,
                'serial_number'    => $data['serialNumber'],
                'type'             => 'checkout',
                'qty'              => (int) $data['qty_per_kbn'],
                'npk'              => auth()->user()->npk ?? null,
                'date'             => now()->format('Y-m-d H:i:s'),
            ]);

            // 8) Update status kanban (hindari write jika sudah 2)
            if ((int) $kanban->status !== 2) {
                $kanban->update(['status' => 2]);
            }

            // 9) Ambil nilai actual terbaru (opsional: pakai +1 dari sebelumnya)
            $newActual = $lld->actual_kanban_qty + 1;

            return response()->json([
                'status' => 'success',
                'data'   => $newActual,
            ]);
        });
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
                        if ($loadingListId->customer_id == 14 || $loadingListId->customer_id == 22) {
                            // SUZUKI
                            // $convertedPartNumber = substr_replace($kanban_cust, '-', 5, 0) . '-' . '000';
                            $convertedPartNumber = substr_replace($kanban_cust, '-', 5, 0);
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

        if (!$customerPartId) {
            return [
                'status' => 'notExists',
                'message' => 'ftar!',
                'customer_part' => $this->convertPartNumber($loadingList, $customerPart)
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

    public function manual()
    {
        $customers = Customer::all(); // Atau filter sesuai kebutuhan
        return view('pages.pulling.manual', compact('customers'));
    }

    public function manualReset(Request $request)
    {
        $validated = $request->validate([
            'internal' => 'required|string',
            'serial' => 'required|string'
        ]);

        // Cari internal part
        $internalPart = InternalPart::with('customerPart')->where('part_number', $request->internal)->first();

        if (!$internalPart) {
            return response()->json([
                'status' => 'error',
                'message' => "Back Number <strong>{$request->internal}</strong> tidak ditemukan."
            ], 404);
        }

        // Ambil kanban berdasarkan internal_part_id dan serial_number
        $kanban = \App\Models\Kanban::where('internal_part_id', $internalPart->id)
            ->where('serial_number', $request->serial)
            ->first();

        if (!$kanban) {
            return response()->json([
                'status' => 'error',
                'message' => "Serial Number <strong>{$request->serial}</strong> tidak ditemukan untuk Back Number <strong>{$request->internal}</strong>."
            ], 404);
        }

        // Update status kanban
        $kanban->status = 2;
        $kanban->save();

        // update mutation
        Mutation::create([
            'internal_part_id' => $internalPart->id,
            'serial_number' => $request->serial,
            'qty' => $internalPart->customerPart->qty_per_kanban,
            'type' => 'manual',
            'npk' => auth()->user()->npk,
            'date' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kanban berhasil di-reset.'
        ]);
    }

}
