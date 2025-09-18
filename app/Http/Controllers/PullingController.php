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
use Illuminate\Validation\Rule;
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
            if ($loadingListId->customer_id == 14 || $loadingListId->customer_id == 22) {
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
            'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18', 'D403', 'D111'],
            'AS004' => ['CI15', 'CI16', 'CI19', 'D500'],
        ];

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

    public function apiProductionItems(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'line' => 'required|string|in:AS003,AS004',
        ]);
    
        $date = Carbon::parse($validated['date'])->toDateString();
        $line = $validated['line'];
    
        $rows = ProductionPlan::query()
            ->where('plan_date', $date)
            ->where('line', $line)
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->orderBy('id')            // tidak dipakai
            ->get(['id','back_no','customer','order_qty','delivery_time','prod_time']);
    
        // Normalisasi & kembalikan sebagai array rapi (tanpa mutasi objek)
        $items = $rows->map(function ($r) {
            $raw = data_get($r, 'delivery_time');
    
            // Normalisasi ke 'HH:MM'
            if ($raw instanceof \DateTimeInterface) {
                $t = $raw->format('H:i');
            } else {
                $t = trim((string) $raw);
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t)) {
                    $t = substr($t, 0, 5);
                }
                if ($t === '' || !preg_match('/^\d{2}:\d{2}$/', $t)) {
                    $t = '00:00';
                }
            }
    
            return [
                'id'             => (int) data_get($r, 'id'),
                'back_no'        => (string) data_get($r, 'back_no', ''),
                'customer'       => (string) data_get($r, 'customer', ''),
                'order_qty'      => (int) data_get($r, 'order_qty', 0),
                'delivery_time'  => $t,
                'prod_time'      => (string) data_get($r, 'prod_time', '00:00'),
            ];
        })->values();
    
        return response()->json($items);
    }
    

    /**
     * POST /pulling/settings/reorder
     * Body:
     * {
     *   "date": "2025-08-28",
     *   "line": "AS003",
     *   "new_order": [{ "id": 123, "delivery_time": "09:30" }, ...] // kirim semua item
     * }
     *
     * Langkah BE:
     * 1) Update delivery_time sesuai payload.
     * 2) Recompute working_* & balance_time utk SEMUA item (tanggal+line) mengikuti delivery_time ASC.
     */
    public function reorderByDeliveryTime(Request $request)
    {
        $validated = $request->validate([
            'date'                      => 'required|date',
            'line'                      => 'required|string|in:AS003,AS004',
            'new_order'                 => 'required|array|min:1',
            'new_order.*.id'            => 'required|exists:production_plans,id',
            'new_order.*.delivery_time' => ['required','regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        DB::beginTransaction();
        try {
            $date  = Carbon::parse($validated['date'])->toDateString();
            $line  = $validated['line'];
            $pairs = collect($validated['new_order'])->keyBy('id');

            // Lock semua item pada date+line supaya konsisten
            $all = ProductionPlan::query()
                ->where('plan_date', $date)
                ->where('line', $line)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Pastikan semua id pada payload memang belong to date+line
            foreach ($pairs as $id => $payload) {
                if (!isset($all[$id])) {
                    throw new \Exception("Item {$id} doesn't belong to selected date/line");
                }
            }

            // 1) Update delivery_time sesuai payload (gunakan HH:MM)
            foreach ($pairs as $id => $payload) {
                $t = trim($payload['delivery_time']);
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t)) {
                    $t = substr($t, 0, 5);
                }
                $item = $all[$id];
                $item->delivery_time = $t;
                $item->save();
            }

            // 2) Recompute untuk semua item, urut delivery_time ASC
            $allItems = ProductionPlan::query()
            ->where('plan_date', $date)
            ->where('line', $line)
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->lockForUpdate()
            ->get();

            // Start kerja 06:00 di tanggal plan
            $startWorkingTime = Carbon::parse("$date 06:00:00");

            // Kelompokkan per dn_number (jika null/kosong, treat unik per id)
            $grouped = $allItems->groupBy(function ($it) {
            return $it->dn_number ?: "ID-{$it->id}";
            });

            // Helper parsing detik produksi (utama mm:ss, juga dukung hh:mm(:ss) / menit tunggal)
            $parseProdSeconds = function (string $time): int {
            $time = trim($time);
            if ($time === '') return 0;
            $parts = explode(':', $time);
            if (count($parts) === 3) { // hh:mm:ss
                return ((int)$parts[0]) * 3600 + ((int)$parts[1]) * 60 + ((int)$parts[2]);
            }
            if (count($parts) === 2) {
                // Asumsikan mm:ss (sesuai controller referensi)
                return ((int)$parts[0]) * 60 + ((int)$parts[1]);
            }
            // Angka tunggal = menit
            if (ctype_digit($time)) return ((int)$time) * 60;
            return 0;
            };

            // Iterasi per grup dn_number
            $currentStartGlobal = $startWorkingTime->copy();

            foreach ($grouped as $dnNumber => $group) {
            // Urutkan dalam grup (ikuti referensi: by back_no)
            $group = $group->sortBy('back_no')->values();

            // Delivery time untuk grup = delivery_time item pertama (fallback "00:00")
            $deliveryTimeStr = (string)($group->first()->delivery_time ?? '00:00');
            $delivery = Carbon::parse("$date " . substr($deliveryTimeStr, 0, 5));

            // 1) Hitung working_start/working_end/item-duration berantai
            $cursor = $currentStartGlobal->copy();
            $lastEnd = $cursor->copy(); // akan jadi end item terakhir

            foreach ($group as $item) {
                $perUnitSeconds = $parseProdSeconds((string)$item->prod_time);
                $qty            = max(0, (int)$item->order_qty);
                $totalSeconds   = $perUnitSeconds * $qty;

                $workingStart = $cursor->copy();
                $workingEnd   = $cursor->copy()->addSeconds($totalSeconds);

                // Simpan ke model
                $item->working_start    = $workingStart->format('H:i');
                $item->working_end      = $workingEnd->format('H:i');
                $item->working_duration = sprintf('%02d:%02d:%02d',
                                        intdiv($totalSeconds,3600),
                                        intdiv($totalSeconds%3600,60),
                                        $totalSeconds%60);

                $cursor = $workingEnd->copy();
                $lastEnd = $workingEnd->copy();
            }

            // 2) Anchor delivery minimal >= lastEnd (kalau lewat tengah malam)
            while ($delivery->lt($lastEnd)) {
                $delivery->addDay();
            }

            // 3) Balance = delivery - lastEnd (positif = masih ada slack)
            $balanceSeconds = $lastEnd->diffInSeconds($delivery, false); // << urutan dibalik
            $sign  = $balanceSeconds < 0 ? '-' : '';
            $abs   = abs($balanceSeconds);
            $bh    = intdiv($abs, 3600);
            $bm    = intdiv($abs % 3600, 60);
            $balanceFormatted = sprintf('%s%02d:%02d', $sign, $bh, $bm);

            // 4) Commit ke DB untuk semua item di grup (pakai balance grup yang sama)
            foreach ($group as $item) {
                $item->balance_time = $balanceFormatted;
                // delivery_time sudah diupdate sebelumnya sesuai payload
                // tapi kalau ingin “rapikan” ke HH:MM:
                $item->delivery_time = substr((string)$item->delivery_time, 0, 5);
                $item->save();
            }

            // 5) Lanjutkan start berikutnya = selesai grup ini
            $currentStartGlobal = $cursor->copy();
            }

            DB::commit();

            $data = ProductionPlan::query()
                ->where('plan_date', $date)
                ->where('line', $line)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->get();

            $updated = $pairs->count();
            return response()->json([
                'success' => true,
                'message' => "Delivery times updated & schedule recomputed ($updated items).",
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update by delivery time: '.$e->getMessage(),
            ], 500);
        }
    }

    /** Parse "mm", "mm:ss", atau "hh:mm(:ss)" → detik */
    private function secondsFromProdTime(string $time): int
    {
        $time = trim($time);
        if ($time === '') return 0;

        $parts = array_map('intval', explode(':', $time));

        if (count($parts) === 3) {        // "HH:MM:SS"
            return $parts[0]*3600 + $parts[1]*60 + $parts[2];
        }

        if (count($parts) === 2) {        // "HH:MM"  (umum di DB)
            return $parts[0]*3600 + $parts[1]*60;
        }

        // Fallback: angka tunggal dianggap menit
        if (ctype_digit($time)) {
            return intval($time) * 60;
        }

        return 0;
    }


    /** Format detik → H:i:s */
    private function formatHms(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /** Format detik bertanda → "-HH:MM" / "HH:MM" */
    private function formatHmSigned(int $seconds): string
    {
        $sign  = $seconds < 0 ? '-' : '';
        $abs   = abs($seconds);
        $hours = intdiv($abs, 3600);
        $mins  = intdiv($abs % 3600, 60);
        return sprintf('%s%02d:%02d', $sign, $hours, $mins);
    }

    public function addManualItem(Request $req)
    {
        // Validasi
        $data = $req->validate([
            'line'           => ['required', Rule::in(['AS003','AS004'])],
            'plan_date'      => ['required','date'],
            'customer'       => ['required','string','max:150'],
            'dock'           => ['required','string','max:10'],
            'cycle'          => ['nullable','integer','min:0'],
            'back_no'        => ['required','string','max:20'],
            'order_qty'      => ['required','integer','min:1'],
            'prod_time'      => ['required','regex:/^\d{2}:\d{2}$/'], // mm:ss
            'working_start'  => ['nullable','regex:/^\d{2}:\d{2}$/'], // HH:MM (optional, akan di-override)
            'delivery_time'  => ['required','regex:/^\d{2}:\d{2}$/'], // HH:MM
            'delivery_date'  => ['required','date'],                   // YYYY-MM-DD
            'dn_number'      => ['required','string','max:50'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $planDate = Carbon::parse($data['plan_date'], $tz)->toDateString();
        $line     = $data['line'];

        // Hitung total waktu kerja (detik)
        [$mm, $ss] = explode(':', $data['prod_time']);
        $perItemSeconds = ((int)$mm * 60) + (int)$ss;
        $totalSeconds   = $perItemSeconds * (int)$data['order_qty'];

        // Ambil working_end terakhir (string HH:MM) untuk plan_date+line
        $lastEnd = ProductionPlan::where('plan_date', $planDate)
            ->where('line', $line)
            ->orderByDesc('working_start')  // paling akhir
            ->value('working_end');

        // Jika belum ada data, start dari 06:00
        $base = Carbon::parse($planDate.' '.($lastEnd ?: '06:00'), $tz);
        $workStart = $base->copy();
        $workEnd   = $base->copy()->addSeconds($totalSeconds);

        // Delivery (gabung date + time)
        $del  = Carbon::parse($data['delivery_date'].' '.$data['delivery_time'], $tz);
        // Kalau delivery < workEnd → berarti lewat tengah malam, majukan 1 hari
        if ($del->lt($workEnd)) {
            $del->addDay();
        }
        $balSec = $workEnd->diffInSeconds($del, false);
        $balFmt = gmdate('H:i', abs($balSec));
        $balanceTime = ($balSec < 0 ? '-' : '').$balFmt;

        $payload = [
            'plan_date'         => $planDate,
            'line'              => $line,
            'customer'          => $data['customer'],
            'dock'              => $data['dock'],
            'cycle'             => (int)($data['cycle'] ?? 0),
            'back_no'           => strtoupper(trim($data['back_no'])),
            'order_qty'         => (int)$data['order_qty'],
            'prod_time'         => $data['prod_time'],                 // mm:ss
            'working_start'     => $workStart->format('H:i'),
            'working_end'       => $workEnd->format('H:i'),
            'working_duration'  => gmdate('H:i:s', $totalSeconds),
            'delivery_time'     => substr($data['delivery_time'], 0, 5), // HH:MM (disimpan apa adanya)
            'delivery_date'     => Carbon::parse($data['delivery_date'], $tz)->toDateString(),
            'balance_time'      => $balanceTime,
            'dn_number'         => $data['dn_number'],
            'direct_pulling_qty'=> 0,
            'stock_chute_qty'   => 0,
            'created_at'        => now($tz),
            'updated_at'        => now($tz),
        ];

        // Optional: kalau mau pakai aturan "dock STR -30 menit" untuk penyimpanan delivery_time,
        // uncomment 3 baris di bawah ini:
        // if (strtoupper($payload['dock']) === 'STR') {
        //     $dt = Carbon::parse($payload['delivery_time'], $tz)->subMinutes(30)->format('H:i');
        //     $payload['delivery_time'] = $dt;
        // }

        DB::beginTransaction();
        try {
            ProductionPlan::create($payload);

            // Kembalikan list terbaru untuk plan_date & line (urut paling logis)
            $items = ProductionPlan::where('plan_date', $planDate)
                ->where('line', $line)
                ->orderBy('working_start') // biar urut sesuai urutan produksi
                ->get([
                    'id','customer','back_no','order_qty','delivery_time','dn_number'
                ])
                ->map(function ($r) {
                    return [
                        'id'            => (string)$r->id,
                        'customer'      => $r->customer,
                        'back_no'       => $r->back_no,
                        'order_qty'     => (int)$r->order_qty,
                        'delivery_time' => substr($r->delivery_time ?? '00:00', 0, 5),
                        'dn_number'     => $r->dn_number,
                    ];
                });

            DB::commit();
            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'DB error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteItem($id, \Illuminate\Http\Request $req)
    {
        $item = \App\Models\ProductionPlan::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $planDate = $item->plan_date;
        $line     = $item->line;

        try {
            $item->delete();

            // Balikkan list terbaru untuk tanggal & line yang sama (urut working_start)
            $items = \App\Models\ProductionPlan::where('plan_date', $planDate)
                ->where('line', $line)
                ->orderBy('working_start')
                ->get(['id','customer','back_no','order_qty','delivery_time','dn_number'])
                ->map(function ($r) {
                    return [
                        'id'            => (string)$r->id,
                        'customer'      => $r->customer,
                        'back_no'       => $r->back_no,
                        'order_qty'     => (int)$r->order_qty,
                        'delivery_time' => substr($r->delivery_time ?? '00:00', 0, 5),
                        'dn_number'     => $r->dn_number,
                    ];
                });

            return response()->json(['success' => true, 'data' => $items]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'DB error: '.$e->getMessage()], 500);
        }
    }

    public function addItemOptions(Request $req)
    {
        $line = $req->query('line');           // AS003 / AS004 (opsional tapi direkomendasikan)
        $plan = $req->query('plan_date');      // YYYY-MM-DD (opsional)
        $daysLookback = 30; // batasi 30 hari terakhir biar ringan

        $q = ProductionPlan::query();

        if ($line) $q->where('line', $line);

        if ($plan) {
            // ambil yang relevan di sekitar plan_date biar hasilnya kontekstual
            $q->whereBetween('plan_date', [
                \Carbon\Carbon::parse($plan)->subDays($daysLookback)->toDateString(),
                \Carbon\Carbon::parse($plan)->addDays(1)->toDateString(),
            ]);
        } else {
            // fallback: 30 hari terakhir
            $q->where('plan_date', '>=', now()->subDays($daysLookback)->toDateString());
        }

        // DISTINCT values, tanpa null/kosong
        $customers = (clone $q)->whereNotNull('customer')->where('customer','<>','')
            ->distinct()->orderBy('customer')->pluck('customer')->values()->all();

        $docks = (clone $q)->whereNotNull('dock')->where('dock','<>','')
            ->distinct()->orderBy('dock')->pluck('dock')->values()->all();

        $backNos = (clone $q)->whereNotNull('back_no')->where('back_no','<>','')
            ->distinct()->orderBy('back_no')->pluck('back_no')->values()->all();

        // jaga-jaga kalau kosong, kasih fallback ringan
        if (empty($docks)) $docks = ['STR','EXP','6I','OTHERS'];

        // optionally: filter back_no agar sesuai mapping line kalau diminta
        if ($line) {
            $map = [
                'AS003' => ['CI11','CI12','CI13','CI14','CI17','CI18','D403','D111'],
                'AS004' => ['CI15','CI16','CI19','D500'],
            ];
            if (!empty($map[$line])) {
                // gabungkan hasil DB + mapping, lalu unique
                $backNos = collect($backNos)->merge($map[$line])->unique()->sort()->values()->all();
            }
        }

        return response()->json([
            'success'   => true,
            'customers' => $customers,
            'docks'     => $docks,
            'back_nos'  => $backNos,
        ]);
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
            $loadingList = LoadingList::select('id', 'number', 'customer_id')
                ->where('number', $data['loadingList'])
                ->first();
                
            // dd( $data['loadingList']);

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
            $internalPart = InternalPart::select('id', 'part_number')
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
            $lld = LoadingListDetail::where('loading_list_id', $loadingList->id)
                ->where('customer_part_id', $customerPart->id)
                ->lockForUpdate()
                ->first(['id', 'kanban_qty', 'actual_kanban_qty']);

            if (!$lld) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Detail loading list tidak ditemukan untuk part ini.',
                ]);
            }

            // 6) Atomic increment: hanya increment jika actual < target (mencegah over-scan)
            if ((int)$lld->actual_kanban_qty >= (int)$lld->kanban_qty) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Kanban sudah penuh',
                ]);
            }

            // update loading list detail
            LoadingListDetail::where('id', $lld->id)
                ->whereColumn('actual_kanban_qty', '<', 'kanban_qty')
                ->update([
                    'actual_kanban_qty' => DB::raw('actual_kanban_qty + 1')
                ]);

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
            // disable karena part common
            ->whereHas('customer', function ($query) use ($customer) {
                $query->where('name', $customer);
            })
            ->first();

        if (!$customerPartId) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer tidak terdaftar!',
                'customer' => $customer,
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
                'message' => 'Part number customer / loading list tidak sesuai!',
                'data' => [
                    'loading_list_id' => $loadingListId->id,
                    'customer_part_id' => $customerPartId->id
                ]
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
