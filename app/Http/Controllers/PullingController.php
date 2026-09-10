<?php

namespace App\Http\Controllers;

use App\Exports\MasterAlamatChuteTemplateExport;
use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Kanban;
use App\Models\KanbanAfterProd;
use App\Models\KanbanAfterPulling;
use App\Models\Line;
use App\Models\LoadingList;
use App\Models\LoadingListDetail;
use App\Models\MasterAlamatChute;
use App\Models\Mutation;
use App\Models\Part;
use App\Models\ProductionPlan;
use App\Models\Pulling;
use App\Models\SkidDetail;
use App\Services\MasterAlamatChuteImporter;
use App\Support\ConvertsCustomerPartNumber;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PullingController extends Controller
{
    use ConvertsCustomerPartNumber;

    private static $loadingListsCache = [];

    private static $internalPartsCache = [];

    private static $mutationsHaveLoadingListColumns = null;

    private static $mutationsHaveCustomerSerialColumn = null;

    private static $mutationsHaveAgModeColumn = null;

    private function mutationsHaveAgModeColumn(): bool
    {
        if (self::$mutationsHaveAgModeColumn !== null) {
            return self::$mutationsHaveAgModeColumn;
        }

        try {
            self::$mutationsHaveAgModeColumn = Schema::hasColumn('mutations', 'ag_mode');
        } catch (\Throwable $th) {
            self::$mutationsHaveAgModeColumn = false;
        }

        return self::$mutationsHaveAgModeColumn;
    }

    private function mutationsHaveCustomerSerialColumn(): bool
    {
        if (self::$mutationsHaveCustomerSerialColumn !== null) {
            return self::$mutationsHaveCustomerSerialColumn;
        }

        try {
            self::$mutationsHaveCustomerSerialColumn = Schema::hasColumn('mutations', 'customer_serial');
        } catch (\Throwable $th) {
            self::$mutationsHaveCustomerSerialColumn = false;
        }

        return self::$mutationsHaveCustomerSerialColumn;
    }

    private function mutationsHaveLoadingListColumns(): bool
    {
        if (self::$mutationsHaveLoadingListColumns !== null) {
            return self::$mutationsHaveLoadingListColumns;
        }

        try {
            self::$mutationsHaveLoadingListColumns = Schema::hasColumn('mutations', 'loading_list_id')
                && Schema::hasColumn('mutations', 'loading_list_detail_id');
        } catch (\Throwable $th) {
            self::$mutationsHaveLoadingListColumns = false;
        }

        return self::$mutationsHaveLoadingListColumns;
    }

    public function mqttConnect($topic, $message)
    {
        $server = '172.18.3.70';
        $port = 1883;
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
            echo 'Exception: '.$e->getMessage()."\n";
        } finally {
            $mqtt->disconnect();
        }
    }

    public function convertPartNumber($loadingList, $customerPart)
    {
        if (! isset(self::$loadingListsCache[$loadingList])) {
            self::$loadingListsCache[$loadingList] = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        }
        $loadingListId = self::$loadingListsCache[$loadingList];
        if (! $loadingListId) {
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!',
            ];
        }

        return $this->convertCustomerPartNumber($customerPart, $loadingListId->customer_id);
    }

    /**
     * Kandidat format part number customer untuk lookup DB (raw, tanpa dash, hasil convert).
     *
     * @return array<int, string>
     */
    private function customerPartNumberCandidates(string $loadingListNumber, string $customerPart): array
    {
        $customerPart = trim($customerPart);
        $raw = strtoupper(str_replace(['-', ' '], '', $customerPart));

        $candidates = array_filter([$customerPart, $raw !== $customerPart ? $raw : null]);

        foreach (array_unique(array_filter([$customerPart, $raw])) as $input) {
            $converted = $this->convertPartNumber($loadingListNumber, $input);
            if (is_string($converted) && $converted !== '') {
                $candidates[] = $converted;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function fetchDeaLoadingListItems(Client $client, string $token, string $loadingListNumber): array
    {
        $response = $client->get('https://dea-dev.aiia.co.id/api/v1/loading-lists/'.rawurlencode($loadingListNumber), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);

        return data_get($payload, 'items', data_get($payload, 'data.items', [])) ?: [];
    }

    /**
     * Endpoint DEA butuh karakter tambahan setelah nomor loading list.
     * Nomor lokal tetap tanpa suffix untuk lookup database internal.
     *
     * @return array<int, string>
     */
    private function deaLoadingListNumberCandidates(string $loadingListNumber): array
    {
        $number = trim($loadingListNumber);

        return array_values(array_unique(array_filter([
            "{$number}z",
            $number,
        ])));
    }

    private function deaPostMatchCount(array $deaItems, string $localLoadingListNumber, array $items): int
    {
        $matched = 0;

        foreach ($items as $item) {
            if ($this->findDeaKanbanItemForPost(
                $deaItems,
                $localLoadingListNumber,
                trim((string) data_get($item, 'part_number_cust', '')),
                trim((string) data_get($item, 'part_number_int', ''))
            )) {
                $matched++;
            }
        }

        return $matched;
    }

    /**
     * @return array{0: array<int, mixed>, 1: string}
     */
    private function fetchBestDeaLoadingListItemsForPost(
        Client $client,
        string $token,
        string $localLoadingListNumber,
        array $items
    ): array {
        $bestItems = [];
        $bestNumber = $localLoadingListNumber;
        $bestMatched = -1;
        $lastError = null;

        foreach ($this->deaLoadingListNumberCandidates($localLoadingListNumber) as $candidate) {
            try {
                $deaItems = $this->fetchDeaLoadingListItems($client, $token, $candidate);
                $matched = $this->deaPostMatchCount($deaItems, $localLoadingListNumber, $items);

                if ($matched > $bestMatched) {
                    $bestItems = $deaItems;
                    $bestNumber = $candidate;
                    $bestMatched = $matched;
                }

                if ($matched >= count($items)) {
                    break;
                }
            } catch (\Throwable $th) {
                $lastError = $th;
            }
        }

        if ($bestMatched < 0) {
            throw $lastError ?: new \RuntimeException("Loading list {$localLoadingListNumber} tidak ditemukan di DEA.");
        }

        return [$bestItems, $bestNumber];
    }

    private function findDeaKanbanItemForPost(
        array $deaItems,
        string $loadingListNumber,
        string $customerPart,
        string $internalPart
    ): ?array {
        $customerCandidates = $this->customerPartNumberCandidates($loadingListNumber, $customerPart);
        $internalPart = trim($internalPart);

        foreach ($deaItems as $item) {
            $deaInternal = trim((string) data_get($item, 'part_number_int', ''));
            if ($deaInternal !== $internalPart) {
                continue;
            }

            $deaCustomer = trim((string) data_get($item, 'part_number_cust', ''));
            if (array_intersect($customerCandidates, $this->customerPartNumberCandidates($loadingListNumber, $deaCustomer))) {
                return [
                    'part_number_cust' => $deaCustomer,
                    'part_number_int' => $deaInternal,
                ];
            }
        }

        return null;
    }

    private function formatCustomerPartForDeaPost(string $customerPart, bool $hasLocalDetail): string
    {
        $partNumber = strtoupper(trim($customerPart));

        if ($hasLocalDetail) {
            return $partNumber;
        }

        return strtoupper(str_replace(['-', ' '], '', $partNumber));
    }

    private function findCustomerPartForLoadingList(
        int $customerId,
        string $loadingListNumber,
        string $customerPart
    ): ?CustomerPart {
        foreach ($this->customerPartNumberCandidates($loadingListNumber, $customerPart) as $partNumber) {
            $found = CustomerPart::query()
                ->with('internalPart:id,part_name,back_number')
                ->where('part_number', $partNumber)
                ->where('customer_id', $customerId)
                ->first(['id', 'back_number', 'internal_part_id', 'part_number']);

            if ($found) {
                return $found;
            }
        }

        $normalizedInput = strtoupper(str_replace(['-', ' '], '', $customerPart));
        if ($normalizedInput === '') {
            return null;
        }

        return CustomerPart::query()
            ->with('internalPart:id,part_name,back_number')
            ->where('customer_id', $customerId)
            ->whereRaw(
                "UPPER(REPLACE(REPLACE(part_number, '-', ''), ' ', '')) = ?",
                [$normalizedInput]
            )
            ->first(['id', 'back_number', 'internal_part_id', 'part_number']);
    }

    private function findLoadingListDetailForPost(
        int $loadingListId,
        int $customerId,
        string $loadingListNumber,
        string $customerPart,
        string $internalPart
    ) {
        $customerCandidates = $this->customerPartNumberCandidates($loadingListNumber, $customerPart);

        return LoadingListDetail::query()
            ->from('loading_list_details as lld')
            ->join('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
            ->join('internal_parts as ip', 'ip.id', '=', 'cp.internal_part_id')
            ->where('lld.loading_list_id', $loadingListId)
            ->where('cp.customer_id', $customerId)
            ->whereIn('cp.part_number', $customerCandidates)
            ->where('ip.part_number', trim($internalPart))
            ->first([
                'lld.id',
                'lld.kanban_qty',
                'cp.part_number',
            ]);
    }

    /**
     * Back no & part name hanya dari part internal (bukan customer_parts.back_number).
     *
     * @return array{0: string, 1: string} [backNo, partName]
     */
    private function resolveInternalBackNo(?CustomerPart $customerPart, ?string $internalPartNumber): array
    {
        if ($customerPart?->relationLoaded('internalPart') || $customerPart?->internal_part_id) {
            $customerPart->loadMissing('internalPart:id,back_number,part_name');
        }

        if ($customerPart?->internalPart) {
            return [
                trim((string) $customerPart->internalPart->back_number),
                trim((string) $customerPart->internalPart->part_name),
            ];
        }

        if ($internalPartNumber !== null && trim($internalPartNumber) !== '') {
            $internal = InternalPart::query()
                ->where('part_number', trim($internalPartNumber))
                ->first(['back_number', 'part_name']);

            if ($internal) {
                return [
                    trim((string) $internal->back_number),
                    trim((string) $internal->part_name),
                ];
            }
        }

        return ['', ''];
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

            return redirect()->back()->with('error', 'Failed to update settings: '.$e->getMessage());
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
            ->get(['id', 'back_no', 'customer', 'order_qty', 'delivery_time', 'prod_time']);

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
                if ($t === '' || ! preg_match('/^\d{2}:\d{2}$/', $t)) {
                    $t = '00:00';
                }
            }

            return [
                'id' => (int) data_get($r, 'id'),
                'back_no' => (string) data_get($r, 'back_no', ''),
                'customer' => (string) data_get($r, 'customer', ''),
                'order_qty' => (int) data_get($r, 'order_qty', 0),
                'delivery_time' => $t,
                'prod_time' => (string) data_get($r, 'prod_time', '00:00'),
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
            'date' => 'required|date',
            'line' => 'required|string|in:AS003,AS004',
            'new_order' => 'required|array|min:1',
            'new_order.*.id' => 'required|exists:production_plans,id',
            'new_order.*.delivery_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        DB::beginTransaction();
        try {
            $date = Carbon::parse($validated['date'])->toDateString();
            $line = $validated['line'];
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
                if (! isset($all[$id])) {
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
                if ($time === '') {
                    return 0;
                }
                $parts = explode(':', $time);
                if (count($parts) === 3) { // hh:mm:ss
                    return ((int) $parts[0]) * 3600 + ((int) $parts[1]) * 60 + ((int) $parts[2]);
                }
                if (count($parts) === 2) {
                    // Asumsikan mm:ss (sesuai controller referensi)
                    return ((int) $parts[0]) * 60 + ((int) $parts[1]);
                }
                // Angka tunggal = menit
                if (ctype_digit($time)) {
                    return ((int) $time) * 60;
                }

                return 0;
            };

            // Iterasi per grup dn_number
            $currentStartGlobal = $startWorkingTime->copy();

            foreach ($grouped as $dnNumber => $group) {
                // Urutkan dalam grup (ikuti referensi: by back_no)
                $group = $group->sortBy('back_no')->values();

                // Delivery time untuk grup = delivery_time item pertama (fallback "00:00")
                $deliveryTimeStr = (string) ($group->first()->delivery_time ?? '00:00');
                $delivery = Carbon::parse("$date ".substr($deliveryTimeStr, 0, 5));

                // 1) Hitung working_start/working_end/item-duration berantai
                $cursor = $currentStartGlobal->copy();
                $lastEnd = $cursor->copy(); // akan jadi end item terakhir

                foreach ($group as $item) {
                    $perUnitSeconds = $parseProdSeconds((string) $item->prod_time);
                    $qty = max(0, (int) $item->order_qty);
                    $totalSeconds = $perUnitSeconds * $qty;

                    $workingStart = $cursor->copy();
                    $workingEnd = $cursor->copy()->addSeconds($totalSeconds);

                    // Simpan ke model
                    $item->working_start = $workingStart->format('H:i');
                    $item->working_end = $workingEnd->format('H:i');
                    $item->working_duration = sprintf(
                        '%02d:%02d:%02d',
                        intdiv($totalSeconds, 3600),
                        intdiv($totalSeconds % 3600, 60),
                        $totalSeconds % 60
                    );

                    $cursor = $workingEnd->copy();
                    $lastEnd = $workingEnd->copy();
                }

                // 2) Anchor delivery minimal >= lastEnd (kalau lewat tengah malam)
                while ($delivery->lt($lastEnd)) {
                    $delivery->addDay();
                }

                // 3) Balance = delivery - lastEnd (positif = masih ada slack)
                $balanceSeconds = $lastEnd->diffInSeconds($delivery, false); // << urutan dibalik
                $sign = $balanceSeconds < 0 ? '-' : '';
                $abs = abs($balanceSeconds);
                $bh = intdiv($abs, 3600);
                $bm = intdiv($abs % 3600, 60);
                $balanceFormatted = sprintf('%s%02d:%02d', $sign, $bh, $bm);

                // 4) Commit ke DB untuk semua item di grup (pakai balance grup yang sama)
                foreach ($group as $item) {
                    $item->balance_time = $balanceFormatted;
                    // delivery_time sudah diupdate sebelumnya sesuai payload
                    // tapi kalau ingin “rapikan” ke HH:MM:
                    $item->delivery_time = substr((string) $item->delivery_time, 0, 5);
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
                'data' => $data,
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
        if ($time === '') {
            return 0;
        }

        $parts = array_map('intval', explode(':', $time));

        if (count($parts) === 3) {        // "HH:MM:SS"
            return $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
        }

        if (count($parts) === 2) {        // "HH:MM"  (umum di DB)
            return $parts[0] * 3600 + $parts[1] * 60;
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
        $sign = $seconds < 0 ? '-' : '';
        $abs = abs($seconds);
        $hours = intdiv($abs, 3600);
        $mins = intdiv($abs % 3600, 60);

        return sprintf('%s%02d:%02d', $sign, $hours, $mins);
    }

    public function addManualItem(Request $req)
    {
        // Validasi
        $data = $req->validate([
            'line' => ['required', Rule::in(['AS003', 'AS004'])],
            'plan_date' => ['required', 'date'],
            'customer' => ['required', 'string', 'max:150'],
            'dock' => ['required', 'string', 'max:10'],
            'cycle' => ['nullable', 'integer', 'min:0'],
            'back_no' => ['required', 'string', 'max:20'],
            'order_qty' => ['required', 'integer', 'min:1'],
            'prod_time' => ['required', 'regex:/^\d{2}:\d{2}$/'], // mm:ss
            'working_start' => ['nullable', 'regex:/^\d{2}:\d{2}$/'], // HH:MM (optional, akan di-override)
            'delivery_time' => ['required', 'regex:/^\d{2}:\d{2}$/'], // HH:MM
            'delivery_date' => ['required', 'date'],                   // YYYY-MM-DD
            'dn_number' => ['required', 'string', 'max:50'],
        ]);

        $tz = config('app.timezone', 'Asia/Jakarta');
        $planDate = Carbon::parse($data['plan_date'], $tz)->toDateString();
        $line = $data['line'];

        // Hitung total waktu kerja (detik)
        [$mm, $ss] = explode(':', $data['prod_time']);
        $perItemSeconds = ((int) $mm * 60) + (int) $ss;
        $totalSeconds = $perItemSeconds * (int) $data['order_qty'];

        // Ambil working_end terakhir (string HH:MM) untuk plan_date+line
        $lastEnd = ProductionPlan::where('plan_date', $planDate)
            ->where('line', $line)
            ->orderByDesc('working_start')  // paling akhir
            ->value('working_end');

        // Jika belum ada data, start dari 06:00
        $base = Carbon::parse($planDate.' '.($lastEnd ?: '06:00'), $tz);
        $workStart = $base->copy();
        $workEnd = $base->copy()->addSeconds($totalSeconds);

        // Delivery (gabung date + time)
        $del = Carbon::parse($data['delivery_date'].' '.$data['delivery_time'], $tz);
        // Kalau delivery < workEnd → berarti lewat tengah malam, majukan 1 hari
        if ($del->lt($workEnd)) {
            $del->addDay();
        }
        $balSec = $workEnd->diffInSeconds($del, false);
        $balFmt = gmdate('H:i', abs($balSec));
        $balanceTime = ($balSec < 0 ? '-' : '').$balFmt;

        $payload = [
            'plan_date' => $planDate,
            'line' => $line,
            'customer' => $data['customer'],
            'dock' => $data['dock'],
            'cycle' => (int) ($data['cycle'] ?? 0),
            'back_no' => strtoupper(trim($data['back_no'])),
            'order_qty' => (int) $data['order_qty'],
            'prod_time' => $data['prod_time'],                 // mm:ss
            'working_start' => $workStart->format('H:i'),
            'working_end' => $workEnd->format('H:i'),
            'working_duration' => gmdate('H:i:s', $totalSeconds),
            'delivery_time' => substr($data['delivery_time'], 0, 5), // HH:MM (disimpan apa adanya)
            'delivery_date' => Carbon::parse($data['delivery_date'], $tz)->toDateString(),
            'balance_time' => $balanceTime,
            'dn_number' => $data['dn_number'],
            'direct_pulling_qty' => 0,
            'stock_chute_qty' => 0,
            'created_at' => now($tz),
            'updated_at' => now($tz),
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
                    'id', 'customer', 'back_no', 'order_qty', 'delivery_time', 'dn_number',
                ])
                ->map(function ($r) {
                    return [
                        'id' => (string) $r->id,
                        'customer' => $r->customer,
                        'back_no' => $r->back_no,
                        'order_qty' => (int) $r->order_qty,
                        'delivery_time' => substr($r->delivery_time ?? '00:00', 0, 5),
                        'dn_number' => $r->dn_number,
                    ];
                });

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $items,
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

    public function deleteItem($id, Request $req)
    {
        $item = \App\Models\ProductionPlan::find($id);
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $planDate = $item->plan_date;
        $line = $item->line;

        try {
            $item->delete();

            // Balikkan list terbaru untuk tanggal & line yang sama (urut working_start)
            $items = \App\Models\ProductionPlan::where('plan_date', $planDate)
                ->where('line', $line)
                ->orderBy('working_start')
                ->get(['id', 'customer', 'back_no', 'order_qty', 'delivery_time', 'dn_number'])
                ->map(function ($r) {
                    return [
                        'id' => (string) $r->id,
                        'customer' => $r->customer,
                        'back_no' => $r->back_no,
                        'order_qty' => (int) $r->order_qty,
                        'delivery_time' => substr($r->delivery_time ?? '00:00', 0, 5),
                        'dn_number' => $r->dn_number,
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

        if ($line) {
            $q->where('line', $line);
        }

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
        $customers = (clone $q)->whereNotNull('customer')->where('customer', '<>', '')
            ->distinct()->orderBy('customer')->pluck('customer')->values()->all();

        $docks = (clone $q)->whereNotNull('dock')->where('dock', '<>', '')
            ->distinct()->orderBy('dock')->pluck('dock')->values()->all();

        $backNos = (clone $q)->whereNotNull('back_no')->where('back_no', '<>', '')
            ->distinct()->orderBy('back_no')->pluck('back_no')->values()->all();

        // jaga-jaga kalau kosong, kasih fallback ringan
        if (empty($docks)) {
            $docks = ['STR', 'EXP', '6I', 'OTHERS'];
        }

        // optionally: filter back_no agar sesuai mapping line kalau diminta
        if ($line) {
            $map = [
                'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18', 'D403', 'D111'],
                'AS004' => ['CI15', 'CI16', 'CI19', 'D500'],
            ];
            if (! empty($map[$line])) {
                // gabungkan hasil DB + mapping, lalu unique
                $backNos = collect($backNos)->merge($map[$line])->unique()->sort()->values()->all();
            }
        }

        return response()->json([
            'success' => true,
            'customers' => $customers,
            'docks' => $docks,
            'back_nos' => $backNos,
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
                'cycle' => (int) $cycle,
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

        if (! $check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'customer' => $check->name,
            'first' => $check->char_first,
            'length' => $check->char_length,
            'total' => $check->char_total,
            'serial_first' => $check->serial_first ?? null,
            'serial_length' => $check->serial_length ?? null,
        ]);
    }

    public function getAlamatChute(Request $request)
    {
        $validated = $request->validate([
            'loadingList' => 'required|string',
            'customerPart' => 'required|string',
            'internalPart' => 'nullable|string',
        ]);

        $loadingList = LoadingList::query()
            ->select('id', 'customer_id')
            ->where('number', $validated['loadingList'])
            ->first();

        if (! $loadingList) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loading list tidak terdaftar!',
            ], 404);
        }

        $customerPart = $this->findCustomerPartForLoadingList(
            (int) $loadingList->customer_id,
            $validated['loadingList'],
            $validated['customerPart']
        );

        [$backNo] = $this->resolveInternalBackNo(
            $customerPart,
            $validated['internalPart'] ?? null
        );

        $partCandidates = array_values(array_unique(array_filter(
            $customerPart
                ? array_merge(
                    [trim((string) $customerPart->part_number)],
                    $this->customerPartNumberCandidates($validated['loadingList'], $validated['customerPart'])
                )
                : $this->customerPartNumberCandidates($validated['loadingList'], $validated['customerPart'])
        )));

        $alamatChute = null;
        if ($backNo !== '') {
            foreach ($partCandidates as $partNumber) {
                $alamatChute = MasterAlamatChute::resolveByPartCustomerAndBackNo($partNumber, $backNo);
                if ($alamatChute !== null) {
                    break;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'alamat_chute' => $alamatChute,
            'back_no' => $backNo !== '' ? $backNo : null,
        ]);
    }

    public function internalCheck($internal, $isinternal = 0)
    {
        // check internal
        $internal = InternalPart::with('customerPart')->where('part_number', $internal)->first();
        if (! $internal) {
            return [
                'status' => 'error',
                'message' => 'Part atau Kanban tidak ditemukan!',
            ];
        }

        $lineProd = Line::select('name')->where('id', $internal->line_id)->first();

        if ($isinternal == 0) {
            DB::beginTransaction();
            // insert into mutation table
            Mutation::create([
                'internal_part_id' => $internal->id,
                'serial_number' => 'XXXX',
                'type' => 'checkout',
                'qty' => 0,
                'npk' => auth()->user()->npk,
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();
        }

        return [
            'status' => 'success',
            'partNumber' => $internal->part_number,
            'backNumber' => $internal->back_number,
            'partName' => $internal->part_name,
            'target' => $internal->customerPart->qty_per_kanban,
            'line' => $lineProd->name ?? null,
            'photo' => $internal->photo,
        ];
    }

    public function mutation(Request $request)
    {
        $data = $request->validate([
            'loadingList' => 'required|string',
            'customerPart' => 'required|string',
            'internalPart' => 'required|string',
            'serialNumber' => 'required|string',
            'qty_per_kbn' => 'required|numeric|min:1',
            'agMode' => 'sometimes|boolean',
            'customer_serial' => 'sometimes|nullable|string|max:32',
        ]);

        $data['qty_per_kbn'] = (int) round($data['qty_per_kbn']);
        $agMode = $request->boolean('agMode');

        $result = DB::transaction(function () use ($data, $agMode) {
            // 1) Ambil Loading List
            if (! isset(self::$loadingListsCache[$data['loadingList']])) {
                self::$loadingListsCache[$data['loadingList']] = LoadingList::select('id', 'number', 'customer_id')
                    ->where('number', $data['loadingList'])
                    ->first();
            }
            $loadingList = self::$loadingListsCache[$data['loadingList']];

            // dd( $data['loadingList']);

            if (! $loadingList) {
                return response()->json([
                    'status' => 'notExists',
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

            // 3) Ambil internal_part dan detail loading list yang benar-benar terhubung
            $internalPartKey = $data['internalPart'];
            if (! isset(self::$internalPartsCache[$internalPartKey])) {
                self::$internalPartsCache[$internalPartKey] = InternalPart::select('id', 'part_number', 'back_number', 'part_name')
                    ->where('part_number', $data['internalPart'])
                    ->first();
            }
            $internalPart = self::$internalPartsCache[$internalPartKey];

            if (! $internalPart) {
                return response()->json([
                    'status' => 'notExists',
                    'message' => 'Part atau Kanban tidak ditemukan!',
                ]);
            }

            $lld = LoadingListDetail::query()
                ->from('loading_list_details as lld')
                ->join('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
                ->where('lld.loading_list_id', $loadingList->id)
                ->where('cp.internal_part_id', $internalPart->id)
                ->where('cp.part_number', $convertedPartNumber)
                ->where('cp.customer_id', $loadingList->customer_id)
                ->lockForUpdate()
                ->first(['lld.id', 'lld.kanban_qty', 'lld.actual_kanban_qty']);

            if (! $lld) {
                $customerPartExists = DB::table('customer_parts')
                    ->where('internal_part_id', $internalPart->id)
                    ->where('part_number', $convertedPartNumber)
                    ->where('customer_id', $loadingList->customer_id)
                    ->exists();

                if ($customerPartExists) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Detail loading list tidak ditemukan untuk part ini.',
                    ]);
                }

                return response()->json([
                    'status' => 'notExists',
                    'message' => 'Part number customer tidak terdaftar!',
                    'data' => [
                        'int' => $data['internalPart'],
                        'cust' => $convertedPartNumber,
                    ],
                ]);
            }

            // 4) AG dan part pengecualian melewati gate serial/status kanban.
            $kanban = null;
            if (! $agMode && ! $internalPart->isPullingKanbanStatusExempt()) {
                $kanban = Kanban::where('internal_part_id', $internalPart->id)
                    ->where('serial_number', $data['serialNumber'])
                    ->lockForUpdate()
                    ->first();

                if (! $kanban) {
                    return response()->json([
                        'status' => 'notExists',
                        'message' => 'Kanban tidak terdaftar!',
                    ]);
                }

                if ((int) $kanban->status === Kanban::STATUS_PULLED) {
                    return response()->json([
                        'status' => 'alreadyPulled',
                        'message' => 'Kanban sudah pernah discan pulling!',
                    ]);
                }

                if (! $kanban->canBePulled()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Status kanban tidak valid.',
                    ]);
                }
            }

            // 6) Atomic increment: hanya increment jika actual < target (mencegah over-scan)
            if ((int) $lld->actual_kanban_qty >= (int) $lld->kanban_qty) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kanban sudah penuh',
                ]);
            }

            // update loading list detail
            LoadingListDetail::where('id', $lld->id)
                ->whereColumn('actual_kanban_qty', '<', 'kanban_qty')
                ->update([
                    'actual_kanban_qty' => DB::raw('actual_kanban_qty + 1'),
                ]);

            // 7) Catat mutasi
            $mutationPayload = [
                'internal_part_id' => $internalPart->id,
                'serial_number' => $data['serialNumber'],
                'type' => 'checkout',
                'qty' => (int) $data['qty_per_kbn'],
                'npk' => auth()->user()->npk ?? null,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            if ($this->mutationsHaveLoadingListColumns()) {
                $mutationPayload['loading_list_id'] = $loadingList->id;
                $mutationPayload['loading_list_detail_id'] = $lld->id;
            }

            if ($this->mutationsHaveAgModeColumn()) {
                $mutationPayload['ag_mode'] = $agMode;
            }

            // Pairing kanban customer (pilot TMMIN) — info only, tanpa validasi.
            if ($this->mutationsHaveCustomerSerialColumn()) {
                $customerSerial = trim((string) ($data['customer_serial'] ?? ''));
                $mutationPayload['customer_serial'] = $customerSerial !== '' ? $customerSerial : null;
            }

            $mutation = Mutation::create($mutationPayload);

            // 8) Status 1 -> 2 dilakukan dalam transaksi dan row lock yang sama.
            if ($kanban) {
                $kanban->update(['status' => Kanban::STATUS_PULLED]);
            }

            // 9) Siapkan payload sukses. Lookup alamat chute SENGAJA dilakukan
            //    di luar transaksi agar lock baris tidak ditahan selama query
            //    master alamat chute (yang bisa full-scan saat cache miss).
            return [
                'newActual' => $lld->actual_kanban_qty + 1,
                'convertedPartNumber' => $convertedPartNumber,
                'backNo' => trim((string) ($internalPart->back_number ?? '')),
                'scanEventId' => $mutation->id,
            ];
        });

        // Error dari dalam transaksi (JsonResponse) diteruskan apa adanya
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        // Lookup alamat chute bersifat read-only → aman di luar transaksi
        $alamatChute = MasterAlamatChute::resolveByPartCustomerAndBackNo(
            $result['convertedPartNumber'],
            $result['backNo']
        );

        return response()->json([
            'status' => 'success',
            'data' => $result['newActual'],
            'alamat_chute' => $alamatChute,
            'scan_event_id' => $result['scanEventId'],
        ]);
    }

    public function scanHistory(Request $request)
    {
        $validated = $request->validate([
            'pds' => ['nullable', 'string', 'max:80'],
            'loading_lists' => ['nullable', 'array'],
            'loading_lists.*' => ['nullable', 'string', 'max:80'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $loadingListNumbers = collect($validated['loading_lists'] ?? [])
            ->map(fn ($number) => trim((string) $number))
            ->filter()
            ->unique()
            ->take(20)
            ->values();
        $pds = trim((string) ($validated['pds'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 200);

        if ($loadingListNumbers->isEmpty() && $pds === '') {
            return response()->json(['status' => 'success', 'history' => [], 'sync' => []]);
        }

        $loadingLists = LoadingList::query()
            ->select('id', 'number')
            ->when($loadingListNumbers->isNotEmpty(), fn ($query) => $query->whereIn('number', $loadingListNumbers))
            ->when($loadingListNumbers->isEmpty() && $pds !== '', fn ($query) => $query->where('pds_number', $pds))
            ->limit(20)
            ->get();

        if ($loadingLists->isEmpty()) {
            return response()->json(['status' => 'success', 'history' => [], 'sync' => []]);
        }

        $details = DB::table('loading_list_details as lld')
            ->join('loading_lists as ll', 'll.id', '=', 'lld.loading_list_id')
            ->join('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
            ->join('internal_parts as ip', 'ip.id', '=', 'cp.internal_part_id')
            ->whereIn('lld.loading_list_id', $loadingLists->pluck('id'))
            ->where('lld.actual_kanban_qty', '>', 0)
            ->orderBy('ll.number')
            ->get([
                'll.number as loading_list',
                'll.id as loading_list_id',
                'lld.id as loading_list_detail_id',
                'cp.part_number as customer',
                'cp.internal_part_id',
                'ip.part_number as internal',
                'lld.kanban_qty',
                'lld.total_qty',
                'lld.actual_kanban_qty',
                'lld.created_at as detail_created_at',
                'lld.updated_at as detail_updated_at',
            ]);

        $history = collect();
        $sync = collect();
        $hasLinkedMutationColumns = $this->mutationsHaveLoadingListColumns();

        foreach ($details as $detail) {
            $actualCount = (int) $detail->actual_kanban_qty;
            if ($actualCount <= 0) {
                continue;
            }

            $mutations = collect();

            if ($hasLinkedMutationColumns) {
                $mutations = DB::table('mutations as m')
                    ->where('m.loading_list_detail_id', (int) $detail->loading_list_detail_id)
                    ->where('m.type', 'checkout')
                    ->whereNotNull('m.serial_number')
                    ->where('m.serial_number', '<>', '')
                    ->orderByDesc('m.created_at')
                    ->orderByDesc('m.id')
                    ->limit($actualCount)
                    ->get([
                        'm.id',
                        'm.serial_number',
                        'm.date',
                        'm.created_at',
                        DB::raw('1 as verified'),
                    ]);
            }

            if ($mutations->count() < $actualCount) {
                $seenMutationIds = $mutations->pluck('id')->filter()->all();
                $fallback = DB::table('mutations as m')
                    ->where('m.internal_part_id', (int) $detail->internal_part_id)
                    ->where('m.type', 'checkout')
                    ->whereNotNull('m.serial_number')
                    ->where('m.serial_number', '<>', '')
                    ->when($seenMutationIds !== [], fn ($query) => $query->whereNotIn('m.id', $seenMutationIds))
                    ->orderByDesc('m.created_at')
                    ->orderByDesc('m.id')
                    ->limit($actualCount - $mutations->count())
                    ->get([
                        'm.id',
                        'm.serial_number',
                        'm.date',
                        'm.created_at',
                        DB::raw('0 as verified'),
                    ]);

                $mutations = $mutations->concat($fallback);
            }

            $serials = $mutations->map(function ($mutation) {
                $scannedAt = $mutation->date ?: $mutation->created_at;

                return [
                    'mutation_id' => (int) $mutation->id,
                    'serial' => $mutation->serial_number,
                    'scanned_at' => $scannedAt ? Carbon::parse($scannedAt)->timestamp * 1000 : 0,
                    'verified' => (bool) ($mutation->verified ?? false),
                ];
            })->values();

            $sync->push([
                'customer' => $detail->customer,
                'internal' => $detail->internal,
                'loading_list' => $detail->loading_list,
                'target' => (int) ($detail->kanban_qty ?? 0),
                'qty_per_kbn' => ((int) ($detail->kanban_qty ?? 0)) > 0
                    ? ((int) ($detail->total_qty ?? 0) / (int) $detail->kanban_qty)
                    : 0,
                'scanned_count' => $actualCount,
                'serials' => $serials->all(),
            ]);

            foreach ($serials as $serial) {
                if ($history->count() >= $limit) {
                    break;
                }

                $history->push([
                    'customer' => $detail->customer,
                    'internal' => $detail->internal,
                    'serial' => $serial['serial'],
                    'mutation_id' => $serial['mutation_id'],
                    'loading_list' => $detail->loading_list,
                    'scanned_at' => $serial['scanned_at'],
                    'verified' => $serial['verified'] ?? false,
                    'source' => 'mutation',
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'history' => $history
                ->sortByDesc('scanned_at')
                ->values()
                ->all(),
            'sync' => $sync->values()->all(),
        ]);
    }

    public function post(Request $request)
    {
        $loadingLists = $request->input('loadingList', []);
        $token = $request->input('token');
        $data = [];
        $result = [];

        if (! is_array($loadingLists) || empty($loadingLists)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data loading list kosong.',
            ], 422);
        }

        $client = new Client([
            'verify' => false, // Temporarily disabling SSL verification
        ]);

        // loop the loading list & restructure the array
        foreach ($loadingLists as $loadingList => $items) {
            array_push($data, (object) ['loading_list_number' => $loadingList]);
            // check if items belongs to loading list based on index of the array
            foreach ($items as $item => $val) {
                if (array_key_exists($loadingList, $loadingLists) && array_key_exists($item, $loadingLists[$loadingList])) {
                    $result[] = [
                        'part_number_int' => $val['part_number_internal'],
                        'part_number_cust' => $val['part_number_customer'],
                        'serial_number' => $val['serial_number'],
                    ];
                }
            }
            $data[count($data) - 1]->items = $result;
            $result = [];
        }

        $detailUpdates = [];

        foreach ($data as $row) {
            $loadingListId = LoadingList::select('id', 'customer_id')
                ->where('number', $row->loading_list_number)
                ->first();

            if (! $loadingListId) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Loading list {$row->loading_list_number} tidak ditemukan.",
                ], 422);
            }

            $items = array_values((array) $row->items);
            try {
                [$deaItems] = $this->fetchBestDeaLoadingListItemsForPost(
                    $client,
                    (string) $token,
                    $row->loading_list_number,
                    $items
                );
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Gagal membaca detail loading list {$row->loading_list_number} dari DEA: ".$th->getMessage(),
                ], 502);
            }

            foreach ($items as $index => $item) {
                $customerPartNumber = trim((string) data_get($item, 'part_number_cust', ''));
                $internalPartNumber = trim((string) data_get($item, 'part_number_int', ''));
                $deaItem = $deaItems !== []
                    ? $this->findDeaKanbanItemForPost(
                        $deaItems,
                        $row->loading_list_number,
                        $customerPartNumber,
                        $internalPartNumber
                    )
                    : null;

                if ($deaItems !== [] && ! $deaItem) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Part '.data_get($item, 'part_number_cust').' / '.data_get($item, 'part_number_int')." tidak ditemukan di detail DEA loading list {$row->loading_list_number}.",
                    ], 422);
                }

                $localDetail = $this->findLoadingListDetailForPost(
                    $loadingListId->id,
                    $loadingListId->customer_id,
                    $row->loading_list_number,
                    $customerPartNumber,
                    $internalPartNumber
                );

                $items[$index]['part_number_cust'] = $deaItem['part_number_cust']
                    ?? $this->formatCustomerPartForDeaPost(
                        $localDetail?->part_number ?? $customerPartNumber,
                        $localDetail !== null
                    );
                $items[$index]['part_number_int'] = $deaItem['part_number_int'] ?? $internalPartNumber;
            }

            $row->items = $items;
            $kanbans = [];

            foreach ($items as $item) {
                $customerPartNumber = trim((string) data_get($item, 'part_number_cust', ''));
                $internalPartNumber = trim((string) data_get($item, 'part_number_int', ''));
                $key = $customerPartNumber.'||'.$internalPartNumber;

                if (! isset($kanbans[$key])) {
                    $kanbans[$key] = [
                        'customer' => $customerPartNumber,
                        'internal' => $internalPartNumber,
                        'actual_scanned' => 0,
                    ];
                }

                $kanbans[$key]['actual_scanned']++;
            }

            foreach ($kanbans as $kanban) {
                $kanbanQty = $this->findLoadingListDetailForPost(
                    $loadingListId->id,
                    $loadingListId->customer_id,
                    $row->loading_list_number,
                    $kanban['customer'],
                    $kanban['internal']
                );

                if (! $kanbanQty) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Detail part {$kanban['customer']} tidak ditemukan di loading list {$row->loading_list_number}.",
                    ], 422);
                }

                $detailUpdates[] = [
                    'id' => $kanbanQty->id,
                    'actual_scanned' => $kanban['actual_scanned'],
                    'kanban_qty' => $kanbanQty->kanban_qty,
                ];
            }
        }

        // post data
        try {
            foreach ($data as $row) {
                // Detail DEA bisa butuh suffix untuk dibaca, tapi POST /kanbans memvalidasi nomor LL asli.
                $client->post('https://dea-dev.aiia.co.id/api/v1/kanbans', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$token,
                    ],
                    'json' => $row,
                ]);
            }
        } catch (\GuzzleHttp\Exception\RequestException $th) {
            $body = $th->getResponse() ? (string) $th->getResponse()->getBody() : '';

            return response()->json([
                'status' => 'error',
                'message' => $body !== '' ? $body : $th->getMessage(),
            ], 502);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 502);
        }

        foreach ($detailUpdates as $update) {
            if ($update['actual_scanned'] < $update['kanban_qty']) {
                LoadingListDetail::where('id', $update['id'])
                    ->where('actual_kanban_qty', '<', $update['actual_scanned'])
                    ->update([
                        'actual_kanban_qty' => $update['actual_scanned'],
                    ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function kanbanCheck(Request $request)
    {
        //get all req
        $internal = $request->internal;
        $seri = $request->seri;

        // get internal part number id
        $internalPart = InternalPart::select('id', 'back_number')->where('part_number', $internal)->first();
        if (! $internalPart) {
            return response()->json([
                'status' => 'partNotExist',
                'message' => 'Part number tidak terdaftar',
            ], 404);
        }

        if ($internalPart->isPullingKanbanStatusExempt()) {
            return ['status' => 'success'];
        }

        // check if kanban exist
        $kanban = Kanban::select('id', 'status')
            ->where('internal_part_id', $internalPart->id)
            ->where('serial_number', $seri)
            ->first();
        if (! $kanban) {
            return response()->json([
                'status' => 'kanbanNotExist',
                'message' => 'Kanban tidak terdaftar',
            ], 404);
        }

        if ((int) $kanban->status === Kanban::STATUS_PULLED) {
            return response()->json([
                'status' => 'alreadyPulled',
                'message' => 'Kanban sudah pernah discan pulling!',
            ], 409);
        }

        if (! $kanban->canBePulled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status kanban tidak valid.',
            ], 422);
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
                    'kanban_id' => null,
                ]);
            }

            // create data at kanban after pulls table
            foreach ($kanbanAfterProd as $kanbanAfterProd) {
                KanbanAfterPulling::create([
                    'kanban_id' => $kanbanAfterProd->kanban_id,
                    'internal_part_id' => $kanbanAfterProd->internal_part_id,
                    'code' => $kanbanAfterProd->code,
                    'npk' => auth()->user()->npk,
                    'date' => Carbon::now()->format('Y-m-d'),
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
                'data' => $kanban->id,
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

    public function edclAuth($username, $password, bool $forceRefresh = false)
    {
        $cacheKey = 'tmmin_edcl_token';

        // Pakai token yang sudah di-cache supaya tidak login eksternal tiap scan
        if (! $forceRefresh) {
            $cached = cache()->get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-apihub-key' => env('API_TMMIN_KEY'),
            ])
                ->connectTimeout(5)
                ->timeout(10)
                ->post(env('API_TMMIN').'auth/login', [
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

            // Cache hanya token yang valid (jangan cache kegagalan)
            if (is_string($accessToken) && $accessToken !== '') {
                $ttl = (int) env('TMMIN_TOKEN_TTL_MINUTES', 30);
                cache()->put($cacheKey, $accessToken, now()->addMinutes($ttl));
            }

            return $accessToken; // Output the token
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Authentication failed, please try again'], 500);
        }
    }

    public function edcl($skid, $manifest, $itemNo, $seqNo, $customerPart, $originalBarcode, $loadingList, $customer)
    {
        // Authenticate and get the token
        $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD')) ?? null;

        if (! $token) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        // get loading list id
        $loadingListId = LoadingList::select('id')->where('number', $loadingList)->first();
        if (! $loadingListId) {
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!',
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

        if (! $customerPartId) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer tidak terdaftar!',
                'customer' => $customer,
                'customer_part' => $this->convertPartNumber($loadingList, $customerPart),
            ];
        }

        // Prepare the data for the API request
        $data = [
            [
                'supplierCode' => env('SUPPLIER_CODE'),
                'supplierPlant' => '2',
                'skidNo' => 'SKD'.$manifest.'00'.$skid, // Replace with actual value if needed
                'manifestNo' => $manifest,
                'itemNo' => $itemNo,
                'seqNo' => $seqNo,
                'kanbanId' => $originalBarcode,
            ],
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

        // Send the request to the EDCL API (timeout + retry sekali bila token cache kedaluwarsa)
        $postConfirm = function ($token) use ($data) {
            return Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'x-apihub-key' => env('API_TMMIN_KEY'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->connectTimeout(5)
                ->timeout(15)
                ->post(env('API_TMMIN').'ManifestCompleteness/confirm', $data);
        };

        $response = $postConfirm($token);

        // Token cache bisa kedaluwarsa di sisi server → refresh sekali lalu ulangi
        if ($response->status() === 401) {
            $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD'), true);
            if (! is_string($token) || $token === '') {
                return response()->json(['error' => 'Authentication failed'], 401);
            }
            $response = $postConfirm($token);
        }

        // get loading list detail table
        $loadingListDetailId = LoadingListDetail::select('id')
            ->where('loading_list_id', $loadingListId->id)
            ->where('customer_part_id', $customerPartId->id)
            ->first();

        if (! $loadingListDetailId) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer / loading list tidak sesuai!',
                'data' => [
                    'loading_list_id' => $loadingListId->id,
                    'customer_part_id' => $customerPartId->id,
                ],
            ];
        }

        // Process the response
        if ($response['message'] === 'Success - Confirm Manifest') {
            try {
                DB::beginTransaction();

                // Check if the kanban_id already exists
                $existingSkid = SkidDetail::where('kanban_id', $data[0]['kanbanId'])->exists();

                if (! $existingSkid) {
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
                    'status' => 500,
                ], 500);
            }

            // Handle successful response
            return response()->json([
                'status' => 'success',
                'message' => $response['message'],
                'data' => $response['data']['successes'],
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
                'data' => $response['data']['faileds'],
            ], $response['status']);
        } else {
            // Handle unexpected response
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected response',
                'data' => [],
            ], 500);
        }
    }

    public function edclCancel($id)
    {
        // Authenticate and get the token
        $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD')) ?? null;

        if (! $token) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        // get skid detail
        $skidData = SkidDetail::where('id', $id)->first();

        $data = [
            [
                'supplierCode' => env('SUPPLIER_CODE'),
                'supplierPlant' => '2',
                'skidNo' => $skidData->skid_no, // Replace with actual value if needed
                'manifestNo' => substr($skidData->skid_no, 3, 10),
                'itemNo' => (int) $skidData->item_no, // Replace with actual value if needed
                'seqNo' => (int) $skidData->serial, // Replace with actual value
                'kanbanId' => $skidData->kanban_id, // Replace with actual value if needed
            ],
        ];

        $postCancel = function ($token) use ($data) {
            return Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'x-apihub-key' => env('API_TMMIN_KEY'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->connectTimeout(5)
                ->timeout(15)
                ->post(env('API_TMMIN').'ManifestCompleteness/cancel', $data);
        };

        $response = $postCancel($token);

        // Token cache bisa kedaluwarsa di sisi server → refresh sekali lalu ulangi
        if ($response->status() === 401) {
            $token = $this->edclAuth(env('TMMIN_USERNAME'), env('TMMIN_PASSWORD'), true);
            if (! is_string($token) || $token === '') {
                return response()->json(['error' => 'Authentication failed'], 401);
            }
            $response = $postCancel($token);
        }

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
                    'data' => $response['data']['successes'],
                ], $response['status']);
            } catch (\Throwable $th) {
                //throw $th;
                DB::rollBack();

                return response()->json([
                    'error' => $th->getMessage(),
                    'status' => 500,
                ], 500);
            }
        } elseif ($response['message'] === 'Failed - Cancel Manifest') {
            // Handle failed response
            return response()->json([
                'status' => 'error',
                'message' => $response['data']['faileds'][0]['message'],
                'data' => $response['data']['faileds'],
            ], $response['status']);
        } else {
            // Handle unexpected response
            return response()->json([
                'status' => 'error',
                'message' => 'Unexpected response',
                'data' => [],
            ], 500);
        }
    }

    public function manual()
    {
        $customers = Customer::all(); // Atau filter sesuai kebutuhan

        return view('pages.pulling.manual', compact('customers'));
    }

    public function masterAlamatChuteIndex()
    {
        $masters = MasterAlamatChute::query()
            ->orderBy('part_no_customer')
            ->orderBy('back_no')
            ->orderBy('part_name')
            ->get();

        return view('pages.pulling.master_alamat_chute', compact('masters'));
    }

    public function downloadMasterAlamatChuteTemplate()
    {
        return Excel::download(
            new MasterAlamatChuteTemplateExport,
            'template_master_alamat_chute.xlsx'
        );
    }

    public function importMasterAlamatChute(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->getRealPath();
        if ($path === false || $path === '') {
            $path = $uploaded->getPathname();
        }

        try {
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Throwable $e) {
            return redirect()
                ->route('pulling.master-alamat-chute')
                ->withErrors(['file' => 'Gagal membaca file: '.$e->getMessage()]);
        }

        $importer = new MasterAlamatChuteImporter;
        $importer->processSheetRows($rows);

        $redirect = redirect()->route('pulling.master-alamat-chute');

        if ($importer->imported > 0) {
            $redirect->with(
                'success',
                "Tersimpan {$importer->imported} baris ke master alamat chute (Part no customer | Back No | Part name | Alamat Chute)."
            );
        }

        if ($importer->errors !== []) {
            $lines = $importer->errors;
            if (count($lines) > 30) {
                $lines = array_merge(
                    array_slice($lines, 0, 30),
                    ['… dan '.(count($importer->errors) - 30).' pesan lainnya.']
                );
            }
            $redirect->with('import_notice', implode("\n", $lines));
        } elseif ($importer->imported === 0) {
            $redirect->with(
                'import_notice',
                'Tidak ada baris yang diimpor. Pastikan baris pertama berisi header kolom (sesuai template) dan ada data di bawahnya.'
            );
        }

        return $redirect;
    }

    public function clearAllMasterAlamatChute()
    {
        $count = MasterAlamatChute::query()->count();

        if ($count === 0) {
            return redirect()
                ->route('pulling.master-alamat-chute')
                ->with('success', 'Tidak ada data master alamat chute yang perlu dihapus.');
        }

        MasterAlamatChute::query()->delete();
        try {
            cache()->increment('chute_cache_version');
        } catch (\Exception $e) {
        }

        return redirect()
            ->route('pulling.master-alamat-chute')
            ->with('success', "Semua data master alamat chute ({$count} baris) berhasil dihapus. Anda dapat mengimpor ulang file Excel terbaru.");
    }

    public function storeMasterAlamatChute(Request $request)
    {
        $validated = $request->validate([
            'part_no_customer' => 'required|string|max:100',
            'back_no' => 'nullable|string|max:50',
            'part_name' => 'nullable|string|max:150',
            'alamat_chute' => 'required|string|max:100',
        ]);

        $partNoCustomer = trim($validated['part_no_customer']);
        $backNo = trim((string) ($validated['back_no'] ?? ''));
        $partName = trim((string) ($validated['part_name'] ?? ''));

        MasterAlamatChute::updateOrCreate(
            [
                'part_no_customer' => $partNoCustomer,
                'back_no' => $backNo,
            ],
            [
                'part_name' => $partName,
                'alamat_chute' => trim($validated['alamat_chute']),
            ]
        );

        return redirect()
            ->route('pulling.master-alamat-chute')
            ->with('success', 'Master alamat Chute berhasil disimpan.');
    }

    public function updateMasterAlamatChute(Request $request, MasterAlamatChute $master_alamat_chute)
    {
        $validated = $request->validate([
            'part_no_customer' => 'required|string|max:100',
            'back_no' => 'nullable|string|max:50',
            'part_name' => 'nullable|string|max:150',
            'alamat_chute' => 'required|string|max:100',
        ]);

        $partNoCustomer = trim($validated['part_no_customer']);
        $backNo = trim((string) ($validated['back_no'] ?? ''));
        $partName = trim((string) ($validated['part_name'] ?? ''));

        $duplicate = MasterAlamatChute::query()
            ->where('part_no_customer', $partNoCustomer)
            ->where('back_no', $backNo)
            ->where('id', '!=', $master_alamat_chute->id)
            ->exists();

        if ($duplicate) {
            return redirect()
                ->route('pulling.master-alamat-chute')
                ->withErrors(['part_no_customer' => 'Kombinasi Part no customer dan Back No sudah terdaftar.']);
        }

        $master_alamat_chute->update([
            'part_no_customer' => $partNoCustomer,
            'back_no' => $backNo,
            'part_name' => $partName,
            'alamat_chute' => trim($validated['alamat_chute']),
        ]);

        return redirect()
            ->route('pulling.master-alamat-chute')
            ->with('success', 'Master alamat Chute berhasil diperbarui.');
    }

    public function deleteMasterAlamatChute(MasterAlamatChute $master_alamat_chute)
    {
        $master_alamat_chute->delete();

        return redirect()
            ->route('pulling.master-alamat-chute')
            ->with('success', 'Master alamat Chute berhasil dihapus.');
    }

    public function manualReset(Request $request)
    {
        $validated = $request->validate([
            'internal' => 'nullable|required_without:back_number|string|max:100',
            'back_number' => 'nullable|required_without:internal|string|max:50',
            'serial' => 'required|string|max:10',
        ]);

        $serial = strtoupper(trim($validated['serial']));
        $lookupByBackNumber = filled($validated['back_number'] ?? null);
        $partColumn = $lookupByBackNumber ? 'back_number' : 'part_number';
        $partLabel = $lookupByBackNumber ? 'Back Number' : 'Internal Part';
        $partKey = strtoupper(trim((string) ($lookupByBackNumber ? $validated['back_number'] : $validated['internal'])));

        $internalPart = InternalPart::with('customerPart')->where($partColumn, $partKey)->first();

        if (! $internalPart) {
            return response()->json([
                'status' => 'error',
                'message' => "{$partLabel} <strong>".e($partKey).'</strong> tidak ditemukan.',
            ], 404);
        }

        $kanban = Kanban::where('internal_part_id', $internalPart->id)
            ->where('serial_number', $serial)
            ->first();

        if (! $kanban) {
            return response()->json([
                'status' => 'error',
                'message' => 'Serial Number <strong>'.e($serial)."</strong> tidak ditemukan untuk {$partLabel} <strong>".e($partKey).'</strong>.',
            ], 404);
        }

        $kanban->status = 0;
        $kanban->save();

        Mutation::create([
            'internal_part_id' => $internalPart->id,
            'serial_number' => $serial,
            'qty' => optional($internalPart->customerPart)->qty_per_kanban,
            'type' => 'manual',
            'npk' => auth()->user()->npk,
            'date' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kanban berhasil di-reset.',
        ]);
    }
}
