<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use Monolog\Logger;

use App\Models\Line;
use App\Models\Part;
use App\Models\Kanban;
use App\Models\Customer;
use App\Models\Mutation;
use App\Models\Injection;
use PhpMqtt\ClientBuilder;
use App\Models\ScannedPart;
use Illuminate\Support\Str;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Services\CustomerKanbanResolver;
use App\Services\KanbanBarcodeParser;
use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\KanbanAfterProd;
use App\Models\ProductionStock;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PhpMqtt\Client\ConnectionSettings;



class ProductionController extends Controller
{
    private const AS547_TYPE_RIGHT = 'as547_kanan';

    private const AS547_TYPE_LEFT = 'as547_kiri';

    private const AS547_LINE = 'AS547';

    /**
     * Ambang konfirmasi scan beruntun (detik).
     *
     * Kanban customer AS547 (5716A871 / 5716A872) hanya memuat nomor part tanpa
     * serial, jadi dua box tidak bisa dibedakan dari barcode-nya. Sebagai gantinya
     * scan yang terlalu rapat dimintakan konfirmasi — bukan ditolak, karena dari
     * 327 jeda produksi nyata ada 7 jeda di bawah 2 menit yang semuanya sah
     * (MP menyusul input di awal sesi). Tidak ada satu pun jeda nyata antara
     * 119 dan 290 detik, jadi 120 detik jatuh di celah kosong antara keduanya.
     */
    private const AS547_FAST_SCAN_SECONDS = 120;

    /** Rentang pengecekan kanban internal dobel (jam), cukup untuk satu shift. */
    private const AS547_DUPLICATE_WINDOW_HOURS = 12;

    private ?CustomerKanbanResolver $customerKanbanResolver = null;

    private ?KanbanBarcodeParser $kanbanParser = null;

    private function customerKanban(): CustomerKanbanResolver
    {
        return $this->customerKanbanResolver ??= new CustomerKanbanResolver();
    }

    private function kanbanParser(): KanbanBarcodeParser
    {
        return $this->kanbanParser ??= new KanbanBarcodeParser();
    }

    /**
     * Kenali satu hasil scan AS547. Halaman menerima dua jenis kanban sekaligus:
     *
     *  - kanban internal cetakan plant : barcode panjang (218-241 karakter), punya serial
     *  - kanban customer               : nomor part pendek (8-15 karakter), tanpa serial
     *
     * Keduanya tidak mungkin tertukar karena panjangnya terpaut jauh, jadi tidak
     * perlu tombol mode — jenisnya ditentukan dari barcode itu sendiri.
     *
     * @return array{status: string, message?: string, data?: array}
     */
    private function as547ResolveScan(string $raw): array
    {
        $code = trim($raw);

        if ($code === '') {
            return ['status' => 'invalid', 'message' => 'Barcode kosong.'];
        }

        $parsed = $this->kanbanParser()->parse($code);

        if ($parsed) {
            $part = InternalPart::with('line')->where('part_number', $parsed['part_number'])->first();

            if (! $part) {
                return [
                    'status'  => 'not_found',
                    'message' => 'Part ' . $parsed['part_number'] . ' dari kanban internal tidak terdaftar di master.',
                ];
            }

            return [
                'status' => 'ok',
                'data'   => [
                    'source'          => 'internal',
                    'code'            => $parsed['part_number'],
                    'internal_part'   => $part,
                    'serial'          => $parsed['serial'],
                    'customer_serial' => null,
                    'qty_per_kanban'  => $this->as547QtyFor($part),
                ],
            ];
        }

        $resolved = $this->customerKanban()->resolve($code, self::AS547_LINE);

        if ($resolved['status'] !== 'ok') {
            return $resolved;
        }

        $data = $resolved['data'];

        return [
            'status' => 'ok',
            'data'   => [
                'source'          => 'customer',
                'code'            => $data['customer_part_number'],
                'internal_part'   => $data['internal_part'],
                'serial'          => null,
                'customer_serial' => $data['customer_serial'],
                'qty_per_kanban'  => $data['qty_per_kanban'],
            ],
        ];
    }

    private function as547QtyFor(InternalPart $part): ?int
    {
        $customerPart = CustomerPart::where('internal_part_id', $part->id)
            ->whereNotNull('qty_per_kanban')
            ->first();

        return $customerPart ? (int) $customerPart->qty_per_kanban : null;
    }

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexprdreport()
    {
        return view('pages.production.prdreport', [
            'productionLines' => Line::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function as523()
    {
        return view('pages.production.as523');
    }

    public function pc2b()
    {
        return view('pages.production.pc2b');
    }

    public function as547()
    {
        return view('pages.production.as547');
    }

    /**
     * Simpan scan kanban PC2B ke tabel mutations.
     * Dipanggil setelah parse barcode kanban (part_number + serial_number).
     *
     * @param  \Illuminate\Http\Request  $request  part_number, serial_number, side (R|L)
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePc2bKanbanScan(Request $request)
    {
        return $this->storePairedKanbanScan($request, 'pc2b');
    }

    /**
     * Resolve kanban customer jadi pasangan part RH/LH untuk halaman AS547.
     * Dipakai saat scan PERTAMA (tanda awal produksi) — tidak menulis mutation apa pun.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function as547Pair(Request $request)
    {
        $code = trim((string) $request->query('code', ''));

        if ($code === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Barcode kanban customer kosong.',
            ], 422);
        }

        $resolved = $this->as547ResolveScan($code);

        if ($resolved['status'] !== 'ok') {
            return response()->json([
                'status'  => 'error',
                'message' => $resolved['message'] ?? 'Kanban tidak dikenali.',
            ], $resolved['status'] === 'not_found' ? 404 : 422);
        }

        $part = $resolved['data']['internal_part'];
        $side = $this->as547SideFromPartName($part->part_name);

        if (!$side) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Part name "' . $part->part_name . '" tidak diakhiri RH / LH, pasangan tidak bisa ditentukan.',
            ], 422);
        }

        $pair = $this->as547FindPair($part, $side);

        if (!$pair) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pasangan ' . ($side === 'R' ? 'LH' : 'RH') . ' untuk "' . $part->part_name . '" tidak ditemukan di line yang sama.',
            ], 404);
        }

        $rh = $side === 'R' ? $part : $pair;
        $lh = $side === 'R' ? $pair : $part;

        return response()->json([
            'status' => 'success',
            'side'   => $side,
            'line'   => optional($part->line)->name,
            'source' => $resolved['data']['source'],
            'data'     => [
                'R' => $this->as547PartPayload($rh),
                'L' => $this->as547PartPayload($lh),
            ],
        ]);
    }

    /**
     * Simpan satu box AS547. Menerima kanban internal maupun kanban customer.
     *
     * Penjagaan scan dobel menyesuaikan jenis kanban yang discan:
     *  - kanban internal punya serial, jadi kartu yang sama persis bisa dikenali
     *  - kanban customer tanpa serial, jadi yang dipakai kerapatan waktu
     *
     * Keduanya tidak menolak mentah-mentah, melainkan meminta konfirmasi MP, supaya
     * tidak ada box sah yang tertahan dan tidak ada box yang terhitung diam-diam.
     *
     * @param  \Illuminate\Http\Request  $request  code, expected_side, expected_part, confirm
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAs547KanbanScan(Request $request)
    {
        $request->validate([
            'code'          => ['required', 'string', 'max:255'],
            'expected_side' => ['required', 'string', 'in:R,L'],
            'expected_part' => ['required', 'string', 'max:64'],
            'confirm'       => ['nullable', 'boolean'],
        ]);

        $expectedSide = $request->expected_side;
        $resolved     = $this->as547ResolveScan($request->code);

        if ($resolved['status'] !== 'ok') {
            return response()->json([
                'status'  => 'error',
                'message' => $resolved['message'] ?? 'Kanban tidak dikenali.',
            ], 422);
        }

        $data         = $resolved['data'];
        $internalPart = $data['internal_part'];
        $side         = $this->as547SideFromPartName($internalPart->part_name);

        if (! $side) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Part name "' . $internalPart->part_name . '" tidak diakhiri RH / LH.',
            ], 422);
        }

        if ($side !== $expectedSide) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kanban ini ' . ($side === 'R' ? 'RH' : 'LH') . ', sekarang harus scan '
                    . ($expectedSide === 'R' ? 'KANAN (RH)' : 'KIRI (LH)') . '.',
            ], 422);
        }

        // Sisi boleh cocok tapi partnya beda model — mis. kanban RH dari line lain.
        if (strcasecmp($internalPart->part_number, trim($request->expected_part)) !== 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kanban ini untuk part ' . $internalPart->part_number
                    . ', sedangkan model yang sedang jalan ' . trim($request->expected_part) . '.',
            ], 422);
        }

        $type      = $side === 'R' ? self::AS547_TYPE_RIGHT : self::AS547_TYPE_LEFT;
        $confirmed = $request->boolean('confirm');

        if (! $confirmed) {
            $question = $data['source'] === 'internal' && $data['serial']
                ? $this->as547DuplicateSerialQuestion($internalPart, $data['serial'], $type)
                : $this->as547FastScanQuestion($internalPart, $type, $side);

            if ($question) {
                return response()->json($question);
            }
        }

        $qty = $data['qty_per_kanban'] ?? 1;

        try {
            $mutation = DB::transaction(function () use ($internalPart, $side, $type, $qty, $data) {
                $now = Carbon::now();

                // Kanban internal membawa serial aslinya; kanban customer tidak punya,
                // jadi nomor box dibuat sistem supaya tiap baris tetap bisa dibedakan.
                $serial = $data['serial'];

                if (! $serial) {
                    $sequence = Mutation::where('type', $type)
                        ->whereDate('date', $now->copy()->startOfDay())
                        ->count() + 1;

                    $serial = sprintf('%s-%s-%s%03d', self::AS547_LINE, $now->format('Ymd'), $side, $sequence);
                }

                $payload = [
                    'internal_part_id' => $internalPart->id,
                    'serial_number'    => $serial,
                    'qty'              => (int) $qty,
                    'type'             => $type,
                    'npk'              => auth()->user()->npk ?? 'SYS',
                    'date'             => $now->format('Y-m-d H:i:s'),
                ];

                if ($data['customer_serial'] !== null && $this->mutationsHaveCustomerSerial()) {
                    $payload['customer_serial'] = $data['customer_serial'];
                }

                return Mutation::create($payload);
            });

            return response()->json([
                'status'      => 'success',
                'message'     => $side === 'R' ? 'Box KANAN (RH) OK.' : 'Box KIRI (LH) OK.',
                'qty'         => (int) $qty,
                'side'        => $side,
                'source'      => $data['source'],
                'back_number' => $internalPart->back_number,
                'serial'      => $mutation->serial_number,
                'next_side'   => $side === 'R' ? 'L' : 'R',
                'mutation_id' => $mutation->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan mutation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kanban internal: kartu dengan serial yang sama persis sudah pernah masuk?
     */
    private function as547DuplicateSerialQuestion(InternalPart $part, string $serial, string $type): ?array
    {
        $duplicate = Mutation::where('internal_part_id', $part->id)
            ->where('serial_number', $serial)
            ->whereIn('type', [self::AS547_TYPE_RIGHT, self::AS547_TYPE_LEFT])
            ->where('date', '>=', Carbon::now()->subHours(self::AS547_DUPLICATE_WINDOW_HOURS))
            ->orderByDesc('date')
            ->first(['date']);

        if (! $duplicate) {
            return null;
        }

        return [
            'status'  => 'confirm',
            'reason'  => 'duplicate',
            'message' => 'Kanban ' . $part->part_number . ' / ' . $serial . ' sudah discan jam '
                . Carbon::parse($duplicate->date)->format('H:i:s') . '. Tetap hitung sebagai box baru?',
        ];
    }

    /**
     * Kanban customer: tidak ada serial, jadi yang dinilai kerapatan waktunya.
     */
    private function as547FastScanQuestion(InternalPart $part, string $type, string $side): ?array
    {
        $last = Mutation::where('internal_part_id', $part->id)
            ->where('type', $type)
            ->orderByDesc('date')
            ->first(['date']);

        if (! $last) {
            return null;
        }

        $seconds = Carbon::parse($last->date)->diffInSeconds(Carbon::now());

        if ($seconds >= self::AS547_FAST_SCAN_SECONDS) {
            return null;
        }

        return [
            'status'  => 'confirm',
            'reason'  => 'fast',
            'seconds' => $seconds,
            'message' => 'Box ' . ($side === 'R' ? 'KANAN' : 'KIRI') . ' terakhir baru discan '
                . $seconds . ' detik lalu (jam ' . Carbon::parse($last->date)->format('H:i:s')
                . '). Ini box baru?',
        ];
    }

    private function mutationsHaveCustomerSerial(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('mutations', 'customer_serial');
    }

    /**
     * Tentukan sisi part dari akhiran part name: "... RH" => R, "... LH" => L.
     */
    private function as547SideFromPartName($partName): ?string
    {
        $name = strtoupper(trim((string) $partName));
        if ($name === '') {
            return null;
        }

        if (preg_match('/(?:^|[\s,\-])(RH|LH)$/', $name, $match)) {
            return $match[1] === 'RH' ? 'R' : 'L';
        }

        return null;
    }

    /**
     * Nama dasar part tanpa akhiran RH / LH, dinormalisasi untuk pencocokan pasangan.
     */
    private function as547BaseName($partName): string
    {
        $name = strtoupper(trim((string) $partName));
        $name = preg_replace('/(?:^|[\s,\-])(RH|LH)$/', '', $name);

        return preg_replace('/\s+/', ' ', trim((string) $name, " \t\n\r\0\x0B,-"));
    }

    /**
     * Cari pasangan part: part name dasar sama, line sama, sisi berlawanan.
     */
    private function as547FindPair(InternalPart $part, string $side): ?InternalPart
    {
        $base = $this->as547BaseName($part->part_name);
        if ($base === '') {
            return null;
        }

        $wantSide = $side === 'R' ? 'L' : 'R';

        $query = InternalPart::where('id', '!=', $part->id)
            ->where('part_name', 'like', $base . '%');

        if ($part->line_id === null) {
            $query->whereNull('line_id');
        } else {
            $query->where('line_id', $part->line_id);
        }

        foreach ($query->get() as $candidate) {
            if ($this->as547BaseName($candidate->part_name) !== $base) {
                continue;
            }

            if ($this->as547SideFromPartName($candidate->part_name) === $wantSide) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Payload part untuk dipakai halaman AS547 (gambar PIS, back number, qty per box).
     */
    private function as547PartPayload(InternalPart $part): array
    {
        $customerParts = CustomerPart::where('internal_part_id', $part->id)->get();
        $withQty       = $customerParts->firstWhere('qty_per_kanban', '!=', null);

        return [
            'id'          => $part->id,
            'part_number' => $part->part_number,
            'back_number' => $part->back_number,
            'part_name'   => $part->part_name,
            'photo'       => $part->photo,
            // Halaman mencocokkan hasil scan ke daftar ini, jadi tidak perlu
            // bolak-balik ke server untuk setiap box.
            'customer_part_numbers' => $customerParts->pluck('part_number')
                ->filter()
                ->map(function ($value) {
                    return strtoupper(trim($value));
                })
                ->unique()
                ->values()
                ->all(),
            'qty_per_kanban' => $withQty ? (int) $withQty->qty_per_kanban : null,
        ];
    }

    private function storePairedKanbanScan(Request $request, string $line)
    {
        $request->validate([
            'part_number'   => ['required', 'string', 'max:64'],
            'serial_number' => ['required', 'string', 'max:32'],
            'side'          => ['required', 'string', 'in:R,L'],
        ]);

        $partNumber   = $request->part_number;
        $serialNumber = $request->serial_number;
        $side         = $request->side;

        $internalPart = InternalPart::where('part_number', $partNumber)->first();
        if (!$internalPart) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Part number tidak ditemukan di internal_parts.',
            ], 422);
        }

        $customerPart = CustomerPart::where('internal_part_id', $internalPart->id)->first();
        $qty          = $customerPart && $customerPart->qty_per_kanban !== null
            ? (int) $customerPart->qty_per_kanban
            : 1;

        $type = $line.'_'.($side === 'R' ? 'kanan' : 'kiri');

        try {
            Mutation::create([
                'internal_part_id' => $internalPart->id,
                'serial_number'   => $serialNumber,
                'qty'             => $qty,
                'type'            => $type,
                'npk'             => auth()->user()->npk ?? 'SYS',
                'date'            => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => $side === 'R' ? 'Scan kanban kanan berhasil disimpan.' : 'Scan kanban kiri berhasil disimpan.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan mutation: ' . $e->getMessage(),
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

        $partName = strtoupper($internalPart->part_name);

        // get line of internal part based on internal part id
        $lineProd = Line::select('name')->where('id', $internalPart->line_id)->first();
        // get customer internalPart based on internal internalPart id
        $customerPart = CustomerPart::select('qty_per_kanban')->where('internal_part_id', $internalPart->id)->first();

        // get kanban_id based on internal part id
        $kanban = Kanban::where('internal_part_id', $internalPart->id)
            ->where('serial_number', $seri)
            ->first();

        if (!$kanban) {
            return [
                'status' => 'error',
                'message' => 'Kanban tidak terdaftar!'
            ];
        }

        // Cek jika mengandung kata SUB ASSY dalam variasi yang dimaksud
        if (isset($internalPart->back_number) && strlen($internalPart->back_number) >= 2 && $internalPart->back_number[1] === 'O') {
            // Lewati pengecekan status
        } else {
            if ($kanban->status == 1) {
                return [
                    'status' => 'kanbanExist',
                    'message' => 'Kanban Sudah di scan!'
                ];
            }
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
            if (! $kanban->update([
                'status' => Kanban::STATUS_PRODUCED
            ])) {
                throw new \RuntimeException('Gagal memperbarui status kanban.');
            }

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
            $datas = [
                'data' => [
                    [
                        'line_id' => $lineProd->name,
                        'prd_dt' => (new DateTime($request->start_time))->format('Y-m-d'),
                        'str_dt' => $request->start_time,
                        'end_dt' => "",
                        'matnr'  => $request->partNumber,
                        'menge' => $customerPart->qty_per_kanban,
                        'crtby'  => auth()->user()->npk,
                    ]
                ]
            ];

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
        $request->validate([
            'internal_part_id' => ['required', 'integer', 'exists:internal_parts,id'],
            'standard_stock' => ['nullable', 'integer', 'min:0'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'active_line' => ['nullable', 'string', 'max:64'],
            'back_number' => ['nullable', 'string', 'max:64'],
        ]);

        $redirect = redirect()->route('dashboard.productionStock', array_filter([
            'line' => trim((string) $request->active_line),
            'back_number' => trim((string) $request->back_number),
        ], static fn ($value) => $value !== ''));

        try {
            DB::beginTransaction();

            $actual_stock = $request->filled('current_stock') ? (int) $request->current_stock : null;

            if ($request->standard_stock !== null) {
                InternalPart::where('id', $request->internal_part_id)
                    ->update([
                        'standard_stock' => (int) $request->standard_stock,
                    ]);
            }

            if ($actual_stock !== null) {
                ProductionStock::adjustCurrentStock(
                    (int) $request->internal_part_id,
                    $actual_stock,
                    auth()->user()->npk ?? 'SYS'
                );
            }

            DB::commit();

            return $redirect->with('success', 'Updated Successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();

            return $redirect->with('error', 'Error: ' . $th->getMessage());
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

    public function updateScanTarget($line, $target)
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

    public function getListStop($line, $category)
    {
        $response = Http::withOptions([
            'verify' => false
        ])->get('https://prdreport.aiia.co.id/prd_body/?action=api_list_stop&line=' . $line . '&category=' . $category);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to fetch stop list.'], 500);
        }
    }

    public function insertStop(Request $request)
    {
        $data = $request->all();
        // dd($data);
        $response = Http::withOptions([
            'verify' => false
        ])->post(
            'https://prdreport.aiia.co.id/prd_body/?action=api_insert_inb_stop',
            $data
        );

        if ($response->successful()) {
            return response()->json(['status' => 'success', 'data' => $response->json()]);
        } else {
            return response()->json(['status' => 'error', 'message' => $response->body()], 500);
        }
    }

    public function inboundStop(Request $request)
    {
        $part = $request->all();

        $internalPart = InternalPart::where('part_number', $part[0])->first();
        if (!$internalPart) {
            return [
                'status' => 'error',
                'message' => 'Part Tidak Sesuai Dengan Sample!'
            ];
        }

        // get line of internal part based on internal part id
        $line = Line::select('name')->where('id', $internalPart->line_id)->first();
        $response = Http::withOptions([
            'verify' => false
        ])->post('https://prdreport.aiia.co.id/prd_body/?action=api_stop_inbound', [
            'line_id' => $line->name
        ]);

        if ($response->successful()) {
            return response()->json(['status' => 'success', 'data' => $response->json()]);
        } else {
            return response()->json(['status' => 'error', 'message' => $response->body()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store2(Request $request)
    {
        $partNumber = $request->partNumber;
        $seri = $request->seri ?? 'noseri';

        // double check to master sample
        $internalPart = InternalPart::where('part_number', $partNumber)->first();
        if (!$internalPart) {
            return [
                'status' => 'error',
                'message' => 'Part Tidak Sesuai Dengan Sample!'
            ];
        }

        // get line of internal part based on internal part id
        $lineProd = Line::select('name')->where('id', $internalPart->line_id)->first();
        // get customer internalPart based on internal internalPart id
        $customerPart = CustomerPart::select('qty_per_kanban')->where('internal_part_id', $internalPart->id)->first();

        if ($seri !== 'noseri') {
            // get kanban_id based on internal part id
            $kanban = Kanban::where('internal_part_id', $internalPart->id)
                ->where('serial_number', $seri)
                ->first();

            if (!$kanban) {
                return [
                    'status' => 'error',
                    'message' => 'Kanban tidak terdaftar!'
                ];
            }

            // Cek jika mengandung kata SUB ASSY dalam variasi yang dimaksud
            if (isset($internalPart->back_number) && strlen($internalPart->back_number) >= 2 && $internalPart->back_number[1] === 'O') {
                // Lewati pengecekan status
            } else {
                if ($kanban->status == 1) {
                    return [
                        'status' => 'kanbanExist',
                        'message' => 'Kanban Sudah di scan!'
                    ];
                }
            }
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

            if ($seri !== 'noseri') {
                // update status in kanbans table
                if (! $kanban->update([
                    'status' => Kanban::STATUS_PRODUCED
                ])) {
                    throw new \RuntimeException('Gagal memperbarui status kanban.');
                }
            }

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
            $datas = [
                'data' => [
                    [
                        'line_id' => $lineProd->name,
                        'prd_dt' => (new DateTime($request->start_time))->format('Y-m-d'),
                        'str_dt' => $request->start_time,
                        'end_dt' => "",
                        'matnr'  => $request->partNumber,
                        'menge' => $customerPart->qty_per_kanban,
                        'crtby'  => auth()->user()->npk,
                    ]
                ]
            ];
            // Kirim ke API external (jika perlu)
            $response = Http::withOptions([
                'verify' => false
            ])->post('https://prdreport.aiia.co.id/prd_body/?action=api_insert_inbound', $datas);

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

    public function direct()
    {
        return view('pages.production.direct');
    }

    public function storePartScan(Request $r)
    {
        $r->validate([
            'line'        => ['required', 'string', 'max:32'],
            'model'       => ['required', 'string', 'max:128'],
            'barcode'     => ['required', 'string'],
            'dandori'     => ['nullable', 'string', 'max:128'],
            'back_number' => ['nullable', 'string', 'max:32'],
        ]);

        $line       = trim($r->line);
        $model      = trim($r->model);
        $barcode    = strtoupper(trim($r->barcode));
        $dandori    = $r->dandori ? trim($r->dandori) : null;
        $backNumber = strtoupper(trim($r->back_number ?? ''));

        $barcodeLength = strlen($barcode);

        $allowedPart22BackNumbers = [
            'SPKV',
            'SPKW',
            'SPKX',
            'SPKY',
            'SPKZ',
            'SPL1',
            'SPL2',
            'SPL3',
            'SPL4',
            'SPL5',
            'SPL6',
            'SPL7',
        ];

        if (!in_array($barcodeLength, [22, 27], true)) {
            return response()->json([
                'status'  => 'invalid',
                'message' => 'Panjang barcode part tidak valid. Hanya menerima 22 atau 27 digit.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi barcode 22 digit
        |--------------------------------------------------------------------------
        */
        if ($barcodeLength === 22) {
            if (!$backNumber) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Back number wajib dikirim untuk scan part 22 digit.',
                ], 422);
            }

            if (!in_array($backNumber, $allowedPart22BackNumbers, true)) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Barcode part 22 digit hanya berlaku untuk model SPKV sampai SPL7.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi barcode 27 digit
        |--------------------------------------------------------------------------
        */
        if ($barcodeLength === 27) {
            if (!$backNumber) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Back number wajib dikirim untuk scan part 27 digit.',
                ], 422);
            }

            $last4           = strtoupper(substr($barcode, -4));
            $prefixBackNumber = substr($backNumber, 0, 2);

            $rules = [
                'KMOU' => 'SP',
                'KMOT' => 'KP',
            ];

            if (
                !isset($rules[$last4]) ||
                $rules[$last4] !== $prefixBackNumber
            ) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Barcode part tidak sesuai dengan back number.',
                ], 422);
            }
        }

        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | Cek duplicate hanya untuk barcode selain 22 digit
        |--------------------------------------------------------------------------
        | Barcode 22 digit boleh discan dan disimpan berulang kali.
        */
        if ($barcodeLength !== 22) {
            $exists = ScannedPart::where('line', $line)
                ->where('barcode', $barcode)
                ->whereDate('scan_date', $today)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'duplicate',
                    'message' => 'Barcode part sudah pernah discan.',
                ]);
            }
        }

        ScannedPart::create([
            'line'          => $line,
            'model'         => $model,
            'dandori_board' => $dandori,
            'barcode'       => $barcode,
            'last4'         => strtoupper(substr($barcode, -4)),
            'scan_date'     => $today,
            'scanned_at'    => now(),
            'kanban_id'     => null,
        ]);

        $actual = ScannedPart::where('line', $line)
            ->where('model', $model)
            ->whereDate('scan_date', $today)
            ->whereNull('kanban_id')
            ->count();

        return response()->json([
            'status' => 'ok',
            'actual' => $actual,
        ]);
    }

    public function assignKanbanToPartScans(Request $r)
    {
        $r->validate([
            'line'     => ['required', 'string', 'max:32'],
            'model'    => ['required', 'string', 'max:128'],
            'internal' => ['required', 'string'],
            'seri'     => ['required', 'string', 'max:10'],
            'limit'    => ['nullable', 'integer', 'min:1'],
        ]);

        $line     = trim($r->line);
        $model    = trim($r->model);
        $internal = trim($r->internal);
        $seri     = trim($r->seri);

        $internalPart = InternalPart::where('part_number', $internal)->first();

        if (!$internalPart) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Internal part tidak ditemukan.'
            ], 422);
        }

        $kanban = Kanban::where('internal_part_id', $internalPart->id)
            ->where('serial_number', $seri)
            ->latest('id')
            ->first();

        if (!$kanban) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'KANBAN tidak ditemukan (internal/seri tidak match).'
            ], 422);
        }

        $today = Carbon::today();

        $baseQuery = ScannedPart::where('line', $line)
            ->where('model', $model)
            ->whereDate('scan_date', $today)
            ->whereNull('kanban_id')
            ->orderBy('scanned_at', 'asc');

        $ids = $r->filled('limit') && (int) $r->limit > 0
            ? $baseQuery->limit((int) $r->limit)->pluck('id')
            : $baseQuery->pluck('id');

        if ($ids->isEmpty()) {
            return response()->json([
                'status'    => 'ok',
                'assigned'  => 0,
                'kanban_id' => $kanban->id,
            ]);
        }

        DB::transaction(function () use ($ids, $kanban) {
            ScannedPart::whereIn('id', $ids)->update([
                'kanban_id' => $kanban->id
            ]);
        });

        return response()->json([
            'status'    => 'ok',
            'assigned'  => $ids->count(),
            'kanban_id' => $kanban->id,
        ]);
    }

    public function getInternalPart($custPart)
    {

        if (!$custPart) {
            return response()->json(['status' => 'error', 'message' => 'part_number parameter is required'], 400);
        }

        $internalPart = CustomerPart::where('part_number', $custPart)->with('internalPart')->first();

        if (!$internalPart) {
            return response()->json(['status' => 'error', 'message' => 'Internal part not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $internalPart]);
    }
}
