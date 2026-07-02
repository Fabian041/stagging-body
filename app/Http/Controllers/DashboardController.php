<?php

namespace App\Http\Controllers;

use App\Exports\MutationMultiSheetExport;
use App\Exports\PartImportTemplateExport;
use App\Imports\ManifestImport;
use App\Imports\PartImport;
use App\Imports\StockImport;
use App\Models\Customer;
use App\Models\InternalPart;
use App\Models\LineStaticSequence;
use App\Models\ProductionPlan;
use App\Models\ScannedPart;
use App\Models\Supplier;
use App\Traits\prodPlanOps;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    use prodPlanOps;

    public function index()
    {
        // Landing dashboard: jangan langsung menampilkan "Body Plant Stock Monitoring"
        return view('pages.dashboard_home');
    }

    /**
     * Dashboard Overview: ringkasan & analisis lintas area (PPIC, Production, PIS, Error).
     */
    public function overview(Request $request)
    {
        [$from, $to] = $this->overviewRange($request);

        return view('pages.dashboard_overview', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    /**
     * Data overview untuk auto-refresh (JSON).
     */
    public function overviewData(Request $request)
    {
        [$from, $to] = $this->overviewRange($request);

        return response()->json($this->buildOverviewData($from, $to) + [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ])->header('Cache-Control', 'private, max-age=30');
    }

    public function overviewAiAnalyst(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:700',
            'history' => 'array|max:8',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:1200',
        ]);

        $apiKey = (string) config('services.groq.key');
        if ($apiKey === '') {
            return response()->json([
                'message' => 'GROQ_API_KEY belum diset di environment.',
            ], 422);
        }

        [$from, $to] = $this->overviewRange($request);
        $overview = $this->buildOverviewData($from, $to);
        $question = trim($validated['question']);
        $context = $this->buildSystemAiContext($overview, $from, $to, $question);
        $history = collect($validated['history'] ?? [])
            ->slice(-4)
            ->map(fn ($item) => [
                'role' => $item['role'],
                'content' => Str::limit(trim((string) $item['content']), 800, ''),
            ])
            ->values()
            ->all();

        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'Kamu adalah BELLA, AI assistant operasional Body Plant.',
                    'Jawab dalam Bahasa Indonesia yang ringkas, natural, dan actionable.',
                    'Gunakan hanya data JSON system_context yang diberikan. Jangan mengarang data yang tidak ada.',
                    'Jika data tidak cukup, sebutkan data mana yang belum tersedia.',
                    'Kamu bisa menganalisis lintas sistem: dashboard, loading list, PDS, pulling, produksi, stok, PIS, receiving, quality, error, dan master data yang tersedia di konteks.',
                    'Jika user meminta data detail yang tidak masuk konteks, minta filter yang lebih spesifik seperti nomor loading list/PDS/back number/customer/tanggal.',
                    'Saat menyebut rekomendasi, jelaskan alasan singkatnya.',
                    'Format jawaban pakai Markdown sederhana: **tebal** untuk judul/label, bullet pakai "-" atau "*". Hindari simbol "+".',
                ]),
            ],
            [
                'role' => 'user',
                'content' => "system_context:\n".$this->encodeAiContext($context),
            ],
        ];

        foreach ($history as $item) {
            $messages[] = $item;
        }
        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        $messages = $this->fitGroqMessages($messages, (int) config('services.groq.max_prompt_tokens', 5000));

        $http = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(25);

        $caBundle = (string) config('services.groq.ca_bundle');
        if ($caBundle !== '' && is_file($caBundle)) {
            $http = $http->withOptions(['verify' => $caBundle]);
        }

        try {
            $response = $http
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => (string) config('services.groq.model', 'llama-3.1-8b-instant'),
                    'messages' => $messages,
                    'temperature' => 0.25,
                    'max_tokens' => 520,
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menghubungi Groq: '.$e->getMessage(),
            ], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => data_get($response->json(), 'error.message', 'Groq mengembalikan error.'),
            ], 502);
        }

        $answer = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($answer === '') {
            $answer = 'Groq tidak mengembalikan jawaban. Coba tanyakan ulang dengan konteks yang lebih spesifik.';
        }

        return response()->json([
            'answer' => $answer,
            'model' => (string) data_get($response->json(), 'model', config('services.groq.model')),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'actions' => $this->buildAiActionLinks($question),
        ]);
    }

    /**
     * Drill-down satu slot heatmap jam tersibuk (hari-of-week × jam) → rincian per tanggal. (#4)
     */
    public function overviewSlot(Request $request)
    {
        [$from, $to] = $this->overviewRange($request);

        $dow = (int) $request->input('dow');   // MySQL DAYOFWEEK: 1=Minggu .. 7=Sabtu
        $hour = (int) $request->input('hour');
        $mode = (string) $request->input('mode', 'all');

        if ($dow < 1 || $dow > 7 || $hour < 0 || $hour > 23) {
            return response()->json(['detail' => []]);
        }

        $types = $mode === 'pulling' ? ['checkout'] : ($mode === 'produksi' ? ['supply'] : ['checkout', 'supply']);

        $rows = DB::table('mutations')
            ->whereIn('type', $types)
            ->whereBetween('date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->whereRaw('DAYOFWEEK(date) = ?', [$dow])
            ->whereRaw('HOUR(date) = ?', [$hour])
            ->selectRaw('DATE(date) as d, type, COUNT(*) as c')
            ->groupBy('d', 'type')
            ->orderBy('d')
            ->get();

        $byDate = [];
        foreach ($rows as $r) {
            $d = (string) $r->d;
            if (! isset($byDate[$d])) {
                $byDate[$d] = ['date' => $d, 'pulling' => 0, 'produksi' => 0];
            }
            if ((string) $r->type === 'checkout') {
                $byDate[$d]['pulling'] = (int) $r->c;
            } else {
                $byDate[$d]['produksi'] = (int) $r->c;
            }
        }

        return response()->json([
            'dow' => $dow,
            'hour' => $hour,
            'mode' => $mode,
            'detail' => array_values($byDate),
        ])->header('Cache-Control', 'private, max-age=30');
    }

    /** Rentang tanggal overview: default 7 hari terakhir, maksimal 31 hari. */
    private function overviewRange(Request $request): array
    {
        try {
            $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();
        } catch (\Throwable $e) {
            $to = Carbon::today();
        }
        try {
            $from = $request->filled('from') ? Carbon::parse($request->input('from')) : $to->copy()->subDays(6);
        } catch (\Throwable $e) {
            $from = $to->copy()->subDays(6);
        }

        $from = $from->startOfDay();
        $to = $to->startOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->lt($to->copy()->subDays(30))) {
            $from = $to->copy()->subDays(30); // batasi maksimal 31 hari
        }

        return [$from, $to];
    }

    private function buildOverviewData(Carbon $from, Carbon $to): array
    {
        $cacheKey = 'dashboard_overview_v5_'.$from->toDateString().'_'.$to->toDateString();

        return Cache::remember($cacheKey, 60, function () use ($from, $to) {
            $start = $from->copy()->startOfDay();
            $end = $to->copy()->endOfDay();
            $lenDays = $from->diffInDays($to) + 1;
            $prevFrom = $from->copy()->subDays($lenDays)->startOfDay();
            $currentFromKey = $from->toDateString();
            $delta = fn ($cur, $prev) => $prev > 0 ? (int) round(($cur - $prev) / $prev * 100) : ($cur > 0 ? 100 : 0);

            // ===== PPIC: loading list delivery dalam rentang =====
            $ll = \App\Models\LoadingList::query()
                ->withSum('detail as total_kanban', 'kanban_qty')
                ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                ->with('customer:id,name')
                ->whereBetween('delivery_date', [$start, $end])
                ->get();

            $isComplete = fn ($l) => (int) ($l->total_kanban ?? 0) > 0
                && (int) ($l->actual_kanban ?? 0) >= (int) $l->total_kanban;

            $prepTotal = $ll->count();
            $prepComplete = $ll->filter($isComplete)->count();
            $prepPct = $prepTotal > 0 ? (int) round($prepComplete / $prepTotal * 100) : 0;

            $prepByCycle = $ll->groupBy(fn ($l) => $l->cycle ?: '-')
                ->map(function ($items, $cycle) use ($isComplete) {
                    $total = $items->count();
                    $complete = $items->filter($isComplete)->count();

                    return [
                        'cycle' => (string) $cycle,
                        'total' => $total,
                        'complete' => $complete,
                        'pct' => $total > 0 ? (int) round($complete / $total * 100) : 0,
                    ];
                })->sortBy('cycle')->values();

            // Satu scan terindeks untuk KPI, tren harian, dan pembanding periode sebelumnya.
            $mutationDailyRows = DB::table('mutations')
                ->whereIn('type', ['checkout', 'supply'])
                ->whereBetween('date', [$prevFrom, $end])
                ->selectRaw('DATE(date) as d, type, COUNT(*) as c')
                ->groupBy('d', 'type')
                ->get();

            $pullingByDay = collect();
            $supplyByDay = collect();
            $prevKanban = 0;
            $prevProduksi = 0;
            foreach ($mutationDailyRows as $row) {
                $count = (int) $row->c;
                if ($row->d >= $currentFromKey) {
                    if ($row->type === 'checkout') {
                        $pullingByDay[$row->d] = $count;
                    } elseif ($row->type === 'supply') {
                        $supplyByDay[$row->d] = $count;
                    }
                } elseif ($row->type === 'checkout') {
                    $prevKanban += $count;
                } elseif ($row->type === 'supply') {
                    $prevProduksi += $count;
                }
            }
            $kanbanScanned = (int) $pullingByDay->sum();
            $produksi = (int) $supplyByDay->sum();

            $pullingDaily = DB::table('pullings')
                ->whereBetween('pulling_date', [$prevFrom, $end])
                ->selectRaw('DATE(pulling_date) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd');
            $pullingDone = (int) $pullingDaily
                ->filter(fn ($count, $date) => $date >= $currentFromKey)
                ->sum();
            $prevPulling = (int) $pullingDaily
                ->filter(fn ($count, $date) => $date < $currentFromKey)
                ->sum();

            $pisByDay = DB::table('pis_scan_logs')
                ->whereBetween('scan_time', [$start, $end])
                ->selectRaw('DATE(scan_time) as d, COUNT(*) as c')
                ->groupBy('d')
                ->pluck('c', 'd');
            $pisScan = (int) $pisByDay->sum();

            // ===== Stok (snapshot, tidak tergantung tanggal) =====
            $stockRows = DB::table('internal_parts')
                ->leftJoin('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
                ->selectRaw('COALESCE(production_stocks.current_stock, 0) as cur, COALESCE(internal_parts.standard_stock, 0) as std')
                ->get();
            $totalPart = $stockRows->count();
            $stockUnder = $stockRows->filter(fn ($r) => $r->cur < $r->std)->count();
            $stockLow = $stockRows->filter(fn ($r) => $r->cur == $r->std)->count();
            $stockIn = $stockRows->filter(fn ($r) => $r->cur > $r->std)->count();

            $topUnder = DB::table('internal_parts')
                ->leftJoin('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
                ->leftJoin('lines', 'lines.id', '=', 'internal_parts.line_id')
                ->selectRaw('internal_parts.back_number, lines.name as line,
                    COALESCE(production_stocks.current_stock, 0) as cur,
                    COALESCE(internal_parts.standard_stock, 0) as std,
                    (COALESCE(internal_parts.standard_stock, 0) - COALESCE(production_stocks.current_stock, 0)) as gap')
                ->whereRaw('COALESCE(production_stocks.current_stock, 0) < COALESCE(internal_parts.standard_stock, 0)')
                ->orderByRaw('gap DESC')->limit(8)->get();

            // ===== PIS =====
            $pisDone = DB::table('pis_scan_details')
                ->where('target_qty', '>', 0)
                ->whereColumn('scanned_qty', '>=', 'target_qty')
                ->count();

            // ===== Error dalam rentang =====
            $errorDailyRows = DB::table('error_logs')
                ->whereBetween('date', [$prevFrom, $end])
                ->selectRaw('DATE(date) as d, area, COUNT(*) as c')
                ->groupBy('d', 'area')
                ->get();
            $errPeriod = 0;
            $prevErr = 0;
            $errorAreaCounts = collect();
            $errorAreaCountsPrev = collect();
            $errByDay = collect();
            foreach ($errorDailyRows as $row) {
                $count = (int) $row->c;
                $area = $row->area ?: '-';
                if ($row->d >= $currentFromKey) {
                    $errPeriod += $count;
                    $errorAreaCounts[$area] = (int) ($errorAreaCounts[$area] ?? 0) + $count;
                    $errByDay[$row->d] = (int) ($errByDay[$row->d] ?? 0) + $count;
                } else {
                    $prevErr += $count;
                    $errorAreaCountsPrev[$area] = (int) ($errorAreaCountsPrev[$area] ?? 0) + $count;
                }
            }
            $errByArea = $errorAreaCounts
                ->sortDesc()
                ->take(6)
                ->map(fn ($count, $area) => (object) [
                    'area' => $area,
                    'c' => $count,
                    'delta' => $delta($count, (int) ($errorAreaCountsPrev[$area] ?? 0)),
                ])
                ->values();
            $recentErrors = DB::table('error_logs')
                ->orderByDesc('date')->limit(8)
                ->get(['area', 'message', 'expected', 'scanned', 'date'])
                ->map(function ($e) {
                    return (object) [
                        'area' => $e->area,
                        'message' => $e->message,
                        'expected' => $e->expected,
                        'scanned' => $e->scanned,
                        'date' => $e->date ? Carbon::parse($e->date)->format('d/m H:i') : '-',
                    ];
                });

            // ===== Tren per hari (rentang) =====
            $trend = collect();
            for ($c = $from->copy(); $c->lte($to); $c->addDay()) {
                $key = $c->toDateString();
                $trend->push([
                    'label' => $c->format('d/m'),
                    'pulling' => (int) ($pullingByDay[$key] ?? 0),
                    'produksi' => (int) ($supplyByDay[$key] ?? 0),
                    'pis' => (int) ($pisByDay[$key] ?? 0),
                    'err' => (int) ($errByDay[$key] ?? 0),
                ]);
            }

            // ===== Perbandingan periode sebelumnya (#1) =====

            // ===== Error rate (#4) =====
            $totalScans = $kanbanScanned + $produksi + $pisScan;
            $errRate = $totalScans > 0 ? round($errPeriod / $totalScans * 100, 1) : 0;

            // ===== Produksi per line (#2) =====
            $prodByLine = DB::table('mutations')
                ->join('internal_parts', 'internal_parts.id', '=', 'mutations.internal_part_id')
                ->join('lines', 'lines.id', '=', 'internal_parts.line_id')
                ->where('mutations.type', 'supply')
                ->whereBetween('mutations.date', [$start, $end])
                ->selectRaw('lines.name as line, COUNT(*) as c')
                ->groupBy('lines.name')->orderByDesc('c')->limit(8)->get();

            // ===== Preparation per customer (#2) =====
            $prepByCustomer = $ll->groupBy(fn ($l) => optional($l->customer)->name ?: '-')
                ->map(function ($items, $cust) use ($isComplete) {
                    $total = $items->count();
                    $complete = $items->filter($isComplete)->count();

                    return [
                        'customer' => (string) $cust, 'total' => $total, 'complete' => $complete,
                        'pct' => $total > 0 ? (int) round($complete / $total * 100) : 0,
                    ];
                })->sortByDesc('total')->take(8)->values();

            // ===== Jam tersibuk: heatmap hari x jam (#5) =====
            $dowLabels = [1 => 'Min', 2 => 'Sen', 3 => 'Sel', 4 => 'Rab', 5 => 'Kam', 6 => 'Jum', 7 => 'Sab'];
            $hm = DB::table('mutations')->whereIn('type', ['checkout', 'supply'])
                ->whereBetween('date', [$start, $end])
                ->selectRaw('type, DAYOFWEEK(date) as dow, HOUR(date) as h, COUNT(*) as c')
                ->groupBy('type', 'dow', 'h')->get();
            $matrix = ['checkout' => [], 'supply' => []];
            $heatmapTotals = ['checkout' => 0, 'supply' => 0];
            $heatmapPeak = [
                'checkout' => ['label' => '-', 'count' => 0],
                'supply' => ['label' => '-', 'count' => 0],
            ];
            foreach ($hm as $r) {
                $type = (string) $r->type;
                if (! isset($matrix[$type])) {
                    continue;
                }

                $dow = (int) $r->dow;
                $hour = (int) $r->h;
                $count = (int) $r->c;
                $matrix[$type][$dow][$hour] = $count;
                $heatmapTotals[$type] += $count;
                if ($count > $heatmapPeak[$type]['count']) {
                    $heatmapPeak[$type] = [
                        'label' => ($dowLabels[$dow] ?? '-').' '.sprintf('%02d:00', $hour),
                        'count' => $count,
                    ];
                }
            }
            // urutan dibalik agar Senin tampil paling atas (ApexCharts render seri pertama di bawah)
            $heatmap = collect([1, 7, 6, 5, 4, 3, 2])->flatMap(function ($dow) use ($matrix, $dowLabels) {
                return collect([
                    ['type' => 'checkout', 'label' => 'Pulling'],
                    ['type' => 'supply', 'label' => 'Produksi'],
                ])->map(function ($item) use ($dow, $matrix, $dowLabels) {
                    $type = $item['type'];
                    $row = [];
                    for ($h = 0; $h < 24; $h++) {
                        $row[] = ['x' => sprintf('%02d', $h), 'y' => (int) ($matrix[$type][$dow][$h] ?? 0)];
                    }

                    return ['name' => $dowLabels[$dow].' '.$item['label'], 'data' => $row];
                });
            })->values();
            $heatmapSummary = [
                'pulling' => $heatmapTotals['checkout'],
                'produksi' => $heatmapTotals['supply'],
                'peakPulling' => $heatmapPeak['checkout'],
                'peakProduksi' => $heatmapPeak['supply'],
            ];

            // ===== Delivery berisiko (#6): hari ini + besok, belum lengkap, + deadline master cycle =====
            // Lookup master cycle per (customer_id, cycle) — cocokkan exact lalu by-angka.
            $mcRows = \App\Models\MasterCycle::get(['customer_id', 'cycle_name', 'truck_time', 'prep_end_time', 'prep_day_offset', 'prep_end_day_offset']);
            $normCycle = fn ($s) => strtolower(trim((string) $s));
            $digitCycle = function ($s) {
                preg_match('/\d+/', (string) $s, $m);

                return $m[0] ?? '';
            };
            $mcExact = [];
            $mcDigit = [];
            foreach ($mcRows as $row) {
                $mcExact[$row->customer_id.'|'.$normCycle($row->cycle_name)] = $row;
                $dg = $digitCycle($row->cycle_name);
                if ($dg !== '') {
                    $mcDigit[$row->customer_id.'|'.$dg] = $row;
                }
            }

            $now = now();
            $atRisk = \App\Models\LoadingList::query()
                ->withSum('detail as total_kanban', 'kanban_qty')
                ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                ->with('customer:id,name')
                ->whereBetween('delivery_date', [Carbon::today()->startOfDay(), Carbon::tomorrow()->endOfDay()])
                ->get()
                ->filter(fn ($l) => (int) ($l->total_kanban ?? 0) > 0 && (int) ($l->actual_kanban ?? 0) < (int) $l->total_kanban)
                ->map(function ($l) use ($mcExact, $mcDigit, $normCycle, $digitCycle, $now) {
                    $m = $mcExact[$l->customer_id.'|'.$normCycle($l->cycle)]
                        ?? ($mcDigit[$l->customer_id.'|'.$digitCycle($l->cycle)] ?? null);

                    $deadline = null;
                    $remaining = null;
                    $urgency = 'unknown';
                    $truck = null;
                    if ($m) {
                        $truck = $m->truck_time ? substr($m->truck_time, 0, 5) : null;
                        if ($m->prep_end_time && $l->delivery_date) {
                            $endOffset = $m->prep_end_day_offset !== null
                                ? (int) $m->prep_end_day_offset
                                : (int) ($m->prep_day_offset ?? 0);
                            $dl = Carbon::parse($l->delivery_date)->addDays($endOffset)->setTimeFromTimeString($m->prep_end_time);
                            $deadline = $dl->format('d M, H:i');
                            $remaining = (int) floor(($dl->getTimestamp() - $now->getTimestamp()) / 60); // menit; negatif = lewat
                            $urgency = $remaining < 0 ? 'overdue' : ($remaining <= 60 ? 'urgent' : ($remaining <= 180 ? 'soon' : 'ok'));
                        }
                    }

                    return (object) [
                        'id' => $l->id,
                        'number' => $l->number,
                        'customer' => optional($l->customer)->name,
                        'cycle' => $l->cycle,
                        'deadline' => $deadline,
                        'truck' => $truck,
                        'remaining' => $remaining,
                        'urgency' => $urgency,
                        'sortKey' => $remaining ?? 999999,
                        'actual' => (int) ($l->actual_kanban ?? 0),
                        'total' => (int) ($l->total_kanban ?? 0),
                        'pct' => (int) ($l->total_kanban ?? 0) > 0 ? (int) round(($l->actual_kanban ?? 0) / $l->total_kanban * 100) : 0,
                    ];
                })
                ->sortBy('sortKey')
                ->take(10)
                ->values();

            return [
                'kpi' => [
                    'prepTotal' => $prepTotal, 'prepComplete' => $prepComplete, 'prepPct' => $prepPct,
                    'kanbanScanned' => $kanbanScanned, 'pullingDone' => $pullingDone,
                    'produksi' => $produksi, 'totalPart' => $totalPart, 'underStock' => $stockUnder,
                    'pisScan' => $pisScan, 'pisDone' => $pisDone, 'errPeriod' => $errPeriod,
                ],
                'delta' => [
                    'kanban' => $delta($kanbanScanned, $prevKanban),
                    'pulling' => $delta($pullingDone, $prevPulling),
                    'produksi' => $delta($produksi, $prevProduksi),
                    'err' => $delta($errPeriod, $prevErr),
                ],
                'errRate' => $errRate,
                'stock' => ['under' => $stockUnder, 'low' => $stockLow, 'in' => $stockIn],
                'trend' => $trend->values(),
                'prepByCycle' => $prepByCycle,
                'prepByCustomer' => $prepByCustomer,
                'prodByLine' => $prodByLine,
                'heatmap' => $heatmap,
                'heatmapSummary' => $heatmapSummary,
                'topUnder' => $topUnder,
                'errByArea' => $errByArea,
                'recentErrors' => $recentErrors,
                'atRisk' => $atRisk,
            ];
        });
    }

    private function buildOverviewAiContext(array $data, Carbon $from, Carbon $to): array
    {
        $pick = function ($items, array $fields, int $limit = 8): array {
            return collect($items ?? [])
                ->take($limit)
                ->map(function ($item) use ($fields) {
                    $row = [];
                    foreach ($fields as $label => $path) {
                        $row[$label] = data_get($item, $path);
                    }

                    return $row;
                })
                ->values()
                ->all();
        };

        return [
            'periode' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'kpi' => $data['kpi'] ?? [],
            'delta_vs_periode_sebelumnya' => $data['delta'] ?? [],
            'error_rate_percent' => $data['errRate'] ?? 0,
            'stock_summary' => $data['stock'] ?? [],
            'heatmap_summary' => $data['heatmapSummary'] ?? [],
            'trend_harian' => collect($data['trend'] ?? [])->take(31)->values()->all(),
            'delivery_berisiko' => $pick($data['atRisk'] ?? [], [
                'loading_list_id' => 'id',
                'loading_list' => 'number',
                'customer' => 'customer',
                'cycle' => 'cycle',
                'deadline_preparation' => 'deadline',
                'eta_truck' => 'truck',
                'remaining_minutes' => 'remaining',
                'urgency' => 'urgency',
                'actual' => 'actual',
                'target' => 'total',
                'progress_pct' => 'pct',
            ], 10),
            'preparation_per_customer' => $pick($data['prepByCustomer'] ?? [], [
                'customer' => 'customer',
                'total' => 'total',
                'complete' => 'complete',
                'pct' => 'pct',
            ], 8),
            'preparation_per_cycle' => $pick($data['prepByCycle'] ?? [], [
                'cycle' => 'cycle',
                'total' => 'total',
                'complete' => 'complete',
                'pct' => 'pct',
            ], 10),
            'produksi_per_line' => $pick($data['prodByLine'] ?? [], [
                'line' => 'line',
                'count' => 'c',
            ], 8),
            'part_paling_kurang_stok' => $pick($data['topUnder'] ?? [], [
                'back_number' => 'back_number',
                'line' => 'line',
                'current_stock' => 'cur',
                'standard_stock' => 'std',
                'gap' => 'gap',
            ], 8),
            'error_per_area' => $pick($data['errByArea'] ?? [], [
                'area' => 'area',
                'count' => 'c',
                'delta_percent' => 'delta',
            ], 6),
            'error_terbaru' => $pick($data['recentErrors'] ?? [], [
                'area' => 'area',
                'message' => 'message',
                'expected' => 'expected',
                'scanned' => 'scanned',
                'date' => 'date',
            ], 6),
        ];
    }

    private function buildSystemAiContext(array $overview, Carbon $from, Carbon $to, string $question): array
    {
        $start = $from->copy()->startOfDay()->toDateTimeString();
        $end = $to->copy()->endOfDay()->toDateTimeString();
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();
        $modules = $this->resolveAiModules($question);

        $context = [
            'scope' => [
                'periode_filter' => ['from' => $fromDate, 'to' => $toDate],
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'mode' => 'lintas sistem via query allowlist, bukan akses SQL bebas dari chat',
                'catatan' => 'Ringkasan memakai periode dashboard; modul detail dimuat sesuai topik pertanyaan; search_hits mencari keyword lintas data utama.',
                'modules_loaded' => $modules,
            ],
            'overview' => $this->buildOverviewAiContext($overview, $from, $to),
            'modules' => [],
            'search_hits' => $this->buildSystemAiSearchHits($question),
        ];

        if (in_array('loading_list_pds', $modules, true) && $this->aiTableExists('loading_lists')) {
            $ll = DB::table('loading_lists as ll')
                ->leftJoin('customers as c', 'c.id', '=', 'll.customer_id')
                ->whereBetween('ll.delivery_date', [$start, $end]);

            $context['modules']['loading_list_pds'] = [
                'summary_periode' => [
                    'total' => (clone $ll)->count(),
                    'total_pds' => (clone $ll)->distinct('ll.pds_number')->count('ll.pds_number'),
                    'truck_status' => $this->aiColumnExists('loading_lists', 'truck_status')
                        ? $this->aiRows((clone $ll)->select('ll.truck_status', DB::raw('COUNT(*) as total'))->groupBy('ll.truck_status')->get())
                        : [],
                ],
                'per_customer' => $this->aiRows((clone $ll)
                    ->select(DB::raw('COALESCE(c.name, "Unknown") as customer'), DB::raw('COUNT(*) as total'))
                    ->groupBy('c.name')
                    ->orderByDesc('total')
                    ->limit(8)
                    ->get()),
                'terbaru' => $this->aiRows((clone $ll)
                    ->select('ll.number as loading_list', 'll.pds_number', 'll.cycle', 'll.delivery_date', 'll.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('ll.delivery_date')
                    ->limit(8)
                    ->get()),
            ];

            if ($this->aiTableExists('loading_list_details')) {
                $context['modules']['loading_list_pds']['qty_periode'] = (array) DB::table('loading_lists as ll')
                    ->join('loading_list_details as d', 'd.loading_list_id', '=', 'll.id')
                    ->whereBetween('ll.delivery_date', [$start, $end])
                    ->selectRaw('SUM(COALESCE(d.kanban_qty, 0)) as target_kanban, SUM(COALESCE(d.actual_kanban_qty, 0)) as actual_kanban, SUM(COALESCE(d.total_qty, 0)) as target_qty')
                    ->first();
            }
        }

        if (in_array('pulling', $modules, true) && $this->aiTableExists('pullings')) {
            $pullings = DB::table('pullings as p')
                ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
                ->whereBetween('p.pulling_date', [$start, $end]);

            $context['modules']['pulling'] = [
                'summary_periode' => [
                    'total' => (clone $pullings)->count(),
                    'total_pds' => (clone $pullings)->distinct('p.pds_number')->count('p.pds_number'),
                    'total_loading_list' => (clone $pullings)->distinct('p.loading_list')->count('p.loading_list'),
                ],
                'per_customer' => $this->aiRows((clone $pullings)
                    ->select(DB::raw('COALESCE(c.name, "Unknown") as customer'), DB::raw('COUNT(*) as total'))
                    ->groupBy('c.name')
                    ->orderByDesc('total')
                    ->limit(8)
                    ->get()),
                'terbaru' => $this->aiRows((clone $pullings)
                    ->select('p.loading_list', 'p.pds_number', 'p.cycle', 'p.pulling_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('p.pulling_date')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('production_plan', $modules, true) && $this->aiTableExists('production_plans')) {
            $plans = DB::table('production_plans as pp')
                ->whereBetween('pp.plan_date', [$fromDate, $toDate]);

            $context['modules']['production_plan'] = [
                'summary_periode' => (array) (clone $plans)
                    ->selectRaw('COUNT(*) as total_plan, SUM(order_qty) as order_qty, SUM(direct_pulling_qty) as direct_pulling_qty, SUM(stock_chute_qty) as stock_chute_qty')
                    ->first(),
                'per_line' => $this->aiRows((clone $plans)
                    ->select('pp.line', DB::raw('COUNT(*) as total_plan'), DB::raw('SUM(pp.order_qty) as order_qty'), DB::raw('SUM(pp.direct_pulling_qty) as direct_pulling_qty'))
                    ->groupBy('pp.line')
                    ->orderByDesc('order_qty')
                    ->limit(10)
                    ->get()),
                'plan_terbaru' => $this->aiRows((clone $plans)
                    ->select('pp.plan_date', 'pp.line', 'pp.customer', 'pp.dock', 'pp.cycle', 'pp.back_no', 'pp.order_qty', 'pp.direct_pulling_qty', 'pp.delivery_time', 'pp.balance_time')
                    ->orderByDesc('pp.plan_date')
                    ->orderBy('pp.delivery_time')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('production_actual_scan', $modules, true) && $this->aiTableExists('part_scans')) {
            $scans = DB::table('part_scans as ps')
                ->whereBetween('ps.scanned_at', [$start, $end]);

            $context['modules']['production_actual_scan'] = [
                'summary_periode' => [
                    'total_scan' => (clone $scans)->count(),
                    'unique_kanban' => (clone $scans)->distinct('ps.kanban_id')->count('ps.kanban_id'),
                ],
                'per_line_model' => $this->aiRows((clone $scans)
                    ->select('ps.line', 'ps.model', DB::raw('COUNT(*) as total_scan'))
                    ->groupBy('ps.line', 'ps.model')
                    ->orderByDesc('total_scan')
                    ->limit(10)
                    ->get()),
                'scan_terbaru' => $this->aiRows((clone $scans)
                    ->select('ps.line', 'ps.model', 'ps.dandori_board', 'ps.last4', 'ps.scanned_at')
                    ->orderByDesc('ps.scanned_at')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('stock', $modules, true) && $this->aiTableExists('internal_parts') && $this->aiTableExists('production_stocks') && $this->aiColumnExists('internal_parts', 'standard_stock')) {
            $stock = DB::table('internal_parts as ip')
                ->leftJoin('lines as l', 'l.id', '=', 'ip.line_id')
                ->leftJoin('production_stocks as ps', 'ps.internal_part_id', '=', 'ip.id');

            $context['modules']['stock'] = [
                'summary_latest' => (array) (clone $stock)
                    ->selectRaw('COUNT(ip.id) as total_part, SUM(CASE WHEN COALESCE(ps.current_stock, 0) < COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as under_stock, SUM(CASE WHEN COALESCE(ps.current_stock, 0) = COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as low_stock, SUM(CASE WHEN COALESCE(ps.current_stock, 0) > COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as in_stock')
                    ->first(),
                'understock_teratas' => $this->aiRows((clone $stock)
                    ->select('ip.part_number', 'ip.back_number', 'ip.part_name', DB::raw('COALESCE(l.name, "-") as line'), DB::raw('COALESCE(ps.current_stock, 0) as current_stock'), 'ip.standard_stock', DB::raw('(COALESCE(ip.standard_stock, 0) - COALESCE(ps.current_stock, 0)) as gap'))
                    ->whereRaw('COALESCE(ps.current_stock, 0) < COALESCE(ip.standard_stock, 0)')
                    ->orderByDesc('gap')
                    ->limit(10)
                    ->get()),
            ];
        }

        if (in_array('pis', $modules, true) && $this->aiTableExists('pis_scans')) {
            $pis = DB::table('pis_scans as ps')
                ->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id')
                ->whereBetween('ps.delivery_date', [$start, $end]);

            $context['modules']['pis'] = [
                'summary_periode' => ['total_scan' => (clone $pis)->count()],
                'terbaru' => $this->aiRows((clone $pis)
                    ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'ps.delivery_date', 'ps.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('ps.delivery_date')
                    ->limit(8)
                    ->get()),
            ];

            if ($this->aiTableExists('pis_scan_details')) {
                $context['modules']['pis']['qty_periode'] = (array) DB::table('pis_scans as ps')
                    ->join('pis_scan_details as d', 'd.pis_scan_id', '=', 'ps.id')
                    ->whereBetween('ps.delivery_date', [$start, $end])
                    ->selectRaw('SUM(COALESCE(d.target_qty, 0)) as target_qty, SUM(COALESCE(d.scanned_qty, 0)) as scanned_qty, SUM(COALESCE(d.remaining_qty, 0)) as remaining_qty')
                    ->first();
            }
        }

        if (in_array('receiving', $modules, true) && $this->aiTableExists('receiving_logs')) {
            $receiving = DB::table('receiving_logs as rl')
                ->whereBetween('rl.expected_time', [$start, $end]);

            $context['modules']['receiving'] = [
                'summary_periode' => [
                    'total_log' => (clone $receiving)->count(),
                    'per_status' => $this->aiRows((clone $receiving)->select('rl.status', DB::raw('COUNT(*) as total'))->groupBy('rl.status')->orderBy('rl.status')->get()),
                ],
                'log_terbaru' => $this->aiRows((clone $receiving)
                    ->select('rl.pick_list', 'rl.supplier_code', 'rl.expected_time', 'rl.status', 'rl.notified_at')
                    ->orderByDesc('rl.expected_time')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('quality', $modules, true) && $this->aiTableExists('quality_inspections')) {
            $quality = DB::table('quality_inspections as qi')
                ->whereBetween('qi.checked_at', [$start, $end]);

            $context['modules']['quality'] = [
                'summary_periode' => [
                    'total_inspection' => (clone $quality)->count(),
                    'per_judgement' => $this->aiRows((clone $quality)->select('qi.judgement', DB::raw('COUNT(*) as total'))->groupBy('qi.judgement')->orderByDesc('total')->get()),
                    'per_approval' => $this->aiRows((clone $quality)->select('qi.approval_status', DB::raw('COUNT(*) as total'))->groupBy('qi.approval_status')->orderByDesc('total')->get()),
                ],
                'not_ok_terbaru' => $this->aiRows((clone $quality)
                    ->select('qi.kanban_code', 'qi.check_type', 'qi.line', 'qi.shift', 'qi.pic', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.judgement', 'qi.approval_status', 'qi.checked_at')
                    ->where('qi.judgement', '<>', 'OK')
                    ->orderByDesc('qi.checked_at')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('error_log', $modules, true) && $this->aiTableExists('error_logs')) {
            $errors = DB::table('error_logs as el')
                ->whereBetween('el.date', [$start, $end]);

            $context['modules']['error_log'] = [
                'summary_periode' => [
                    'total_error' => (clone $errors)->count(),
                    'per_area' => $this->aiRows((clone $errors)->select('el.area', DB::raw('COUNT(*) as total'))->groupBy('el.area')->orderByDesc('total')->limit(8)->get()),
                ],
                'terbaru' => $this->aiRows((clone $errors)
                    ->select('el.area', 'el.message', 'el.expected', 'el.scanned', 'el.date')
                    ->orderByDesc('el.date')
                    ->limit(8)
                    ->get()),
            ];
        }

        if (in_array('mutation_stock_flow', $modules, true) && $this->aiTableExists('mutations')) {
            $mutations = DB::table('mutations as m')
                ->whereBetween('m.date', [$start, $end]);

            $context['modules']['mutation_stock_flow'] = [
                'summary_periode' => [
                    'total_mutation' => (clone $mutations)->count(),
                    'per_type' => $this->aiRows((clone $mutations)->select('m.type', DB::raw('COUNT(*) as total'), DB::raw('SUM(m.qty) as qty'))->groupBy('m.type')->orderByDesc('qty')->get()),
                ],
            ];
        }

        return $context;
    }

    private function resolveAiModules(string $question): array
    {
        $q = strtolower($question);
        $map = [
            'loading_list_pds' => ['loading list', 'loading-list', 'pds', 'delivery', 'shipping', 'truck', 'prep', 'ppic', 'll '],
            'pulling' => ['pulling', 'pull '],
            'production_plan' => ['production plan', 'plan produksi', 'order qty', 'dn number', 'balance time'],
            'production_actual_scan' => ['part scan', 'kanban', 'dandori', 'scan produksi'],
            'stock' => ['stock', 'stok', 'understock', 'back number', 'back no', 'part number'],
            'pis' => ['pis', 'pis scan'],
            'receiving' => ['receiving', 'receive', 'supplier', 'pick list'],
            'quality' => ['quality', 'inspeksi', 'judgement', 'approval', 'not ok', 'ng '],
            'error_log' => ['error', 'gagal', 'salah scan'],
            'mutation_stock_flow' => ['mutation', 'mutasi', 'checkout', 'supply'],
        ];

        $modules = [];
        foreach ($map as $module => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($q, $keyword)) {
                    $modules[] = $module;
                    break;
                }
            }
        }

        if ($modules === []) {
            return ['stock', 'error_log'];
        }

        return array_values(array_unique($modules));
    }

    private function buildAiActionLinks(string $question): array
    {
        $catalog = [
            'loading_list_pds' => ['label' => 'Loading List', 'url' => route('loadingList.index')],
            'pulling' => ['label' => 'Pulling', 'url' => route('pulling.index')],
            'production_plan' => ['label' => 'Production Plan', 'url' => route('dashboard.prodPlan')],
            'production_actual_scan' => ['label' => 'Production', 'url' => route('production.index')],
            'stock' => ['label' => 'Production Stock', 'url' => route('dashboard.productionStock')],
            'pis' => ['label' => 'PIS Scan', 'url' => route('pis.scanList')],
            'receiving' => ['label' => 'Receiving', 'url' => route('dashboard.receiving')],
            'quality' => ['label' => 'Quality', 'url' => route('quality.index')],
            'error_log' => ['label' => 'Error Log', 'url' => route('error.log')],
            'mutation_stock_flow' => ['label' => 'Progress Pulling', 'url' => route('progressPulling.index')],
        ];

        $actions = [];
        foreach ($this->resolveAiModules($question) as $module) {
            if (isset($catalog[$module])) {
                $actions[$catalog[$module]['url']] = $catalog[$module];
            }
        }

        if ($actions === []) {
            $actions[route('dashboard.overview')] = [
                'label' => 'Dashboard Overview',
                'url' => route('dashboard.overview'),
            ];
        }

        return array_values($actions);
    }

    private function encodeAiContext(array $context): string
    {
        return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}';
    }

    private function compactAiContextForGroq(array $context, int $maxChars): array
    {
        $fits = fn (array $ctx) => strlen($this->encodeAiContext($ctx)) <= $maxChars;

        if ($fits($context)) {
            return $context;
        }

        unset($context['modules']);
        if ($fits($context)) {
            $context['scope']['catatan'] = ($context['scope']['catatan'] ?? '').' Modul detail di-skip agar muat limit token Groq.';

            return $context;
        }

        if (isset($context['search_hits']['matches']) && is_array($context['search_hits']['matches'])) {
            foreach ($context['search_hits']['matches'] as $key => $rows) {
                $context['search_hits']['matches'][$key] = array_slice($rows, 0, 3);
            }
        }
        if ($fits($context)) {
            return $context;
        }

        if (isset($context['overview']['trend_harian']) && is_array($context['overview']['trend_harian'])) {
            $context['overview']['trend_harian'] = array_slice($context['overview']['trend_harian'], -14);
        }
        foreach (['delivery_berisiko', 'preparation_per_customer', 'preparation_per_cycle', 'produksi_per_line', 'part_paling_kurang_stok', 'error_per_area', 'error_terbaru'] as $key) {
            if (isset($context['overview'][$key]) && is_array($context['overview'][$key])) {
                $context['overview'][$key] = array_slice($context['overview'][$key], 0, 4);
            }
        }
        if ($fits($context)) {
            return $context;
        }

        $context['overview'] = [
            'periode' => $context['overview']['periode'] ?? [],
            'kpi' => $context['overview']['kpi'] ?? [],
            'delta_vs_periode_sebelumnya' => $context['overview']['delta_vs_periode_sebelumnya'] ?? [],
            'stock_summary' => $context['overview']['stock_summary'] ?? [],
            'error_rate_percent' => $context['overview']['error_rate_percent'] ?? 0,
        ];
        $context['scope']['catatan'] = 'Konteks dipadatkan karena melebihi limit token Groq. Minta filter lebih spesifik jika butuh detail.';

        return $context;
    }

    private function estimateGroqTokens(array $messages): int
    {
        $chars = collect($messages)->sum(fn ($message) => strlen((string) ($message['content'] ?? '')));

        return (int) ceil($chars / 3.2);
    }

    private function fitGroqMessages(array $messages, int $maxPromptTokens): array
    {
        if ($this->estimateGroqTokens($messages) <= $maxPromptTokens) {
            return $messages;
        }

        $contextIndex = collect($messages)->search(fn ($message) => ($message['role'] ?? '') === 'user' && str_starts_with((string) ($message['content'] ?? ''), 'system_context:'));
        if ($contextIndex !== false) {
            $raw = substr((string) $messages[$contextIndex]['content'], strlen('system_context:'));
            $context = json_decode(ltrim($raw), true);
            if (is_array($context)) {
                $budgetChars = max(4000, (int) (($maxPromptTokens - 900) * 3.2));
                $messages[$contextIndex]['content'] = "system_context:\n".$this->encodeAiContext($this->compactAiContextForGroq($context, $budgetChars));
            }
        }

        if ($this->estimateGroqTokens($messages) <= $maxPromptTokens) {
            return $messages;
        }

        $system = array_shift($messages);
        $question = array_pop($messages);
        $messages = array_slice($messages, -2);
        if ($system !== null) {
            array_unshift($messages, $system);
        }
        if ($question !== null) {
            $messages[] = $question;
        }

        return $messages;
    }

    private function buildSystemAiSearchHits(string $question): array
    {
        $terms = $this->aiSearchTerms($question);
        if (empty($terms)) {
            return ['terms' => [], 'matches' => []];
        }

        $applySearch = function ($query, array $columns) use ($terms) {
            return $query->where(function ($where) use ($columns, $terms) {
                foreach ($terms as $term) {
                    $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';
                    foreach ($columns as $column) {
                        $where->orWhere($column, 'like', $like);
                    }
                }
            });
        };

        $matches = [];

        if ($this->aiTableExists('loading_lists')) {
            $query = DB::table('loading_lists as ll')->leftJoin('customers as c', 'c.id', '=', 'll.customer_id');
            $matches['loading_list_pds'] = $this->aiRows($applySearch($query, ['ll.number', 'll.pds_number', 'll.cycle', 'c.name'])
                ->select('ll.number as loading_list', 'll.pds_number', 'll.cycle', 'll.delivery_date', 'll.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('ll.delivery_date')
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('pullings')) {
            $query = DB::table('pullings as p')->leftJoin('customers as c', 'c.id', '=', 'p.customer_id');
            $matches['pulling'] = $this->aiRows($applySearch($query, ['p.loading_list', 'p.pds_number', 'p.cycle', 'c.name'])
                ->select('p.loading_list', 'p.pds_number', 'p.cycle', 'p.pulling_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('p.pulling_date')
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('internal_parts')) {
            $query = DB::table('internal_parts as ip')->leftJoin('lines as l', 'l.id', '=', 'ip.line_id');
            $matches['master_part_stock'] = $this->aiRows($applySearch($query, ['ip.part_number', 'ip.back_number', 'ip.part_name', 'l.name'])
                ->leftJoin('production_stocks as ps', 'ps.internal_part_id', '=', 'ip.id')
                ->select('ip.part_number', 'ip.back_number', 'ip.part_name', DB::raw('COALESCE(l.name, "-") as line'), DB::raw('COALESCE(ip.standard_stock, 0) as standard_stock'), DB::raw('COALESCE(ps.current_stock, 0) as current_stock'))
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('production_plans')) {
            $query = DB::table('production_plans as pp');
            $matches['production_plan'] = $this->aiRows($applySearch($query, ['pp.line', 'pp.customer', 'pp.dock', 'pp.back_no', 'pp.dn_number'])
                ->select('pp.plan_date', 'pp.line', 'pp.customer', 'pp.dock', 'pp.cycle', 'pp.back_no', 'pp.order_qty', 'pp.direct_pulling_qty', 'pp.delivery_time', 'pp.balance_time', 'pp.dn_number')
                ->orderByDesc('pp.plan_date')
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('pis_scans')) {
            $query = DB::table('pis_scans as ps')->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id');
            $matches['pis'] = $this->aiRows($applySearch($query, ['ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'c.name'])
                ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'ps.delivery_date', 'ps.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('ps.delivery_date')
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('quality_inspections')) {
            $query = DB::table('quality_inspections as qi');
            $matches['quality'] = $this->aiRows($applySearch($query, ['qi.kanban_code', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.line', 'qi.pic', 'qi.judgement', 'qi.approval_status'])
                ->select('qi.kanban_code', 'qi.check_type', 'qi.line', 'qi.shift', 'qi.pic', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.judgement', 'qi.approval_status', 'qi.checked_at')
                ->orderByDesc('qi.checked_at')
                ->limit(8)
                ->get());
        }

        if ($this->aiTableExists('error_logs')) {
            $query = DB::table('error_logs as el');
            $matches['error_log'] = $this->aiRows($applySearch($query, ['el.area', 'el.message', 'el.expected', 'el.scanned'])
                ->select('el.area', 'el.message', 'el.expected', 'el.scanned', 'el.date')
                ->orderByDesc('el.date')
                ->limit(8)
                ->get());
        }

        return ['terms' => $terms, 'matches' => array_filter($matches)];
    }

    private function aiSearchTerms(string $question): array
    {
        preg_match_all('/[A-Za-z0-9][A-Za-z0-9._-]{2,}/', $question, $matches);

        $stopwords = [
            'yang', 'dan', 'atau', 'untuk', 'dari', 'mana', 'apa', 'berapa', 'kenapa', 'coba', 'tolong', 'cek',
            'data', 'hari', 'ini', 'besok', 'kemarin', 'status', 'loading', 'list', 'pds', 'part',
            'preparation', 'pulling', 'produksi', 'production', 'stock', 'stok', 'error', 'quality', 'customer',
        ];

        return collect($matches[0] ?? [])
            ->map(fn ($term) => trim((string) $term))
            ->filter(fn ($term) => strlen($term) >= 3 && ! in_array(strtolower($term), $stopwords, true))
            ->unique(fn ($term) => strtolower($term))
            ->take(6)
            ->values()
            ->all();
    }

    private function aiRows($rows): array
    {
        return collect($rows)->map(fn ($row) => (array) $row)->values()->all();
    }

    private function aiTableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function aiColumnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }

    private function aiTableCount(string $table): ?int
    {
        if (! $this->aiTableExists($table)) {
            return null;
        }

        try {
            return DB::table($table)->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Halaman Production Stock (sebelumnya ada di index()).
     */
    public function productionStock(Request $request)
    {
        $lineNames = DB::table('lines')
            ->join('internal_parts', 'internal_parts.line_id', '=', 'lines.id')
            ->select('lines.name')
            ->distinct()
            ->orderByRaw("CASE WHEN lines.name = 'AS711' THEN 0 ELSE 1 END")
            ->orderBy('lines.name')
            ->pluck('name')
            ->map(fn ($line) => (string) $line);

        $focusBackNumber = trim((string) $request->query('back_number', ''));
        $activeLine = $lineNames->first();

        if ($lineNames->contains('AS711')) {
            $activeLine = 'AS711';
        }

        if ($focusBackNumber !== '') {
            $focusLine = DB::table('internal_parts')
                ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
                ->where('internal_parts.back_number', $focusBackNumber)
                ->value('lines.name');

            if ($focusLine && $lineNames->contains((string) $focusLine)) {
                $activeLine = (string) $focusLine;
            }
        }

        $lines = $lineNames
            ->values()
            ->map(function (string $line, int $index) use ($activeLine) {
                $slug = Str::slug($line);

                return [
                    'key' => 'line-'.$index.'-'.($slug !== '' ? $slug : 'stock'),
                    'name' => $line,
                    'active' => $line === $activeLine,
                ];
            })
            ->all();

        return view('pages.production.prodstock', [
            'lines' => $lines,
            'activeLine' => $activeLine,
            'focusBackNumber' => $focusBackNumber,
        ]);
    }

    public function productionStockData(Request $request)
    {
        $line = trim((string) $request->query('line', ''));

        if ($line === '') {
            return response()->json([
                'data' => [],
                'message' => 'Line wajib diisi.',
            ], 422);
        }

        $items = $this->productionStockBaseQuery()
            ->where('lines.name', $line)
            ->orderBy('internal_parts.back_number')
            ->get()
            ->map(function ($item) {
                $qty = (int) $item->current_stock;
                $standard = (int) $item->standard_stock;

                if ($qty < $standard) {
                    $status = 'Under Stock';
                    $statusClass = 'red';
                    $statusIcon = 'fas fa-arrow-down';
                } elseif ($qty === $standard) {
                    $status = 'Low Stock';
                    $statusClass = 'yellow';
                    $statusIcon = 'fas fa-minus';
                } else {
                    $status = 'In Stock';
                    $statusClass = 'green';
                    $statusIcon = 'fas fa-check';
                }

                return [
                    'id' => (int) $item->id,
                    'part_number' => (string) $item->part_number,
                    'back_number' => (string) $item->back_number,
                    'qty' => $qty,
                    'standard' => $standard,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'status_icon' => $statusIcon,
                ];
            })
            ->values();

        return response()
            ->json(['data' => $items])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function productionStockBaseQuery()
    {
        return DB::table('internal_parts')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->leftJoin('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->select(
                'lines.name as line_name',
                'internal_parts.id',
                'internal_parts.part_number',
                'internal_parts.back_number',
                DB::raw('COALESCE(production_stocks.current_stock, 0) as current_stock'),
                DB::raw('COALESCE(internal_parts.standard_stock, 0) as standard_stock')
            );
    }

    public function downloadPartImportTemplate()
    {
        return Excel::download(
            new PartImportTemplateExport(),
            'format import new part BELLA.xlsx'
        );
    }

    public function exportMutation()
    {
        $tz = 'Asia/Jakarta';

        $date = request('date');
        $from = request('from');
        $to = request('to');

        // Resolve range
        if ($date) {
            $start = Carbon::parse($date, $tz)->startOfDay();
            $end = Carbon::parse($date, $tz)->endOfDay();
        } else {
            // default: hari ini kalau kosong semua
            $start = $from ? Carbon::parse($from, $tz)->startOfDay() : Carbon::now($tz)->startOfDay();
            $end = $to ? Carbon::parse($to, $tz)->endOfDay() : Carbon::now($tz)->endOfDay();
        }

        // Ambil data mutasi
        $rows = DB::table('mutations')
            ->join('internal_parts', 'internal_parts.id', '=', 'mutations.internal_part_id')
            ->join('lines', 'lines.id', '=', 'internal_parts.line_id')
            ->select(
                'lines.name as line_name',
                'internal_parts.back_number',
                'mutations.serial_number',
                'mutations.qty',
                'mutations.type',
                DB::raw("DATE_FORMAT(mutations.date, '%Y-%m-%d %H:%i:%s') as mutation_date")
            )
            ->whereBetween('mutations.date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->where('mutations.type', 'supply')
            ->orderBy('lines.name')
            ->orderBy('mutations.date')
            ->orderBy('internal_parts.back_number')
            ->get();

        $filename = 'Mutation_'.$start->format('Ymd').'_'.$end->format('Ymd').'.xlsx';

        return Excel::download(
            new MutationMultiSheetExport($rows),
            $filename
        );
    }

    public function prodResult(Request $request)
    {
        $selectedDate = $request->input('date') ?? now()->toDateString(); // default hari ini

        // Agregat per (line, back_number): cukup jumlah serial + tanggal scan terakhir.
        // Detail serial diambil terpisah via AJAX (prodResultDetail) supaya payload awal kecil.
        $data = DB::table('mutations')
            ->join('internal_parts', 'mutations.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->where('mutations.type', 'supply')
            // range, bukan whereDate(), biar bisa pakai index created_at
            ->whereBetween('mutations.created_at', [
                Carbon::parse($selectedDate)->startOfDay(),
                Carbon::parse($selectedDate)->endOfDay(),
            ])
            ->groupBy('lines.name', 'internal_parts.back_number')
            ->orderByRaw('MAX(mutations.date) DESC')
            ->get([
                DB::raw('lines.name as line'),
                DB::raw('internal_parts.back_number as back_number'),
                DB::raw('COUNT(*) as cnt'),
                DB::raw('MAX(mutations.date) as last_date'),
            ]);

        // Organisasi data per line
        $lines = [];
        foreach ($data as $value) {
            $lineKey = $value->line;
            if (! isset($lines[$lineKey])) {
                $lines[$lineKey] = ['line' => $lineKey, 'items' => []];
            }
            $lines[$lineKey]['items'][] = [
                'back_number' => $value->back_number,
                'count' => (int) $value->cnt,
                'last_date' => $value->last_date,
            ];
        }

        $result = [];
        foreach ($lines as $line) {
            $result[] = (object) $line;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20; // bebas, kamu bisa ubah sesuai kebutuhan

        $collection = collect($result);
        $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedResult = new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('pages.production.prodResult', [
            'lines' => $paginatedResult,
            'selectedDate' => $selectedDate,
        ]);
    }

    /**
     * Detail serial untuk satu back number pada satu tanggal — diambil via AJAX saat baris di-expand,
     * supaya tidak ikut di-render di awal (lihat prodResult.blade.php).
     */
    public function prodResultDetail(Request $request)
    {
        $line = (string) $request->input('line');
        $backNumber = (string) $request->input('back_number');
        $date = $request->input('date') ?? now()->toDateString();

        if ($backNumber === '') {
            return response()->json(['details' => []]);
        }

        $details = DB::table('mutations')
            ->join('internal_parts', 'mutations.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->where('mutations.type', 'supply')
            ->where('internal_parts.back_number', $backNumber)
            ->when($line !== '', fn ($q) => $q->where('lines.name', $line))
            ->whereBetween('mutations.created_at', [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
            ])
            ->orderBy('mutations.date', 'desc')
            ->get(['mutations.serial_number', 'mutations.qty', 'mutations.date']);

        return response()->json(['details' => $details]);
    }

    public function prodPlan(Request $request)
    {
        set_time_limit(90);

        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10);
        $end = $start->copy()->addDay();

        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();
        $forceRefresh = $request->has('force_refresh');

        $lastUpdate = ProductionPlan::where('plan_date', $today->format('Y-m-d'))->max('updated_at');

        // === NEW: cek signature MSSQL ===
        $sigKey = 'pulling:sig:'.$selectedDate->format('Ymd');
        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();
        $curSig = $this->externalSignature($selectedDate, $allBackNos); // pakai default excluded
        $prevSig = Cache::get($sigKey);

        $shouldRefresh =
            $forceRefresh ||
            ! $lastUpdate ||
            ! $prevSig ||
            ($curSig && $curSig !== $prevSig);

        if ($shouldRefresh) {
            try {
                DB::beginTransaction();

                $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
                if ($rawData->isEmpty()) {
                    throw new \Exception('No production data available');
                }

                $processedData = $this->processRawData($rawData, $start, $end);
                $this->updateProductionData($processedData, $this->backNosByLine, $today);

                DB::commit();

                Cache::put($sigKey, $curSig, now()->addHours(6)); // simpan signature terkini
                $lastUpdate = now();
                $message = 'Production data updated (upstream changed).';
                $messageType = 'success';
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Production data processing failed: '.$e->getMessage());
                $message = 'Failed to update data: '.$e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'No upstream change; using cached plan.';
            $messageType = 'info';
        }

        $grouped = $this->getGroupedData($this->backNosByLine, $today);

        return view('pages.pulling.prodPlan', [
            'grouped' => $grouped,
            'lastUpdate' => $lastUpdate ?? now(),
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'message' => $message ?? null,
            'messageType' => $messageType ?? null,
        ]);
    }

    public function boardLanding(Request $request)
    {
        $today = $request->input('date') ?? now()->toDateString();

        return view('pages.pulling.landing', [
            'selectedDate' => $today,
        ]);
    }

    public function prodBoard(Request $request)
    {
        $group = strtoupper($request->input('group', 'AS')); // default AS
        $selectedDate = $request->input('date')
            ? \Carbon\Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        [$boards, $stamp, $lines] = $this->buildBoardsForDate_($selectedDate, $group);

        return view('pages.pulling.board', [
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'boards' => $boards,
            'lines' => $lines,
            'group' => $group, // <<— penting untuk Blade & JS
        ]);
    }

    /**
     * JSON state untuk Board (dipanggil ketika ada SSE update).
     * GET /pulling/board/state?date=YYYY-MM-DD
     */
    public function prodBoardState(Request $request)
    {
        $group = strtoupper($request->input('group', 'AS'));
        $selectedDate = $request->input('date')
            ? \Carbon\Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        [$boards, $stamp, $lines] = $this->buildBoardsForDate_($selectedDate, $group);

        return response()->json([
            'date' => $selectedDate->format('Y-m-d'),
            'at' => $stamp,
            'group' => $group,
            'lines' => $lines,
            'boards' => $boards,
        ]);
    }

    private function buildBoardsForDate_(Carbon $selectedDate, string $group = 'AS'): array
    {
        $todayISO = $selectedDate->toDateString();
        $stamp = now()->format('H:i:s');

        // === Kumpulkan kandidat line dari CONFIG & dari DB (UNION) ===
        $cfgLines = collect(array_keys($this->backNosByLine ?? []))
            ->map(fn ($s) => strtoupper(trim((string) $s)));

        $dbLines = ProductionPlan::whereDate('plan_date', $todayISO)
            ->select('line')->distinct()->pluck('line')
            ->filter()
            ->map(fn ($s) => strtoupper(trim((string) $s)));

        // UNION lalu filter sesuai group (MA atau AS), urut natural (prefix|angka 3 digit)
        $LINES = $cfgLines->merge($dbLines)
            ->unique()
            ->filter(fn ($L) => strtoupper($group) === 'MA'
                ? str_starts_with($L, 'MA')
                : str_starts_with($L, 'AS'))
            ->sortBy(function ($L) {
                $prefix = substr($L, 0, 2);
                $num = (int) preg_replace('/^\D+/', '', $L);

                return sprintf('%s|%03d', $prefix, $num);
            })
            ->values()
            ->all();

        // Jika kosong, fallback DB murni
        if (empty($LINES)) {
            $fallback = ProductionPlan::whereDate('plan_date', $todayISO)
                ->select('line')->distinct()->pluck('line')
                ->filter()->map(fn ($s) => strtoupper(trim($s)))->values();

            $LINES = $fallback->filter(fn ($L) => strtoupper($group) === 'MA'
                ? str_starts_with($L, 'MA')
                : str_starts_with($L, 'AS'))
                ->sortBy(function ($L) {
                    $prefix = substr($L, 0, 2);
                    $num = (int) preg_replace('/^\D+/', '', $L);

                    return sprintf('%s|%03d', $prefix, $num);
                })
                ->values()
                ->all();
        }

        // === Ambil plan hari itu untuk line yang tampil, urut ala prodPlan ===
        $items = ProductionPlan::whereDate('plan_date', $todayISO)
            ->when(! empty($LINES), fn ($q) => $q->whereIn('line', $LINES))
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->orderBy('seq_no')
            ->orderBy('id')
            ->get();

        // Helper normalisasi
        $normBack = fn ($bn) => $this->normalizeBackNo($bn);   // D111 → CI12
        $normDock = fn ($dk) => $this->normalizeDock($dk);     // DOM/DOMESTIC → NR; EXPORT → EXP
        $present = fn ($bn) => $this->presentBackNoForGroup($bn, strtoupper($group)); // <<— NEW

        // === Kelompok per line
        $byLine = [];
        foreach ($LINES as $L) {
            $byLine[$L] = collect();
        }
        foreach ($items as $it) {
            $key = strtoupper((string) ($it->line ?? ''));
            if (isset($byLine[$key])) {
                $byLine[$key]->push($it);
            }
        }

        // === Sort khusus CI12/D111: split C4–7 @6I → C8–3 @EXP → sisanya
        $ci12Bucket = function ($row) use ($normBack) {
            $bn = $normBack($row->back_no ?? '');
            if ($bn !== 'CI12') {
                return 5;
            }
            $dock = strtoupper(trim((string) ($row->dock ?? '')));
            $cycle = (int) ($row->cycle ?? 0);
            $in47 = in_array($cycle, [4, 5, 6, 7], true);
            $in83 = in_array($cycle, [8, 1, 2, 3], true);

            if ($dock === '6I' && $in47) {
                return 0;
            } // C4–7 @ 6I
            if ($dock === 'EXP' && $in83) {
                return 1;
            } // C8–3 @ EXP

            return 2;
        };

        // Terapkan sort CI12 per line dengan menjaga urutan global via baseKey
        foreach ($byLine as $k => $col) {
            $byLine[$k] = $col->sortBy(function ($it) use ($ci12Bucket) {
                $date = $it->delivery_date ?: '9999-12-31';
                $time = $it->delivery_time ?: '99:99';
                $seq = (int) ($it->seq_no ?? 999999);
                $id = (int) ($it->id ?? 0);

                $baseKey = sprintf('%s|%s|%06d|%09d', $date, $time, $seq, $id);
                $suffix = sprintf('|%02d', $ci12Bucket($it));

                return $baseKey.$suffix;
            })->values();
        }

        // === Overlay virtual MA (kalau group MA)
        if (strtoupper($group) === 'MA') {
            $this->applyMAOverlayInto($byLine, $selectedDate);

            foreach ($byLine as $k => $col) {
                $byLine[$k] = $col->sortBy(function ($it) use ($ci12Bucket) {
                    $date = $it->delivery_date ?? '9999-12-31';
                    $time = $it->delivery_time ?? '99:99';
                    $virt = property_exists($it, '__virt_seq') ? (int) $it->__virt_seq : 9999;
                    $seq = (int) ($it->seq_no ?? 999999);
                    $id = (int) ($it->id ?? 0);

                    $baseKey = sprintf('%s|%s|%06d|%06d|%09d', $date, $time, $virt, $seq, $id);
                    $suffix = sprintf('|%02d', $ci12Bucket($it));

                    return $baseKey.$suffix;
                })->values();
            }
        }

        // === Data KPI/progress tetap
        $grouped = $this->getGroupedData($this->backNosByLine, $selectedDate);

        // ===== Builder satu line =====
        $buildBoardForLine = function (\Illuminate\Support\Collection $rows, string $lineKey) use ($grouped, $present, $normBack) {
            $g = $grouped[$lineKey] ?? [];
            $mQty = (int) ($g['morning_shift_qty'] ?? 0);
            $mAct = (int) ($g['morning_shift_actual'] ?? 0);
            $nQty = (int) ($g['night_shift_qty'] ?? 0);
            $nAct = (int) ($g['night_shift_actual'] ?? 0);

            if ($rows->count() > 0 && ($mQty + $nQty) === 0) {
                $mQty = (int) $rows->sum('order_qty');
                $mAct = (int) $rows->sum(fn ($i) => (int) ($i->direct_pulling_qty ?? 0));
                $nQty = 0;
                $nAct = 0;
            }

            $nowMin = (int) now()->format('H') * 60 + (int) now()->format('i');
            [$shiftLabel, $orderQty, $actualQty, $status] = $this->resolveShiftProgress(
                $nowMin,
                $mQty,
                $mAct,
                $nQty,
                $nAct
            );

            $rows = $rows->values();

            // Hitung uniq berdasarkan PRESENTED back_no (setelah CI->CH untuk MA)
            $uniqBackNo = $rows->pluck('back_no')
                ->filter(fn ($v) => filled($v))
                ->map(fn ($v) => $present($normBack($v)))   // <<— penting
                ->unique()
                ->count();

            $isComplete = function ($it) {
                $ord = (int) ($it->order_qty ?? 0);
                $done = (int) ($it->direct_pulling_qty ?? 0) + (int) ($it->stock_chute_qty ?? 0);

                return $ord <= 0 || $done >= $ord;
            };

            $currentIdx = null;
            foreach ($rows as $idx => $it) {
                if (! $isComplete($it)) {
                    $currentIdx = $idx;
                    break;
                }
            }
            $currentItem = $currentIdx !== null ? $rows[$currentIdx] : ($rows->last() ?: null);

            $current = $currentItem ? [
                // tampilkan back_no yang sudah di-present (CI->CH jika MA)
                'back_no' => $present($normBack($currentItem->back_no)),
                'customer' => $currentItem->customer,
                'dock' => $currentItem->dock,
                'order_qty' => (int) ($currentItem->order_qty ?? 0),
                'dp' => (int) ($currentItem->direct_pulling_qty ?? 0),
                'sc' => (int) ($currentItem->stock_chute_qty ?? 0),
                'start' => $currentItem->working_start ?: '--',
            ] : [
                'back_no' => '—',
                'customer' => '—',
                'dock' => '—',
                'order_qty' => 0,
                'dp' => 0,
                'sc' => 0,
                'start' => '--',
            ];

            $nextCandidates = collect();
            if ($currentIdx !== null) {
                $nextCandidates = $rows->slice($currentIdx + 1)->filter(fn ($it) => ! $isComplete($it))->values();
            }

            $norm = fn ($s) => strtoupper(trim((string) $s));
            $unifyForMerge = function ($bn) use ($normBack) {
                // Samakan “keluarga” CI12 / CI19 dari alias (D111→CI12, D500→CI19)
                return $normBack($bn);
            };
            $isMergeTargetBN = fn ($bnU) => in_array($bnU, ['CI12', 'CI19'], true);

            $groups = [];
            $idxCounter = 0;
            $rawDock = fn ($s) => strtoupper(trim((string) $s));
            foreach ($nextCandidates as $it) {
                $bnU = $unifyForMerge($it->back_no);
                $merge = $isMergeTargetBN($bnU);
                $rowOrderIdx = $idxCounter++;

                $date = $it->delivery_date ?: '9999-12-31';
                $time = $it->delivery_time ?: '99:99';
                $sortKey = "{$date}|{$time}|".sprintf('%06d', $rowOrderIdx);

                $DCK = $rawDock($it->dock ?? '');
                $key = $merge && $DCK === '6I'
                    ? ('MERGE6I|'.$bnU.'|'.$date.'|'.$time)
                    : ('ROW|'.($it->dn_number ?? '').'|'.($it->cycle ?? '').'|'.($it->back_no ?? '').'|'.$sortKey);

                if (! isset($groups[$key])) {
                    $groups[$key] = [
                        'back_no' => $merge ? $bnU : ($it->back_no ?? '—'),
                        'customer' => $it->customer ?? '—',
                        'dock' => $norm($it->dock) === '6I' ? '6I' : ($it->dock ?? '—'),
                        'order_qty' => 0,
                        'delivery_time' => $it->delivery_time ?: '--',
                        'delivery_date' => $it->delivery_date ?: '',
                        'sort_key' => $sortKey,
                        '__has6I' => ($norm($it->dock) === '6I'),
                    ];
                }

                $groups[$key]['order_qty'] += (int) ($it->order_qty ?? 0);

                if ($sortKey < ($groups[$key]['sort_key'] ?? '~~~~~')) {
                    $groups[$key]['sort_key'] = $sortKey;
                    $groups[$key]['delivery_time'] = $it->delivery_time ?: $groups[$key]['delivery_time'];
                    $groups[$key]['delivery_date'] = $it->delivery_date ?: $groups[$key]['delivery_date'];
                    $groups[$key]['customer'] = $it->customer ?? $groups[$key]['customer'];
                    if (! $merge) {
                        $groups[$key]['back_no'] = $it->back_no ?? $groups[$key]['back_no'];
                    }
                }

                if ($norm($it->dock) === '6I') {
                    $groups[$key]['__has6I'] = true;
                    $groups[$key]['dock'] = '6I';
                }
            }

            $aggregatedNext = collect(array_values(array_map(function ($g) {
                unset($g['__has6I']);

                return $g;
            }, $groups)))->sortBy('sort_key')->values();

            $nextHighlight = [
                'back_no' => '—',
                'customer' => '—',
                'dock' => '—',
                'order_qty' => 0,
                'delivery_time' => '--',
                'delivery_date' => '',
            ];
            $nextList = [];

            if ($aggregatedNext->isNotEmpty()) {
                $first = $aggregatedNext->first();
                $nextHighlight = [
                    // present back_no terlebih dahulu
                    'back_no' => $present($first['back_no']),
                    'customer' => $first['customer'],
                    'dock' => $first['dock'],
                    'order_qty' => $first['order_qty'],
                    'delivery_time' => $first['delivery_time'],
                    'delivery_date' => $first['delivery_date'],
                ];
                $nextList = $aggregatedNext->slice(1, 20)->map(fn ($a) => [
                    'back_no' => $present($a['back_no']),
                    'customer' => $a['customer'],
                    'dock' => $a['dock'],
                    'order_qty' => $a['order_qty'],
                    'delivery_time' => $a['delivery_time'],
                    'delivery_date' => $a['delivery_date'],
                ])->values()->all();
            }

            return [
                'progress' => [
                    'label' => $shiftLabel,
                    'order' => $orderQty,
                    'actual' => $actualQty,
                    'status' => $status,
                ],
                'current' => $current,
                'nextHighlight' => $nextHighlight,
                'nextList' => $nextList,
                'daily' => ['totalBackNo' => $uniqBackNo],
                'totalBackNo' => $uniqBackNo,
            ];
        };

        // Build
        $boards = [];
        foreach ($LINES as $L) {
            $boards[$L] = $buildBoardForLine($byLine[$L] ?? collect(), $L);
        }

        return [$boards, $stamp, $LINES];
    }

    // Tambahkan helper ini di dalam controller (private function)
    private function presentBackNoForGroup(?string $bn, string $group): string
    {
        $B = strtoupper(trim((string) $bn));
        if ($group === 'MA') {
            // Ganti CI + digit menjadi CH + digit, contoh CI12 → CH12
            $B = preg_replace('/^CI(?=\d)/', 'CH', $B);
        }

        return $B;
    }

    // --- Helpers alias & dock ---
    private function normalizeBackNo(?string $bn): string
    {
        $B = strtoupper(trim((string) $bn));

        return match ($B) {
            'D111' => 'CI12',
            'D500' => 'CI19',
            'D403' => 'CI18',
            default => $B,
        };
    }

    private function normalizeDock(?string $dock): ?string
    {
        $d = strtoupper(trim((string) $dock));
        if ($d === '') {
            return null;
        }

        if ($d === 'NR') {
            return 'NR';
        }            // hanya NR
        if (in_array($d, ['EXP', 'EXPORT'], true)) {
            return 'EXP';
        } // dan EXP/EXPORT

        return $d; // 6I, 6G, 6H, ADM, dll tetap apa adanya
    }

    /**
     * Ringkas order AS pada tanggal tsb.
     * Return:
     *   ['total' => [BN=>sum], 'byDock' => ["BN|DOM"=>sum, "BN|EXP"=>sum]]
     */
    private function getASBacknoSummary(Carbon $date): array
    {
        $iso = $date->toDateString();

        $rows = ProductionPlan::whereDate('plan_date', $iso)
            ->where('line', 'like', 'AS%')
            ->get();

        $total = [];
        $byDock = [];

        foreach ($rows as $r) {
            $bn = $this->normalizeBackNo($r->back_no);
            if (! $bn) {
                continue;
            }

            $ord = (int) ($r->order_qty ?? 0);
            if ($ord <= 0) {
                continue;
            } // nol tidak berpengaruh

            $total[$bn] = ($total[$bn] ?? 0) + $ord;

            $dock = $this->normalizeDock($r->dock);
            if ($dock) {
                $key = $bn.'|'.$dock;
                $byDock[$key] = ($byDock[$key] ?? 0) + $ord;
            }
        }

        return ['total' => $total, 'byDock' => $byDock];
    }

    /**
     * Bangun target order MA (virtual) per line berdasarkan LineStaticSequence.
     * Mengembalikan: [ 'MA001' => [ {back_no, dock, order_qty, seq_no, ...}, ... ], ... ]
     */
    private function buildMAAutoOrders(Carbon $date): array
    {
        $sum = $this->getASBacknoSummary($date);
        $factor = 1.1; // KONSTANTA: pengali 10%
        $seq = LineStaticSequence::where('is_active', true)
            ->where('line', 'like', 'MA%')
            ->orderBy('line')->orderBy('seq_no')->get();

        $out = [];
        foreach ($seq as $row) {
            $bn = $this->normalizeBackNo($row->back_no);
            $hint = $this->normalizeDock($row->dock_hint); // DOM/EXP/null

            $base = $hint
                ? ($sum['byDock'][$bn.'|'.$hint] ?? 0)
                : ($sum['total'][$bn] ?? 0);

            $qty = (int) ceil($base * $factor);
            if ($qty <= 0) {
                continue;
            }

            $out[$row->line][] = [
                'back_no' => $bn,
                'customer' => '—',
                'dock' => $hint ?: '—',
                'order_qty' => $qty,
                'dp' => 0,
                'sc' => 0,
                'start' => null,
                'seq_no' => (int) $row->seq_no,
            ];
        }

        return $out;
    }

    /**
     * Suntikkan “virtual” order MA ke $byLine (reference) TANPA menulis DB.
     * - Jika sudah ada row & order_qty == 0 → set ke target qty
     * - Jika belum ada row → push object stdClass (virtual)
     */
    private function applyMAOverlayInto(array &$byLine, Carbon $date): void
    {
        $virtual = $this->buildMAAutoOrders($date);
        if (empty($virtual)) {
            return;
        }

        foreach ($virtual as $line => $targets) {
            if (! isset($byLine[$line])) {
                continue;
            } // line tidak tampil di board

            /** @var \Illuminate\Support\Collection $col */
            $col = $byLine[$line];

            foreach ($targets as $t) {
                $bn = $this->normalizeBackNo($t['back_no']);
                $dock = $this->normalizeDock($t['dock']) ?: null;

                // Cari row existing utk BN (+dock bila ada hint)
                $match = $col->first(function ($it) use ($bn, $dock) {
                    $bnIt = $this->normalizeBackNo($it->back_no ?? '');
                    $dockIt = $this->normalizeDock($it->dock ?? null);
                    $dockOk = $dock ? ($dockIt === $dock) : true;

                    return ($bnIt === $bn) && $dockOk;
                });

                if ($match) {
                    // Hanya inject bila kosong
                    $ord = (int) ($match->order_qty ?? 0);
                    if ($ord <= 0) {
                        $match->order_qty = (int) $t['order_qty'];
                        if ($dock && empty($match->dock)) {
                            $match->dock = $dock;
                        }
                    }

                    continue;
                }

                // Tidak ada row: buat virtual row (stdClass aman untuk builder)
                $fake = new \stdClass();
                $fake->line = $line;
                $fake->back_no = $bn;
                $fake->customer = $t['customer'] ?? '—';
                $fake->dock = $dock;
                $fake->order_qty = (int) $t['order_qty'];
                $fake->direct_pulling_qty = 0;
                $fake->stock_chute_qty = 0;
                $fake->working_start = null;
                $fake->working_end = null;
                $fake->delivery_time = null;
                $fake->delivery_date = null;
                $fake->dn_number = null;
                $fake->cycle = null;
                $fake->prod_time = null;
                $fake->__virt_seq = (int) $t['seq_no'];

                $col->push($fake);
            }

            // Urutkan: yang punya working_start dulu, lalu urut seq static (jika ada), sisanya di belakang
            $byLine[$line] = $col->sortBy(function ($it) {
                $hasStart = filled($it->working_start);
                $seq = property_exists($it, '__virt_seq') ? (int) $it->__virt_seq : 999;
                // prioritas: punya start (0), tidak (1) → seq → dn_number sebagai tie-breaker
                return ($hasStart ? 0 : 1).'|'.sprintf('%03d', $seq).'|'.($it->dn_number ?? 'ZZZ');
            })->values();
        }
    }

    /** Khusus penyusunan data tabel + KPI shift (delivery-based 09:40 rule) */
    protected function getGroupedData($backNosByLine, $today)
    {
        $grouped = [];
        $todayISO = $today->toDateString();
        $nextISO = $today->copy()->addDay()->toDateString();

        // Window (menit)
        $MORNING_START = 12 * 60;          // 12:00
        $MORNING_END = 22 * 60 + 57;     // 22:57
        $NIGHT_START = 22 * 60 + 59;     // 22:59
        $NIGHT_END = 9 * 60 + 35;     // 09:35

        $toMin = function ($t) {
            if (! $t) {
                return null;
            }
            try {
                [$h, $m] = array_map('intval', explode(':', $t));
                if (! is_numeric($h) || ! is_numeric($m)) {
                    return null;
                }

                return $h * 60 + $m;
            } catch (\Throwable $e) {
                return null;
            }
        };

        foreach ($backNosByLine as $line => $backNos) {
            $lineData = ProductionPlan::whereDate('plan_date', $todayISO)
                ->where('line', $line)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->get();

            $isMorning = function ($item) use ($todayISO, $toMin, $MORNING_START, $MORNING_END) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);

                // Morning = HARI INI jam 12:00–22:57
                if ($dd && $tm !== null) {
                    return ($dd === $todayISO) && ($tm >= $MORNING_START) && ($tm <= $MORNING_END);
                }

                // Fallback: pakai working time (menit)
                $sm = $toMin($item->working_start ?? null);
                $em = $toMin($item->working_end ?? null);
                $in = function ($min) use ($MORNING_START, $MORNING_END) {
                    return $min !== null && $min >= $MORNING_START && $min <= $MORNING_END;
                };

                return $in($sm) || $in($em);
            };

            $isNight = function ($item) use ($todayISO, $nextISO, $toMin, $NIGHT_START, $NIGHT_END) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);

                // Night = HARI INI >=22:59 ATAU HARI BESOK <=09:35
                if ($dd && $tm !== null) {
                    return ($dd === $todayISO && $tm >= $NIGHT_START)
                        || ($dd === $nextISO && $tm <= $NIGHT_END);
                }

                // Fallback: pakai working time (menit) – melintasi tengah malam
                $sm = $toMin($item->working_start ?? null);
                $em = $toMin($item->working_end ?? null);
                $in = function ($min) use ($NIGHT_START, $NIGHT_END) {
                    return $min !== null && ($min >= $NIGHT_START || $min <= $NIGHT_END);
                };

                return $in($sm) || $in($em);
            };

            $morningItems = $lineData->filter($isMorning);
            $nightItems = $lineData->filter($isNight);

            $morningShiftQty = (int) $morningItems->sum('order_qty');
            $nightShiftQty = (int) $nightItems->sum('order_qty');
            $morningShiftActual = (int) $morningItems->sum(fn ($i) => (int) ($i->direct_pulling_qty ?? 0));
            $nightShiftActual = (int) $nightItems->sum(fn ($i) => (int) ($i->direct_pulling_qty ?? 0));

            $grouped[$line] = [
                'data' => $lineData->groupBy(function ($item) {
                    $cust = trim((string) ($item->customer ?? '')) ?: '--';
                    $time = trim((string) ($item->delivery_time ?? '')) ?: '--';
                    $dock = trim((string) ($item->dock ?? '')) ?: '--';

                    return "{$cust}|{$time}|{$dock}";
                }),
                'morning_shift_qty' => $morningShiftQty,
                'night_shift_qty' => $nightShiftQty,
                'total_qty' => $morningShiftQty + $nightShiftQty,
                'morning_shift_actual' => $morningShiftActual,
                'night_shift_actual' => $nightShiftActual,
                'total_actual' => $morningShiftActual + $nightShiftActual,
            ];
        }

        return $grouped;
    }

    /**
     * Hitung status shift sederhana (pakai window yang sama dengan getGroupedData):
     * Morning 12:00–22:57, Night 22:59–09:35.
     */
    private function resolveShiftProgress(int $nowMin, int $mQty, int $mAct, int $nQty, int $nAct): array
    {
        $MORNING_START = 12 * 60;          // 720
        $MORNING_END = 22 * 60 + 57;     // 1377
        $NIGHT_START = 22 * 60 + 59;     // 1379
        $NIGHT_END = 9 * 60 + 35;     // 575

        // default: pilih shift yang “berlaku” sekarang; kalau di gap, anggap Morning
        $label = 'Morning';
        $order = $mQty;
        $actual = $mAct;
        $elapsed = 0;
        $duration = max(1, $MORNING_END - $MORNING_START); // 657

        if ($nowMin >= $MORNING_START && $nowMin <= $MORNING_END) {
            $label = 'Morning';
            $order = $mQty;
            $actual = $mAct;
            $elapsed = $nowMin - $MORNING_START;
            $duration = max(1, $MORNING_END - $MORNING_START);
        } elseif ($nowMin >= $NIGHT_START || $nowMin <= $NIGHT_END) {
            $label = 'Night';
            $order = $nQty;
            $actual = $nAct;
            // durasi night melewati tengah malam
            $duration = (1440 - $NIGHT_START) + $NIGHT_END; // 61 + 575 = 636
            $elapsed = ($nowMin >= $NIGHT_START)
                ? ($nowMin - $NIGHT_START)
                : ((1440 - $NIGHT_START) + $nowMin);
        }

        // status sederhana: S1 (OK), NS (no start > 60min), LS1 (di bawah expected progress)
        if ($order <= 0) {
            return [$label, 0, 0, 'S1'];
        }

        $ratio = $actual / max(1, $order);
        $expected = min(1, $elapsed / max(1, $duration)); // linear expectation
        $status = 'S1';

        if ($ratio == 0 && $elapsed > 60) {
            $status = 'NS';
        } elseif ($ratio + 0.05 < $expected) { // toleransi 5%
            $status = 'LS1';
        }

        return [$label, $order, $actual, $status];
    }

    /** Bikin note singkat dari balance_time (format "H:i" atau "-H:i") */
    private function makeProgressNote(?string $balance): string
    {
        if (! $balance || ! preg_match('/^-?\d{2}:\d{2}$/', $balance)) {
            return 'Back no detail information';
        }

        return str_starts_with($balance, '-')
            ? "Late {$balance} to delivery"
            : "Buffer {$balance} to delivery";
    }

    public function progressPulling()
    {
        // get per delivery date
        $cycle = DB::table('loading_lists')
            ->join('loading_list_details', 'loading_list_details.loading_list_id', 'loading_lists.id')
            ->select(DB::raw('SUM(kanban_qty) as total_kanban, SUM(actual_kanban_qty) as total_actual'), 'cycle')
            ->where('delivery_date', '2023-07-11')
            ->groupBy('cycle')
            ->get();

        return view('pages.progressPulling');
    }

    public function importPart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv',
        ], [
            'file.required' => 'Silakan pilih file Master Part terlebih dahulu.',
            'file.mimes' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx, .xls, atau .csv.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', $validator->errors()->first());
        }

        try {
            $import = new PartImport();

            Excel::import($import, $request->file('file'));

            if (method_exists($import, 'isAllDuplicate') && $import->isAllDuplicate()) {
                return redirect()
                    ->back()
                    ->with('error', 'Import Master Part gagal. Semua data pada file sudah pernah diupload sebelumnya.')
                    ->with('import_errors', method_exists($import, 'getUserMessages') ? $import->getUserMessages() : []);
            }

            if (method_exists($import, 'hasImportedData') && ! $import->hasImportedData()) {
                return redirect()
                    ->back()
                    ->with('error', 'Import Master Part gagal. Tidak ada data baru yang berhasil diupload.')
                    ->with('import_errors', method_exists($import, 'getUserMessages') ? $import->getUserMessages() : []);
            }

            $message = method_exists($import, 'getSummaryMessage')
                ? $import->getSummaryMessage()
                : 'Part berhasil diupload!';

            $redirect = redirect()
                ->back()
                ->with('success', $message);

            if (method_exists($import, 'hasWarnings') && $import->hasWarnings()) {
                $redirect->with('warning', 'Beberapa baris tidak diproses. Silakan cek detail import.');
                $redirect->with('import_errors', method_exists($import, 'getUserMessages') ? $import->getUserMessages() : []);
            }

            return $redirect;
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $this->friendlyImportError($th->getMessage()));
        }
    }

    public function importManifest(Request $request)
    {
        Excel::import(new ManifestImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Manifest berhasil diupload!');
    }

    public function importStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv',
        ], [
            'file.required' => 'Silakan pilih file Stock terlebih dahulu.',
            'file.mimes' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx, .xls, atau .csv.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', $validator->errors()->first());
        }

        try {
            $import = new StockImport();

            Excel::import($import, $request->file('file'));

            if (method_exists($import, 'hasUpdatedData') && ! $import->hasUpdatedData()) {
                return redirect()
                    ->back()
                    ->with('error', 'Import Stock gagal. Tidak ada stock yang berhasil diupdate.')
                    ->with('import_errors', method_exists($import, 'getUserMessages') ? $import->getUserMessages() : []);
            }

            $message = method_exists($import, 'getSummaryMessage')
                ? $import->getSummaryMessage()
                : 'Standard stock dan current stock berhasil diimport.';

            $redirect = redirect()
                ->back()
                ->with('success', $message);

            if (method_exists($import, 'hasWarnings') && $import->hasWarnings()) {
                $redirect->with('warning', 'Beberapa baris stock tidak diproses. Silakan cek detail import.');
                $redirect->with('import_errors', method_exists($import, 'getUserMessages') ? $import->getUserMessages() : []);
            }

            return $redirect;
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $this->friendlyImportError($th->getMessage()));
        }
    }

    private function friendlyImportError(string $message): string
    {
        if (str_contains($message, 'Header Excel tidak ditemukan')) {
            return 'Header Excel tidak ditemukan. Pastikan file memiliki kolom yang sesuai.';
        }

        if (str_contains($message, 'Undefined array key')) {
            return 'Format kolom Excel tidak sesuai. Pastikan nama kolom sudah benar.';
        }

        if (str_contains($message, 'Trying to access array offset')) {
            return 'Format data Excel tidak sesuai. Pastikan header dan isi kolom sudah benar.';
        }

        if (str_contains($message, 'Call to a member function store() on null')) {
            return 'File belum dipilih. Silakan pilih file Excel terlebih dahulu.';
        }

        if (str_contains($message, 'SQLSTATE')) {
            return 'Terjadi kesalahan saat menyimpan data ke database. Silakan cek kembali isi file Excel.';
        }

        return $message ?: 'Terjadi kesalahan saat import file.';
    }

    //Receiving Dashboard
    public function receivingDashboard()
    {
        $startOfWeek = request('start_date') ? Carbon::parse(request('start_date')) : now()->startOfWeek();
        $endOfWeek = request('end_date') ? Carbon::parse(request('end_date')) : now()->startOfWeek()->addWeeks(2)->endOfWeek();
        // dd($startOfWeek->toDateString(), $endOfWeek->toDateString());

        $area = request('area') ? request('area') : 'unit';
        $statusColorss = [
            0 => '#cccccc', // Default / tidak diketahui
            1 => '#007bff', // Terdaftar
            2 => '#f52899', // Dikirim
            3 => '#ffc107', // Diterima Sebagian
            4 => '#28a745', // Diterima Semua
            5 => '#fd7e14', // Pengiriman Sebagian
        ];

        $statusColors = [
            0 => '#cccccc', // Default / tidak diketahui
            1 => '#cccccc', // Terdaftar
            2 => '#cccccc', // Dikirim
            3 => '#cccccc', // Diterima Sebagian
            4 => '#28a745', // Diterima Semua
            5 => '#fd7e14', // Pengiriman Sebagian
        ];

        // JOIN external_deliveries ke suppliers agar dapat nama supplier
        $deliveries = DB::table('external_deliveries')
            ->join('suppliers', 'external_deliveries.supplier_code', '=', 'suppliers.code')
            ->select(
                'external_deliveries.*',
                'suppliers.name as supplier_name'
            )
            ->whereBetween('delivery_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('area', 'LIKE', '%'.$area.'%') // Filter berdasarkan area
            ->get();

        $seriesData = [];

        foreach ($deliveries as $delivery) {
            $date = \Carbon\Carbon::parse($delivery->delivery_date);

            // Lewati data jika tidak termasuk minggu ini
            if (! $date->between($startOfWeek, $endOfWeek)) {
                continue;
            }

            // Format "00:HH:MM", ambil HH dan MM
            $timeParts = explode(':', $delivery->delivery_time);
            $hour = (int) ($timeParts[1] ?? 0);
            $minute = (int) ($timeParts[2] ?? 0);

            $start = $date->copy()->setTime($hour, $minute);
            $end = $start->copy()->addMinutes(120);

            $status = $delivery->status ?? 0;
            $color = $statusColors[$status] ?? '#cccccc';

            $seriesData[] = [
                'x' => $delivery->supplier_name,
                'y' => [$start->timestamp * 1000, $end->timestamp * 1000],
                'fillColor' => $color,
                'pick_list' => $delivery->pick_list, // ini penting untuk buka modal
            ];
        }

        $series = [[
            'name' => 'Pengiriman Aktual',
            'data' => $seriesData,
        ]];
        $now = now();

        if ($now->between($startOfWeek, $endOfWeek)) {
            $annotations = $now->timestamp * 1000;
        } else {
            $annotations = null;
        }

        return view('pages.dashboard_receiving', [
            'series' => $series,
            'annotationTimestamp' => $annotations,
        ]);
    }

    public function applyTimeToDate(Carbon $date, string $time)
    {
        [$hour, $minute, $second] = explode(':', $time);

        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }

    public function getReceivingData(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $day = $request->input('day');

        $query = DB::table('receive_schedules')
            ->join('suppliers', 'receive_schedules.supplier_id', '=', 'suppliers.id')
            ->select('receive_schedules.*', 'suppliers.name as supplier_name');

        if ($supplierId) {
            $query->where('receive_schedules.supplier_id', $supplierId);
        }

        if ($day) {
            $query->where('receive_schedules.day', $day);
        }

        return response()->json($query->get());
    }

    public function showModal(Request $request)
    {
        $request->validate([
            'pick_list' => 'required|string',
        ]);
        $pickList = $request->pick_list;
        // $data = DB::connection('mssql_external')
        //     ->table('IAA1NT as a')
        //     ->where('a.CHR_NUB_NYSJNO', $pickList)
        //     ->select(
        //         'a.CHR_COD_OMSS as supplier_code',
        //         'a.CHR_COD_HINB as part_number',
        //         'a.CHR_NUB_SBNG as back_number',
        //         'a.DEC_SUR_SHSU as qty_ordered',
        //         'a.DEC_SUR_HSSU as qty_confirmed',
        //         'a.CHR_INF_HTTN as uom',
        //         'a.CHR_NUB_NYSJNO as pick_list'
        //     )
        //     ->get();
        // ambil picklist dari IA31NT terus dapat dec_cod_binid cari cod_binid di IA16NT
        $ia31nt = DB::connection('mssql_external')
            ->table('IA31NT as a')
            ->where('a.CHR_NUB_NYSJNO', $pickList)
            ->select(
                'a.DEC_COD_BINID as flight',
            )
            ->first();

        $data = DB::connection('mssql_external')
            ->table('IA16NT as a')
            ->where('a.DEC_COD_BINID', $ia31nt->flight)
            ->select(
                'a.CHR_NUB_MLNO as supplier_code',
                'a.CHR_COD_HINB as part_number',
                'a.DEC_SUR_KKSUH as qty_ordered',
                'a.CHR_INF_HTTN as uom'
            )
            ->get()
            ->map(function ($item) use ($pickList) {
                $item->pick_list = $pickList;

                $iaa1nt = DB::connection('mssql_external')
                    ->table('IAA1NT as a')
                    ->where('a.CHR_COD_HINB', $item->part_number)
                    ->select(
                        'a.CHR_NUB_SBNG as back_number',
                    )
                    ->first();

                $item->back_number = $iaa1nt->back_number; // Menambahkan custom2 dengan part_number

                return $item;
            });

        return response()->json($data);
    }

    public function kbnCheck()
    {
        return view('pages.production.kbnCheck');
    }

    public function kbnCheckSubmit(Request $request)
    {
        $request->validate([
            'back_number' => 'required',
            'serial_number' => 'required',
        ]);

        // Ambil data internal part berdasarkan back number
        $internalPart = InternalPart::where('back_number', $request->back_number)->first();

        // Jika tidak ditemukan, kembalikan dengan error
        if (! $internalPart) {
            return back()->with([
                'error' => "Back Number <strong>{$request->back_number}</strong> tidak ditemukan.",
            ])->withInput();
        }

        // Ambil kanban berdasarkan internal_part_id dan serial_number
        $kanban = DB::table('kanbans')
            ->where('internal_part_id', $internalPart->id)
            ->where('serial_number', $request->serial_number)
            ->first();

        // Jika kanban tidak ditemukan
        if (! $kanban) {
            return view('pages.production.kbnCheck', compact('internalPart'))
                ->with([
                    'back_number' => $request->back_number,
                    'serial_number' => $request->serial_number,
                    'error' => "Serial Number <strong>{$request->serial_number}</strong> tidak ditemukan untuk Back Number <strong>{$request->back_number}</strong>.",
                ]);
        }

        // Tampilkan hasil jika ditemukan
        return view('pages.production.kbnCheck', compact('internalPart', 'kanban'))
            ->with([
                'back_number' => $request->back_number,
                'serial_number' => $request->serial_number,
            ]);
    }

    public function partCheck()
    {
        return view('pages.production.partCheck');
    }

    public function partCheckSubmit(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = trim($request->barcode);

        $part = ScannedPart::with(['kanban.internalPart'])
            ->where('barcode', $barcode)
            ->latest('scanned_at')
            ->first();

        if (! $part) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $part->id,
                'line' => $part->line,
                'model' => $part->model,
                'dandori_board' => $part->dandori_board,
                'barcode' => $part->barcode,
                'last4' => $part->last4,
                'scan_date' => $part->scan_date,
                'scanned_at' => $part->scanned_at,
                'kanban_id' => $part->kanban_id,

                'serial' => optional($part->kanban)->serial_number,
                'back_number' => optional(optional($part->kanban)->internalPart)->back_number,
                'part_name' => optional(optional($part->kanban)->internalPart)->part_name,
            ],
        ]);
    }
}
