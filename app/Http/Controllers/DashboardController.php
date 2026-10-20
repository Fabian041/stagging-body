<?php

namespace App\Http\Controllers;

use App\Exports\MutationMultiSheetExport;
use App\Exports\PartImportTemplateExport;
use App\Exports\StockImportTemplateExport;
use App\Imports\ManifestImport;
use App\Imports\PartImport;
use App\Imports\StockImport;
use App\Models\Customer;
use App\Models\InternalPart;
use App\Models\KanbanPairingLog;
use App\Models\LineStaticSequence;
use App\Models\ProductionPlan;
use App\Models\ScannedPart;
use App\Models\Supplier;
use App\Services\Ai\BellaContextBuilder;
use App\Traits\prodPlanOps;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessKanbanUpload;

class DashboardController extends Controller
{
    use prodPlanOps;

    private const PRODUCTION_RESULT_TYPES = ['supply', 'as547_kanan', 'as547_kiri'];

    public function index()
    {
        // Portal (launcher) untuk semua. Blade sembunyikan tombol Login kalau sudah login.
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
            'scanners' => $request->user()->can('manage-device-sessions') ? $this->scannerSummary() : null,
        ])->header('Cache-Control', 'private, max-age=30');
    }

    /** Ringkasan status device scanner pulling (panel monitoring ringkas di overview). */
    private function scannerSummary(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('device_sessions')) {
            return ['online' => 0, 'total' => 0, 'list' => []];
        }

        $devices = DB::table('device_sessions')
            ->join('users', 'users.id', '=', 'device_sessions.user_id')
            ->where('device_sessions.current_page', '/pulling')
            ->where('device_sessions.last_seen_at', '>=', now()->subHours(3))
            ->orderByDesc('device_sessions.last_seen_at')
            ->limit(24)
            ->get([
                'device_sessions.device_id',
                'device_sessions.device_label',
                'device_sessions.scanner_no',
                'device_sessions.last_seen_at',
                'users.name as user_name',
                'users.npk as user_npk',
            ]);

        $online = 0;
        $list = $devices->map(function ($d) use (&$online) {
            $lastSeen = $d->last_seen_at ? Carbon::parse($d->last_seen_at) : null;
            $isOnline = $lastSeen && $lastSeen->gt(now()->subMinutes(2));
            if ($isOnline) {
                $online++;
            }

            return [
                'name' => $d->device_label
                    ?: ($d->scanner_no ? 'Scanner #'.$d->scanner_no : 'Scanner #'.substr($d->device_id, 0, 6)),
                'user_name' => $d->user_name ?: '-',
                'user_npk' => $d->user_npk ?: null,
                'online' => $isOnline,
                'last_seen' => $lastSeen ? $lastSeen->diffForHumans() : '-',
            ];
        })->all();

        // online dulu (usort stabil di PHP 8 → urutan last_seen dalam grup dipertahankan)
        usort($list, fn ($a, $b) => $b['online'] <=> $a['online']);

        return ['online' => $online, 'total' => count($list), 'list' => $list];
    }

    public function overviewAiAnalyst(Request $request, BellaContextBuilder $contextBuilder)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:700',
            'history' => 'array|max:8',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:5000',
            'answer_style' => 'nullable|string|in:concise,detailed',
        ]);

        $apiKey = (string) config('services.groq.key');
        if ($apiKey === '') {
            return response()->json([
                'message' => 'GROQ_API_KEY belum diset di environment.',
            ], 422);
        }

        [$from, $to] = $this->overviewRange($request);
        $question = trim($validated['question']);
        $answerStyle = $validated['answer_style'] ?? 'concise';
        $styleInstruction = $answerStyle === 'detailed'
            ? 'Jawab dalam Bahasa Indonesia yang lebih detail, tetap natural, actionable, dan tidak mengarang data.'
            : 'Jawab dalam Bahasa Indonesia yang ringkas, natural, dan actionable.';

        // Periode relatif (bulan kemarin, minggu lalu, …) mengalahkan filter dashboard.
        $questionPeriod = $contextBuilder->explicitQuestionPeriod($question);
        if ($questionPeriod !== null) {
            [$from, $to] = $questionPeriod;
            $from = $from->copy()->startOfDay();
            $to = $to->copy()->startOfDay();
        } else {
            // Tanggal tunggal (mis. "1 Juli 2026") → fokus 1 hari.
            $explicitDate = $contextBuilder->explicitQuestionDate($question);
            if ($explicitDate !== null) {
                $from = $explicitDate->copy()->startOfDay();
                $to = $explicitDate->copy()->startOfDay();
            }
        }

        if ($from->diffInDays($to) > 45) {
            $from = $to->copy()->subDays(45)->startOfDay();
        }

        $overview = $this->buildOverviewData($from, $to);
        $context = $contextBuilder->build($overview, $from, $to, $question);
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
                    $styleInstruction,
                    '',
                    'ATURAN KETAT (wajib):',
                    '- Hanya gunakan angka, nama, dan status yang ADA persis di JSON system_context. Dilarang mengarang, mengestimasi, atau mengisi data kosong.',
                    '- Untuk pertanyaan ranking/terbanyak/paling banyak: prioritaskan insights.* dan modules.*.fokus_tanggal.ranking_by_qty — jawab langsung dari top_1 atau items[0].',
                    '- Untuk pertanyaan pulling jumlah 0 / hari tanpa pulling (tabel pullings): prioritaskan insights.pulling_hari_nol.',
                    '- Istilah "pulling" punya 2 arti — WAJIB bedakan: (1) "berapa pulling [hari/tanggal]" tanpa menyebut loading list = checkout kanban (mutations) → insights.pulling_metrik.checkout_kanban.total; (2) "berapa loading list yang di pulling/di-pull" = tabel pullings → insights.pulling_metrik.loading_list_di_pulling.jumlah_distinct. Ikuti insights.pulling_metrik.intent_terdeteksi dan rekomendasi_jawaban.',
                    '- Jangan buat daftar panjang modul/field yang "belum tersedia". Hanya sebut data spesifik yang benar-benar dibutuhkan user dan tidak ada di konteks.',
                    '- petunjuk_data dan overview.kpi_labels hanya kamus arti field — BUKAN checklist yang wajib dilaporkan.',
                    '- Bedakan sumber: insights = jawaban siap pakai; ringkasan_operasional/overview.kpi = agregat periode filter; modules.* = detail modul; search_hits = pencarian keyword; referensi = daftar nilai valid (line, customer, area error, judgement) untuk memetakan istilah user & menjawab "apa saja/daftar X".',
                    '- Untuk pertanyaan spesifik (nomor PDS, loading list, back number), prioritaskan search_hits.matches lalu modules terkait.',
                    '- Jangan menyebut Groq, API, token, atau istilah teknis backend.',
                    '',
                    'Kamu bisa menganalisis: dashboard, loading list, PDS, pulling, produksi, stok, PIS, receiving, quality, error, preparation deadline.',
                    'Jika system_context.preparation_deadline tersedia, jadikan sumber utama untuk pertanyaan deadline preparation. Telat = pulling_date > deadline_preparation.',
                    'Jika user minta detail di luar konteks, minta filter spesifik: nomor loading list/PDS/back number/customer/tanggal.',
                    'Saat rekomendasi, sebut alasan singkat berdasarkan data konteks.',
                    'Format jawaban: Markdown sederhana — **tebal** untuk label, bullet "-" atau "*". Hindari simbol "+".',
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
                    'temperature' => 0.15,
                    'max_tokens' => $answerStyle === 'detailed' ? 760 : 520,
                ]);
        } catch (\Throwable $e) {
            $err = $this->groqErrorPayload($e->getMessage());

            return response()->json($err, 502);
        }

        if ($response->failed()) {
            $raw = (string) data_get($response->json(), 'error.message', 'Groq mengembalikan error.');
            $err = $this->groqErrorPayload($raw, $response->status());

            return response()->json($err, 502);
        }

        $answer = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($answer === '') {
            $answer = 'Maaf, BELLA belum sempat menyusun jawabannya. Coba tanyakan ulang dengan pertanyaan yang lebih spesifik ya.';
        }

        return response()->json([
            'answer' => $answer,
            'model' => (string) data_get($response->json(), 'model', config('services.groq.model')),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'actions' => $contextBuilder->actionLinks($question, $context),
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
                ->filter(fn ($row) => in_array($row->urgency, ['overdue', 'urgent', 'soon'], true))
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

    public function bellaNotifications()
    {
        $today = Carbon::today();
        $cacheKey = 'bella_notifications_'.$today->toDateString();

        $payload = Cache::remember($cacheKey, 300, function () use ($today) {
            $overview = $this->buildOverviewData($today, $today);
            $brief = app(\App\Services\Ai\BellaBriefService::class)->build($overview, $today);

            return [
                'notifications' => [$brief],
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($payload)->header('Cache-Control', 'private, max-age=60');
    }

    private function groqErrorPayload(string $raw, int $status = 502): array
    {
        $retryAfter = $this->groqRetrySeconds($raw, $status);

        return [
            'message' => $this->humanizeGroqError($raw, $status),
            'retry_after' => $retryAfter,
            'rate_limited' => $retryAfter !== null,
        ];
    }

    private function groqRetrySeconds(string $raw, int $status = 502): ?int
    {
        if (preg_match('/try again in ([\d.]+)\s*s/i', $raw, $m)) {
            return max(1, (int) ceil((float) $m[1]));
        }

        $lower = strtolower($raw);
        if ($status === 429
            || str_contains($lower, 'rate limit')
            || str_contains($lower, 'tokens per minute')
            || str_contains($lower, 'tpm')
            || str_contains($lower, 'too many requests')) {
            return 15;
        }

        return null;
    }

    private function humanizeGroqError(string $raw, int $status = 502): string
    {
        $msg = trim($raw);
        $lower = strtolower($msg);

        if (preg_match('/try again in ([\d.]+)\s*s/i', $msg, $m)) {
            $seconds = max(1, (int) ceil((float) $m[1]));

            return "Maaf ya, BELLA lagi agak penuh barusan. Coba tanya lagi sekitar {$seconds} detik—biasanya langsung lancar.";
        }

        if (str_contains($lower, 'rate limit')
            || str_contains($lower, 'tokens per minute')
            || str_contains($lower, 'tpm')
            || str_contains($lower, 'too many requests')) {
            return 'Maaf, BELLA lagi sibuk dipakai berkali-kali. Tunggu sebentar (~15 detik) lalu coba lagi ya.';
        }

        if (str_contains($lower, 'request too large')
            || str_contains($lower, 'reduce your message size')
            || str_contains($lower, 'context length')) {
            return 'Pertanyaannya agak banyak datanya nih. Coba lebih spesifik—misalnya sebut nomor PDS, loading list, atau tanggal—supaya BELLA bisa bantu lebih cepat.';
        }

        if (str_contains($lower, 'invalid api key')
            || str_contains($lower, 'incorrect api key')
            || str_contains($lower, 'authentication')) {
            return 'Sistem AI belum siap dipakai. Hubungi admin untuk cek konfigurasi API key-nya ya.';
        }

        if (str_contains($lower, 'timeout')
            || str_contains($lower, 'timed out')
            || str_contains($lower, 'curl error 28')) {
            return 'BELLA agak lama merespons barusan. Coba kirim ulang pertanyaannya ya—koneksi mungkin sedang lambat.';
        }

        if (str_contains($lower, 'could not resolve host')
            || str_contains($lower, 'connection refused')
            || str_contains($lower, 'failed to connect')
            || str_contains($lower, 'ssl')
            || str_contains($lower, 'certificate')) {
            return 'BELLA belum bisa nyambung ke server AI. Cek koneksi internet atau coba lagi sebentar ya.';
        }

        if ($status === 429) {
            return 'Maaf, BELLA lagi agak penuh. Tunggu sebentar lalu coba tanya lagi ya.';
        }

        if ($status >= 500) {
            return 'Ada kendala teknis di server AI barusan. Coba kirim ulang pertanyaannya—BELLA siap bantu.';
        }

        return 'Maaf, BELLA belum bisa memproses pertanyaan itu. Coba lagi sebentar ya.';
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

        $insights = $context['insights'] ?? [];
        unset($context['modules']);
        $context['insights'] = $insights;
        if ($fits($context)) {
            $context['scope']['catatan'] = ($context['scope']['catatan'] ?? '').' Modul detail di-skip agar muat limit token Groq. Gunakan insights untuk pertanyaan umum.';

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
        unset($context['petunjuk_data']);
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

    /**
     * Halaman Production Stock (sebelumnya ada di index()).
     */
    public function productionStock(Request $request)
    {
        $lineNames = DB::table('lines')
            ->join('internal_parts', 'internal_parts.line_id', '=', 'lines.id')
            ->select('lines.name')
            ->distinct()
            ->orderBy('lines.name')
            ->pluck('name')
            ->map(fn ($line) => (string) $line);

        $requestedLine = trim((string) $request->query('line', ''));
        $focusBackNumber = trim((string) $request->query('back_number', ''));
        $activeLine = $lineNames->first();

        if ($requestedLine !== '' && $lineNames->contains($requestedLine)) {
            $activeLine = $requestedLine;
        } elseif ($focusBackNumber !== '') {
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

    public function productionPairingLogs(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $logs = KanbanPairingLog::query()
            ->leftJoin('body_kanban_pairings as pairing_master', 'pairing_master.id', '=', 'kanban_pairing_logs.body_kanban_pairing_id')
            ->select([
                'kanban_pairing_logs.*',
                'pairing_master.assembly_back_number',
                'pairing_master.painting_back_number',
            ])
            ->when($request->filled('project'), fn ($query) => $query->where('kanban_pairing_logs.project', $request->query('project')))
            ->when(in_array((string) $status, ['0', '1'], true), fn ($query) => $query->where('kanban_pairing_logs.status', (int) $status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('kanban_pairing_logs.paired_at', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('kanban_pairing_logs.paired_at', '<=', $request->query('date_to')))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($query) use ($like) {
                    $query->where('kanban_pairing_logs.assembly_part', 'like', $like)
                        ->orWhere('kanban_pairing_logs.painting_part', 'like', $like)
                        ->orWhere('kanban_pairing_logs.assembly_serial_number', 'like', $like)
                        ->orWhere('kanban_pairing_logs.painting_serial_numbers', 'like', $like)
                        ->orWhere('kanban_pairing_logs.paired_by', 'like', $like)
                        ->orWhere('kanban_pairing_logs.consumed_by', 'like', $like)
                        ->orWhere('pairing_master.assembly_back_number', 'like', $like)
                        ->orWhere('pairing_master.painting_back_number', 'like', $like);
                });
            })
            ->orderByDesc('kanban_pairing_logs.paired_at')
            ->orderByDesc('kanban_pairing_logs.id')
            ->paginate(50)
            ->withQueryString();

        return view('pages.production.pairingLogs', [
            'logs' => $logs,
            'projects' => KanbanPairingLog::query()
                ->whereNotNull('project')
                ->distinct()
                ->orderBy('project')
                ->pluck('project'),
            'statusOptions' => [
                KanbanPairingLog::STATUS_READY => 'Ready',
                KanbanPairingLog::STATUS_CONSUMED => 'Consumed',
            ],
        ]);
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

    public function downloadStockImportTemplate()
    {
        return Excel::download(
            new StockImportTemplateExport(),
            'template import stock BELLA.xlsx'
        );
    }

    public function exportMutation()
    {
        $tz = 'Asia/Jakarta';
        $productionResultExport = request()->boolean('production_result');

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
                DB::raw('COALESCE(mutations.is_adjustment, 0) as is_adjustment'),
                'mutations.date as mutation_date'
            )
            ->whereBetween('mutations.date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->whereIn(
                'mutations.type',
                $productionResultExport ? self::PRODUCTION_RESULT_TYPES : ['supply']
            )
            ->orderBy('lines.name')
            ->orderBy('mutations.date')
            ->orderBy('internal_parts.back_number')
            ->get();

        $filename = ($productionResultExport ? 'Production_Result_' : 'Mutation_')
            .$start->format('Ymd').'_'.$end->format('Ymd').'.xlsx';

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
            ->whereIn('mutations.type', self::PRODUCTION_RESULT_TYPES)
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
            ->whereIn('mutations.type', self::PRODUCTION_RESULT_TYPES)
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
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Silakan pilih file Master Part terlebih dahulu.',
            'file.mimes' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file Master Part maksimal 10 MB.',
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
            $import = new StockImport(auth()->user()->npk ?? 'SYS');

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
        $endOfWeek = request('end_date') ? Carbon::parse(request('end_date')) : now()->endOfWeek();
        // dd($startOfWeek->toDateString(), $endOfWeek->toDateString());

        // Belum pilih area -> tampilkan semua; unitbody ikut di kedua view
        $area = in_array(request('area'), ['unit', 'body']) ? request('area') : null;
        $areaFilter = $area ? [$area, 'unitbody'] : null;
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
            ->when($areaFilter, fn ($q) => $q->whereIn('suppliers.area', $areaFilter))
            ->get();

        // Rule 2: ambil semua supplier sesuai area agar row tetap tampil meski kosong
        $allSuppliers = DB::table('suppliers')
            ->when($areaFilter, fn ($q) => $q->whereIn('area', $areaFilter))
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

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
                'status' => (int) ($delivery->status ?? 0),
            ];
        }

        // Rule 4: jika 1 supplier punya data berjarak 0-30 menit, gabung jadi 1 kotak
        $clustered = [];
        foreach (collect($seriesData)->groupBy('x') as $supplierName => $points) {
            $sorted = $points->sortBy(fn ($p) => $p['y'][0])->values();
            $cluster = [];
            $prevStart = null;
            $flush = function ($cluster) use (&$clustered, $supplierName) {
                if (empty($cluster)) {
                    return;
                }
                if (count($cluster) === 1) {
                    $clustered[] = $cluster[0];
                    return;
                }
                $starts = array_column(array_column($cluster, 'y'), 0);
                $ends = array_column(array_column($cluster, 'y'), 1);
                $group = [];
                foreach ($cluster as $c) {
                    $group[] = [
                        'pick_list' => $c['pick_list'],
                        'start' => $c['y'][0],
                        'end' => $c['y'][1],
                        'status' => $c['status'] ?? 0,
                    ];
                }
                $clustered[] = [
                    'x' => $supplierName,
                    'y' => [min($starts), max($ends)],
                    'fillColor' => $cluster[0]['fillColor'],
                    'pick_list' => $cluster[0]['pick_list'],
                    'isMerged' => true,
                    'group' => $group,
                    'meta' => count($cluster).' pengiriman',
                ];
            };
            foreach ($sorted as $p) {
                if ($prevStart === null || ($p['y'][0] - $prevStart) <= 30 * 60 * 1000) {
                    $cluster[] = $p;
                } else {
                    $flush($cluster);
                    $cluster = [$p];
                }
                $prevStart = $p['y'][0];
            }
            $flush($cluster);
        }
        $seriesData = $clustered;

        // Rule 2: tambah dummy transparan agar supplier tanpa data tetap punya row
        $filledSuppliers = collect($seriesData)->pluck('x')->unique()->values();
        $emptyAnchor = $startOfWeek->copy()->startOfDay()->timestamp * 1000;
        foreach ($allSuppliers as $supplierName) {
            if (! $filledSuppliers->contains($supplierName)) {
                $seriesData[] = [
                    'x' => $supplierName,
                    'y' => [$emptyAnchor, $emptyAnchor],
                    'fillColor' => 'transparent',
                    'pick_list' => null,
                    'isEmpty' => true,
                ];
            }
        }

        // Rule 3: urut supplier paling pagi di atas (contoh jam 10 di atas jam 11)
        $grouped = collect($seriesData)->groupBy('x');
        $earliest = [];
        foreach ($grouped as $supplierName => $points) {
            $real = $points->filter(fn ($p) => empty($p['isEmpty']));
            $earliest[$supplierName] = $real->isNotEmpty() ? $real->min(fn ($p) => $p['y'][0]) : PHP_INT_MAX;
        }
        $sortedSuppliers = collect($earliest)->sortBy(fn ($time, $name) => [$time === PHP_INT_MAX ? 1 : 0, $time, $name])->keys()->values();
        $orderedData = [];
        foreach ($sortedSuppliers as $supplierName) {
            $points = $grouped[$supplierName]->sortBy(fn ($p) => $p['y'][0])->values()->all();
            foreach ($points as $p) {
                $orderedData[] = $p;
            }
        }
        $seriesData = $orderedData;

        $series = [[
            'name' => 'Pengiriman Aktual',
            'data' => $seriesData,
        ]];

        // Filter via AJAX (mode fullscreen): kembalikan JSON tanpa reload halaman
        // agar browser tidak keluar fullscreen.
        if (request()->boolean('ajax')) {
            return response()->json([
                'series' => $series,
                'start_date' => $startOfWeek->toDateString(),
                'end_date' => $endOfWeek->toDateString(),
            ]);
        }
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

        if (! $ia31nt) {
            return response()->json([]);
        }

        $data = DB::connection('mssql_external')
            ->table('IA16NT as a')
            ->where('a.DEC_COD_BINID', $ia31nt->flight)
            ->select(
                'a.CHR_NUB_MLNO as supplier_code',
                'a.CHR_COD_HINB as part_number',
                'a.DEC_SUR_KKSUH as qty_ordered',
                'a.DEC_SUR_KKSUK as qty_per_kanban_raw',
                'a.CHR_INF_HTTN as uom',
                'a.CHR_INF_BHTTN as uom_kbn'
            )
            ->get();

        $partNumbers = $data->pluck('part_number')->filter()->unique()->values()->all();
        $qtyPerKanbanMap = collect();
        if (! empty($partNumbers)) {
            $qtyPerKanbanMap = DB::table('customer_parts')
                ->whereIn('part_number', $partNumbers)
                ->get(['part_number', 'back_number', 'qty_per_kanban'])
                ->groupBy('part_number');
        }

        $trimNumber = function ($value) {
            if ($value === null || $value === '') {
                return null;
            }
            if (! is_numeric($value)) {
                return $value;
            }
            $float = (float) $value;
            // Hilangkan .0000 / trailing zero: 100.0000 -> 100, 10.5000 -> 10.5
            $str = rtrim(rtrim(sprintf('%.4F', $float), '0'), '.');

            return $str === '-0' ? '0' : $str;
        };

        $data = $data->map(function ($item) use ($pickList, $qtyPerKanbanMap, $trimNumber) {
            $item->pick_list = $pickList;

            // IAA1NT: coba picklist+part dulu (baris picklist tsb), fallback part saja (master).
            // DEC_SUR_SHSU di sini = Quantity/Kanban (cth: 25.00 untuk AM-8030F).
            $iaa1nt = DB::connection('mssql_external')
                ->table('IAA1NT as a')
                ->where('a.CHR_NUB_NYSJNO', $pickList)
                ->where('a.CHR_COD_HINB', $item->part_number)
                ->select(
                    'a.CHR_NUB_SBNG as back_number',
                    'a.DEC_SUR_SHSU as shsu',
                    'a.DEC_SUR_HSSU as hssu'
                )
                ->first();
            if (! $iaa1nt) {
                $iaa1nt = DB::connection('mssql_external')
                    ->table('IAA1NT as a')
                    ->where('a.CHR_COD_HINB', $item->part_number)
                    ->select(
                        'a.CHR_NUB_SBNG as back_number',
                        'a.DEC_SUR_SHSU as shsu',
                        'a.DEC_SUR_HSSU as hssu'
                    )
                    ->first();
            }

            $item->back_number = $iaa1nt->back_number ?? null; // Menambahkan custom2 dengan part_number
            $item->uom = $item->uom ? trim((string) $item->uom) : $item->uom;
            $item->uom_kbn = isset($item->uom_kbn) ? trim((string) $item->uom_kbn) : null;

            $qtyOrderedFloat = is_numeric($item->qty_ordered) ? (float) $item->qty_ordered : null;
            $qtyPerKanbanRaw = isset($item->qty_per_kanban_raw) && is_numeric($item->qty_per_kanban_raw)
                ? (float) $item->qty_per_kanban_raw
                : null;
            unset($item->qty_per_kanban_raw);
            $shsuFloat = isset($iaa1nt->shsu) && is_numeric($iaa1nt->shsu)
                ? (float) $iaa1nt->shsu
                : null;

            $item->qty_ordered = $trimNumber($item->qty_ordered);

            // Quantity/Kanban: IA16NT.DEC_SUR_KKSUK dulu, lalu IAA1NT.DEC_SUR_SHSU,
            // terakhir master lokal customer_parts (KKSUK terbukti .0000 bahkan di data 2026).
            $qtyPerKanban = ($qtyPerKanbanRaw !== null && $qtyPerKanbanRaw > 0)
                ? $qtyPerKanbanRaw
                : null;
            if ($qtyPerKanban === null && $shsuFloat !== null && $shsuFloat > 0) {
                $qtyPerKanban = $shsuFloat;
            }
            if ($qtyPerKanban === null && isset($qtyPerKanbanMap[$item->part_number])) {
                $candidates = $qtyPerKanbanMap[$item->part_number];
                $matched = $item->back_number
                    ? $candidates->firstWhere('back_number', $item->back_number)
                    : null;
                $fallback = $matched->qty_per_kanban ?? $candidates->first()->qty_per_kanban ?? null;
                if (is_numeric($fallback) && (float) $fallback > 0) {
                    $qtyPerKanban = (float) $fallback;
                }
            }
            $item->qty_per_kanban = ($qtyPerKanban !== null && $qtyPerKanban !== '')
                ? $trimNumber($qtyPerKanban)
                : null;

            // QTY Ordered (KBN) = qty_ordered / qty_per_kanban (cth: 1800 / 300 = 6)
            if ($qtyOrderedFloat !== null && $qtyPerKanban !== null && (float) $qtyPerKanban > 0) {
                $item->qty_ordered_kbn = $trimNumber($qtyOrderedFloat / (float) $qtyPerKanban);
            } else {
                $item->qty_ordered_kbn = null;
            }

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

    public function kanbanUpload(Request $request)
    {
        $customer = strtoupper((string) $request->query('customer', 'TMMIN'));
        if (! in_array($customer, ['TMMIN', 'MMKI', 'ALL'], true)) {
            $customer = 'TMMIN';
        }

        return view('pages.kanban.upload', compact('customer'));
    }

    public function kanbanUploadProcess(Request $request)
{

    $request->validate([

        'files'=>'required|array',

        'files.*'=>'mimes:pdf|max:20480',

        'customer'=>'nullable|in:TMMIN,MMKI,ALL'

    ]);


    $customer = strtoupper((string) $request->input('customer', 'TMMIN'));
    if (! in_array($customer, ['TMMIN', 'MMKI', 'ALL'], true)) {
        $customer = 'TMMIN';
    }


    $jobId = (string) Str::uuid();



    $paths = [];

    $basenames = [];

    $originals = [];

    $usedNames = [];


    foreach($request->file('files') as $file)
    {

        // Simpan dengan NAMA ASLI (disanitasi), bukan hash acak — agar di future
        // bisa di-fetch berdasarkan nama file yang tampil di dashboard delivery
        // (bar PDS / manifest).
        [$storedName, $originalName] = $this->resolveKanbanStoredName($file, $usedNames);

        $path =
            $file->storeAs(
                'temp-kanban',
                $storedName
            );


        $paths[] =
            storage_path(
                'app/'.$path
            );

        $basenames[] = basename($path);

        $originals[basename($path)] = $originalName;

    }



    // Meta job untuk tombol Cancel total (lacak file input tanpa ubah logic PDF).
    // 'originals' dipakai job untuk menamai arsip HASIL generate di kanban-source.
    @file_put_contents(
        storage_path('app/temp-kanban/'.$jobId.'.meta.json'),
        json_encode([
            'customer' => $customer,
            'files' => array_values($basenames ?? []),
            'originals' => $originals,
            'reverse' => $request->boolean('reverse', true),
            'created_at' => now()->toDateTimeString(),
        ])
    );



    ProcessKanbanUpload::dispatch(

        $paths,

        $jobId,

        $customer

    );



    return response()->json([

        'status'=>true,

        'job_id'=>$jobId,

        'customer'=>$customer,

        'files'=>array_values($basenames ?? []),

        'originals'=>$originals,

        'message'=>
            'PDF processing started'

    ]);

}

    /**
     * Tentukan nama simpan yang aman berbasis NAMA ASLI upload.
     * Return [storedName, originalName]. Tabrakan (antar file se-request maupun
     * sisa file lama di storage) diatasi dengan suffix _2, _3, ... agar tidak
     * menimpa file lain. Validasi mimes:pdf sudah menjamin ekstensi PDF.
     * Nama ini dipakai job untuk menamai arsip HASIL generate di kanban-source.
     */
    private function resolveKanbanStoredName($file, array &$used): array
    {
        $original = basename(str_replace('\\', '/', (string) $file->getClientOriginalName()));
        $stem = pathinfo($original, PATHINFO_FILENAME);
        $stem = (string) preg_replace('/[^A-Za-z0-9._-]+/', '_', $stem);
        $stem = trim($stem, '._');
        if ($stem === '') {
            $stem = 'kanban';
        }
        $stem = substr($stem, 0, 120);

        $candidate = $stem.'.pdf';
        $i = 1;
        while (isset($used[$candidate]) || Storage::exists('temp-kanban/'.$candidate)) {
            $i++;
            $suffix = '_'.$i;
            $candidate = substr($stem, 0, 120 - strlen($suffix)).$suffix.'.pdf';
        }
        $used[$candidate] = true;

        return [$candidate, $original !== '' ? $original : $candidate];
    }

    /**
     * Proses MANUAL khusus tab ALL (crop garis merah draggable).
     * TERISOLASI: tidak mengubah kanbanUploadProcess (TMMIN/MMKI/ALL-auto).
     */
    public function kanbanAllManualProcess(Request $request)
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'mimes:pdf|max:20480',
            'cuts' => 'nullable|string',
            'vcuts' => 'nullable|string',
        ]);
        $cuts = [];
        if ($request->filled('cuts')) {
            try {
                $decoded = json_decode((string) $request->input('cuts'), true);
                if (is_array($decoded)) {
                    $cuts = $decoded;
                }
            } catch (\Throwable $e) {
                $cuts = [];
            }
        }
        $vCuts = [];
        if ($request->filled('vcuts')) {
            try {
                $decodedV = json_decode((string) $request->input('vcuts'), true);
                if (is_array($decodedV)) {
                    $vCuts = $decodedV;
                }
            } catch (\Throwable $e) {
                $vCuts = [];
            }
        }

        $jobId = (string) Str::uuid();
        $paths = [];
        $basenames = [];
        $originals = [];
        $usedNames = [];

        foreach ($request->file('files') as $file) {
            // Sama seperti flow auto: pertahankan nama asli (disanitasi).
            // Nama ini dipakai job untuk menamai arsip HASIL crop di kanban-source.
            [$storedName, $originalName] = $this->resolveKanbanStoredName($file, $usedNames);
            $path = $file->storeAs('temp-kanban', $storedName);
            $paths[] = storage_path('app/'.$path);
            $basenames[] = basename($path);
            $originals[basename($path)] = $originalName;
        }

        @file_put_contents(
            storage_path('app/temp-kanban/'.$jobId.'.meta.json'),
            json_encode([
                'customer' => 'ALL-MANUAL',
                'files' => array_values($basenames),
                'originals' => $originals,
                'cuts' => $cuts,
                'vcuts' => $vCuts,
                'reverse' => $request->boolean('reverse', true),
                'created_at' => now()->toDateTimeString(),
            ])
        );

        \App\Jobs\ProcessAllManualCrop::dispatch($paths, $jobId, $cuts, $request->boolean('skip_blank', true), $vCuts);

        return response()->json([
            'status' => true,
            'job_id' => $jobId,
            'customer' => 'ALL',
            'message' => 'PDF manual crop started',
        ]);
    }

    /**
     * Terima satu bagian (chunk) PDF untuk tab MANUAL file raksasa.
     * TERISOLASI: hanya dipakai flow chunked manual.
     */
    public function kanbanAllChunkUpload(Request $request)
    {
        $request->validate([
            'uploadId' => ['required', 'string', 'regex:/^[A-Za-z0-9\-]{8,64}$/'],
            'fileIndex' => 'required|integer|min:0|max:99',
            'chunkIndex' => 'required|integer|min:0|max:9999',
            'totalChunks' => 'required|integer|min:1|max:10000',
            'fileName' => 'required|string|max:255',
            'chunk' => 'required|file|mimes:pdf|max:20480',
        ]);

        $uploadId = basename((string) $request->input('uploadId'));
        $fi = (int) $request->input('fileIndex');
        $ci = (int) $request->input('chunkIndex');

        $name = 'chunk_'.$uploadId.'_'.$fi.'_'.$ci.'.pdf';
        $request->file('chunk')->storeAs('temp-kanban', $name);

        return response()->json(['status' => true]);
    }

    /**
     * Finalisasi upload chunk: verifikasi kelengkapan lalu dispatch job gabung+crop.
     * TERISOLASI: hanya dipakai flow chunked manual.
     */
    public function kanbanAllChunksFinalize(Request $request)
    {
        $request->validate([
            'uploadId' => ['required', 'string', 'regex:/^[A-Za-z0-9\-]{8,64}$/'],
            'files' => 'required|string',
            'cuts' => 'nullable|string',
            'vcuts' => 'nullable|string',
        ]);

        $uploadId = basename((string) $request->input('uploadId'));

        $specs = json_decode((string) $request->input('files'), true);
        if (! is_array($specs) || empty($specs)) {
            return response()->json(['status' => false, 'message' => 'Spesifikasi file tidak valid.'], 422);
        }

        $cuts = [];
        if ($request->filled('cuts')) {
            $decoded = json_decode((string) $request->input('cuts'), true);
            if (is_array($decoded)) {
                $cuts = $decoded;
            }
        }

        $vCuts = [];
        if ($request->filled('vcuts')) {
            $decodedV = json_decode((string) $request->input('vcuts'), true);
            if (is_array($decodedV)) {
                $vCuts = $decodedV;
            }
        }

        $dir = storage_path('app/temp-kanban');
        $basenames = [];

        // Kumpulkan chunk per file + hitung offset halaman global.
        // $filePages[fi] = total halaman file; $chunkPagesAll[fi] = halaman per chunk.
        $fileList = [];
        $originalFiles = [];
        $globalBase = 0;
        foreach ($specs as $sp) {
            $fi = (int) ($sp['fileIndex'] ?? 0);
            $n = (int) ($sp['chunks'] ?? 0);
            if ($n < 1 || $n > 10000) {
                return response()->json(['status' => false, 'message' => 'Jumlah chunk tidak valid.'], 422);
            }
            $cpages = (isset($sp['chunkPages']) && is_array($sp['chunkPages'])) ? array_values($sp['chunkPages']) : null;
            $totalPages = (int) ($sp['pages'] ?? 0);
            $chunks = [];
            for ($ci = 0; $ci < $n; $ci++) {
                $base = 'chunk_'.$uploadId.'_'.$fi.'_'.$ci.'.pdf';
                if (! is_file($dir.'/'.$base)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Chunk hilang: file '.$fi.' bagian '.($ci + 1).'/'.$n.'. Ulangi upload.',
                    ], 422);
                }
                $chunks[] = [
                    'path' => $dir.'/'.$base,
                    'pages' => ($cpages !== null && isset($cpages[$ci])) ? max(1, (int) $cpages[$ci]) : null,
                ];
                $basenames[] = $base;
            }
            $fileList[] = [
                'fileIndex' => $fi,
                'chunks' => $chunks,
                'globalBase' => $globalBase, // halaman global pertama file ini - 1
            ];
            // Pertahankan nama asli untuk future fetch berdasarkan nama file.
            $origName = $sp['fileName'] ?? $sp['name'] ?? null;
            if (is_string($origName) && trim($origName) !== '') {
                $originalFiles[$fi] = basename(str_replace('\\', '/', trim($origName)));
            }
            $globalBase += $totalPages > 0 ? $totalPages : array_sum(array_map(
                static fn ($c) => $c['pages'] ?? 0,
                $chunks
            ));
        }

        // Canary: pastikan chunk pertama bisa dibaca FPDI sebelum dispatch.
        // Gagal = error jelas (422), bukan PDF hasil kosong.
        try {
            $probe = new \setasign\Fpdi\Tcpdf\Fpdi();
            $firstPath = $fileList[0]['chunks'][0]['path'] ?? null;
            if (! is_string($firstPath) || ! is_file($firstPath)) {
                throw new \Exception('Chunk tidak ditemukan.');
            }
            $probe->setSourceFile($firstPath);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Chunk tidak bisa dibaca server ('.$e->getMessage().'). Refresh halaman lalu ulangi upload.',
            ], 422);
        }

        // Bagi jadi part jobs @ ±100 halaman sumber agar tiap job ringan.
        // Chunk tanpa info halaman (klien lama) = 1 part per file (tetap lebih ringan dari 1 job semua).
        $BATCH_PAGES = 100;
        $parts = [];
        foreach ($fileList as $f) {
            $fi = (int) $f['fileIndex'];
            $known = true;
            foreach ($f['chunks'] as $c) {
                if (empty($c['pages'])) {
                    $known = false;
                    break;
                }
            }
            if (! $known) {
                $paths = array_map(static fn ($c) => $c['path'], $f['chunks']);
                $parts[] = ['fileIndex' => $fi, 'chunks' => $paths, 'cutStart' => (int) $f['globalBase'] + 1];
                continue;
            }
            $acc = [];
            $accPages = 0;
            $accStart = (int) $f['globalBase'] + 1;
            $consumed = 0;
            foreach ($f['chunks'] as $c) {
                if (! empty($acc) && $accPages + (int) $c['pages'] > $BATCH_PAGES) {
                    $parts[] = ['fileIndex' => $fi, 'chunks' => $acc, 'cutStart' => $accStart];
                    $acc = [];
                    $accPages = 0;
                    $accStart = (int) $f['globalBase'] + 1 + $consumed;
                }
                $acc[] = $c['path'];
                $accPages += (int) $c['pages'];
                $consumed += (int) $c['pages'];
            }
            if (! empty($acc)) {
                $parts[] = ['fileIndex' => $fi, 'chunks' => $acc, 'cutStart' => $accStart];
            }
        }

        if (empty($parts)) {
            return response()->json(['status' => false, 'message' => 'Tidak ada chunk untuk diproses.'], 422);
        }

        $jobId = (string) Str::uuid();
        $skipBlank = $request->boolean('skip_blank', true);

        // Daftarkan semua file sementara (chunk + parsial deterministik) untuk tombol cancel.
        $partTotal = count($parts);
        for ($i = 0; $i < $partTotal; $i++) {
            $subId = $jobId.'_part'.$i;
            $basenames[] = 'kanban_all_manual_'.$subId.'.pdf';
            $basenames[] = $subId.'.done';
            $basenames[] = $subId.'.info.json';
        }
        $basenames[] = $jobId.'.progress.json';

        @file_put_contents(
            $dir.'/'.$jobId.'.meta.json',
            json_encode([
                'customer' => 'ALL-MANUAL-CHUNKED',
                'files' => array_values($basenames),
                'original_files' => $originalFiles,
                'cuts' => $cuts,
                'vcuts' => $vCuts,
                'reverse' => $request->boolean('reverse', true),
                'created_at' => now()->toDateTimeString(),
            ])
        );

        @file_put_contents(
            $dir.'/'.$jobId.'.progress.json',
            json_encode(['done' => 0, 'total' => $partTotal])
        );

        $chain = [];
        foreach ($parts as $i => $g) {
            $chain[] = new \App\Jobs\ProcessAllManualPart($g, $jobId, $i, $partTotal, $cuts, $skipBlank, $vCuts);
        }
        $chain[] = new \App\Jobs\MergeAllManualParts($jobId, $partTotal);

        \Illuminate\Support\Facades\Bus::chain($chain)->dispatch();

        return response()->json([
            'status' => true,
            'job_id' => $jobId,
            'customer' => 'ALL',
            'message' => 'PDF manual chunked crop started',
        ]);
    }

public function kanbanUploadStatus($id)
{

    $id = basename((string) $id);

    $dir = storage_path('app/temp-kanban');

    if(file_exists($dir.'/'.$id.'.cancelled') || file_exists($dir.'/'.$id.'.cancel'))
    {

        return response()->json([

            'status'=>'CANCELLED'

        ]);

    }

    $marker =

        storage_path(
            'app/temp-kanban/'.$id.'.done'
        );



    if(!file_exists($marker))
    {

        $payload = [

            'status'=>'PROCESSING'

        ];

        // Progres part jobs (khusus flow chunked; tidak ada = abaikan agar kompatibel).
        try {
            $progPath = storage_path('app/temp-kanban/'.$id.'.progress.json');
            if (is_file($progPath)) {
                $prog = json_decode((string) @file_get_contents($progPath), true);
                if (is_array($prog) && isset($prog['done'], $prog['total'])) {
                    $payload['parts_done'] = (int) $prog['done'];
                    $payload['parts_total'] = (int) $prog['total'];
                }
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        // Error merge yang dicatat job (tampil di UI walau log tak terbaca).
        try {
            $errPath = storage_path('app/temp-kanban/'.$id.'.merge-error.json');
            if (is_file($errPath)) {
                $err = json_decode((string) @file_get_contents($errPath), true);
                if (is_array($err) && ! empty($err['error'])) {
                    return response()->json([
                        'status' => 'FAILED',
                        'message' => (string) $err['error'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        return response()->json($payload);

    }



    $filename =
        file_get_contents(
            $marker
        );



    // .done multi-volume (JSON) vs klasik (nama file). Klasik tidak berubah.
    $volumes = null;
    try {
        $decodedDone = json_decode(trim((string) $filename), true);
        if (is_array($decodedDone) && ! empty($decodedDone['volumes']) && is_array($decodedDone['volumes'])) {
            $volumes = [];
            foreach ($decodedDone['volumes'] as $v) {
                $b = basename((string) $v);
                if ($b === '' || $b === '.' || $b === '..') {
                    continue;
                }
                $volumes[] = [
                    'file' => $b,
                    'download' => route('dashboard.kanbanDownload', $b),
                    'preview' => route('dashboard.kanbanPreview', $b),
                ];
            }
        }
    } catch (\Throwable $e) {
        $volumes = null;
    }

    if (is_array($volumes) && ! empty($volumes)) {
        $payload = [
            'status' => 'DONE',
            'volumes' => $volumes,
        ];
    } else {
        $payload = [

            'status'=>'DONE',

            'download'=>

                route(

                    'dashboard.kanbanDownload',

                    $filename

                ),

            'preview'=>

                route(

                    'dashboard.kanbanPreview',

                    $filename

                )

        ];
    }

    // Sidecar info khusus job manual (ada = tampilkan, tidak ada = abaikan agar kompatibel).
    try {
        $infoPath = storage_path('app/temp-kanban/'.$id.'.info.json');
        if (is_file($infoPath)) {
            $info = json_decode((string) @file_get_contents($infoPath), true);
            if (is_array($info)) {
                if (isset($info['pages'])) {
                    $payload['pages'] = (int) $info['pages'];
                }
                if (isset($info['skipped_blank'])) {
                    $payload['skipped_blank'] = (int) $info['skipped_blank'];
                }
                // Arsip kanban-source (1 file, sudah reverse): sajikan ini untuk
                // tombol download/preview, bukan file temp. Tanpa arsip = fallback
                // temp (kompatibel job lama / arsip gagal).
                if (! empty($info['archived']) && is_array($info['archived'])) {
                    $b = basename((string) $info['archived'][0]);
                    if ($b !== '' && $b !== '.' && $b !== '..') {
                        unset($payload['volumes']);
                        $payload['download'] = route('dashboard.kanbanSourceDownload', $b);
                        $payload['preview'] = route('dashboard.kanbanSourcePreview', $b);
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        // abaikan, response DONE tetap valid tanpa info tambahan
    }

    return response()->json($payload);

}

public function kanbanCancel($id)
{

    $id = basename((string) $id);

    if(! preg_match('/^[A-Za-z0-9\-]{8,64}$/', $id))
    {

        return response()->json([

            'status'=>false,

            'message'=>'Invalid job id'

        ], 422);

    }


    $dir = storage_path('app/temp-kanban');

    // 1) Tandai dibatalkan agar worker yang sedang/belum jalan berhenti total.
    @file_put_contents($dir.'/'.$id.'.cancel', now()->toDateTimeString());

    // 2) Keluarkan dari antrean database bila driver-nya database (best effort).
    try {
        DB::table('jobs')->where('payload', 'like', '%'.$id.'%')->delete();
    } catch (\Throwable $e) {
        // abaikan: tabel jobs tidak ada (driver sync) atau DB tidak terjangkau
    }

    $deleted = 0;

    // 3) Hapus file input sisa berdasarkan meta job.
    try {
        $metaPath = $dir.'/'.$id.'.meta.json';
        if(is_file($metaPath))
        {
            $meta = json_decode((string) @file_get_contents($metaPath), true);
            foreach((array) ($meta['files'] ?? []) as $base)
            {
                $base = basename((string) $base);
                if($base === '' || $base === '.' || $base === '..') continue;
                $p = $dir.'/'.$base;
                if(is_file($p) && @unlink($p)) $deleted++;
            }
            @unlink($metaPath);
        }
    } catch (\Throwable $e) {
        // abaikan, lanjut bersih-bersih
    }

    // 4) Hapus hasil bila job keburu DONE (klasik: 1 file; volume: JSON).
    try {
        $donePath = $dir.'/'.$id.'.done';
        if(is_file($donePath))
        {
            $rawDone = trim((string) @file_get_contents($donePath));
            $targets = [];
            $decodedDone = json_decode($rawDone, true);
            if (is_array($decodedDone) && ! empty($decodedDone['volumes']) && is_array($decodedDone['volumes'])) {
                foreach ($decodedDone['volumes'] as $v) {
                    $targets[] = basename((string) $v);
                }
            } elseif ($rawDone !== '') {
                $targets[] = basename($rawDone);
            }
            foreach ($targets as $out) {
                if($out === '' || $out === '.' || $out === '..') continue;
                if(is_file($dir.'/'.$out) && @unlink($dir.'/'.$out))
                {
                    $deleted++;
                }
            }
            @unlink($donePath);
        }
        // 5) Hapus sidecar manual (info/progress/merge-error) bila ada; job lama tidak punya = abaikan.
        @unlink($dir.'/'.$id.'.info.json');
        @unlink($dir.'/'.$id.'.progress.json');
        @unlink($dir.'/'.$id.'.merge-error.json');
    } catch (\Throwable $e) {
        // abaikan
    }

    @file_put_contents($dir.'/'.$id.'.cancelled', now()->toDateTimeString());

    return response()->json([

        'status'=>true,

        'message'=>'Job dibatalkan total',

        'deleted'=>$deleted

    ]);

}

    /**
     * MASTER PRESET garis manual — shared database.
     * GET /dashboard/kanban/manual-presets → [{id, name, lines[]}]
     * Hanya garis yang disimpan (sesuai rule), tanpa checkbox.
     */
    public function kanbanManualPresets()
    {
        $presets = \App\Models\KanbanManualPreset::query()
            ->orderBy('name')
            ->get(['id', 'name', 'lines', 'vlines', 'updated_at']);

        $normLines = static fn ($raw) => array_values(array_map(
            static fn ($v) => round((float) $v, 4),
            is_array($raw) ? $raw : []
        ));

        return response()->json([
            'status' => true,
            'data' => $presets->map(fn ($p) => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'lines' => $normLines($p->lines),
                'vlines' => $normLines($p->vlines),
                'updated_at' => $p->updated_at,
            ])->values(),
            'can_manage' => auth()->user() ? auth()->user()->can('manage-master') : false,
            'can_save' => true,
        ]);
    }

    /**
     * Simpan/upsert preset: {name: "AISIN", lines: [0.3333, 0.6667]}.
     * Nama dinormalisasi UPPERCASE; nama sama = update garis (tambah button baru bila nama baru).
     */
    public function kanbanManualPresetStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'lines' => 'nullable|array|max:30',
            'lines.*' => 'numeric|min:0.02|max:0.98',
            'vlines' => 'nullable|array|max:30',
            'vlines.*' => 'numeric|min:0.02|max:0.98',
        ], [
            'name.required' => 'Nama preset wajib diisi (mis. AISIN).',
        ]);

        if (empty($validated['lines']) && empty($validated['vlines'])) {
            return response()->json(['status' => false, 'message' => 'Atur minimal 1 garis (tidur/berdiri) dulu sebelum save.'], 422);
        }

        $name = strtoupper(trim((string) $validated['name']));
        // Samakan rule frontend: hanya huruf/angka/spasi/dash/underscore.
        $name = preg_replace('/\s+/', ' ', $name);
        if ($name === '' || ! preg_match('/^[A-Z0-9 _\-]{1,50}$/', $name)) {
            return response()->json([
                'status' => false,
                'message' => 'Nama hanya boleh huruf, angka, spasi, - dan _ (mis. AISIN, TESTER 1).',
            ], 422);
        }

        $cleanLines = function ($raw) {
            $sorted = collect((array) $raw)
                ->map(fn ($v) => round((float) $v, 4))
                ->filter(fn ($v) => $v > 0.02 && $v < 0.98)
                ->sort()
                ->values();
            // Dedup garis yang terlalu rapat (<3%).
            $dedup = [];
            foreach ($sorted as $v) {
                if (empty($dedup) || abs($v - end($dedup)) >= 0.03) {
                    $dedup[] = $v;
                }
            }

            return array_values($dedup);
        };
        $dedup = $cleanLines($validated['lines'] ?? []);
        $dedupV = $cleanLines($validated['vlines'] ?? []);
        if (empty($dedup) && empty($dedupV)) {
            return response()->json(['status' => false, 'message' => 'Garis tidak valid.'], 422);
        }

        $npk = auth()->user()->npk ?? auth()->user()->name ?? null;
        $preset = \App\Models\KanbanManualPreset::updateOrCreate(
            ['name' => $name],
            ['lines' => $dedup, 'vlines' => $dedupV, 'updated_by' => $npk]
        );
        if (! $preset->wasRecentlyCreated && empty($preset->created_by)) {
            $preset->created_by = $npk;
            $preset->save();
        } elseif ($preset->wasRecentlyCreated) {
            $preset->created_by = $npk;
            $preset->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Preset '.$name.' tersimpan.',
            'data' => ['id' => (int) $preset->id, 'name' => $preset->name, 'lines' => $preset->lines, 'vlines' => $preset->vlines],
        ]);
    }

    public function kanbanManualPresetDestroy(\App\Models\KanbanManualPreset $preset)
    {
        $name = $preset->name;
        $preset->delete();

        return response()->json(['status' => true, 'message' => 'Preset '.$name.' dihapus.']);
    }

    public function kanbanDownload($file)
{

    $path =
        storage_path(
            'app/temp-kanban/'.$file
        );


    abort_if(
        !file_exists($path),
        404
    );


    return response()->download(
        $path,
        $file
    );

}

    public function kanbanPreview($file)
    {
        $base = basename((string) $file);
        abort_if($base === '' || $base === '.' || $base === '..', 404);

        $path = storage_path('app/temp-kanban/'.$base);
        abort_if(! is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$base.'"',
        ]);
    }

    /**
     * Daftar file kanban arsip (kanban-source) untuk 1..N nomor PDS/manifest.
     * GET /dashboard/kanban/source-files?pds=2004481852,2004485062
     * Dipakai popup bar split di dashboard delivery.
     */
    public function kanbanSourceFiles(Request $request)
    {
        $raw = $request->input('pds', []);
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        $wanted = collect((array) $raw)
            ->map(fn ($v) => preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->take(24)
            ->values();

        if ($wanted->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Parameter PDS kosong.'], 422);
        }

        try {
            $diskFiles = Storage::files('kanban-source');
        } catch (\Throwable $e) {
            $diskFiles = [];
        }

        $data = [];
        foreach ($wanted as $pds) {
            $matched = [];
            foreach ($diskFiles as $path) {
                $base = basename($path);
                if (stripos($base, $pds) === false) {
                    continue;
                }
                // Manifest files are served by manifest endpoint, not kanban.
                if (stripos($base, 'manifest') !== false) {
                    continue;
                }
                $matched[] = [
                    'file' => $base,
                    'download' => route('dashboard.kanbanSourceDownload', $base),
                    'preview' => route('dashboard.kanbanSourcePreview', $base),
                ];
            }
            usort($matched, static fn ($a, $b) => strcmp($a['file'], $b['file']));
            $data[] = ['pds' => $pds, 'files' => $matched];
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function kanbanSourceDownload($file)
    {
        $base = basename((string) $file);
        abort_if($base === '' || $base === '.' || $base === '..', 404);

        $path = storage_path('app/kanban-source/'.$base);
        abort_if(! is_file($path), 404);

        return response()->download($path, $base);
    }

    public function kanbanSourcePreview($file)
    {
        $base = basename((string) $file);
        abort_if($base === '' || $base === '.' || $base === '..', 404);

        $path = storage_path('app/kanban-source/'.$base);
        abort_if(! is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$base.'"',
        ]);
    }

    /**
     * Daftar file manifest arsip (manifest-source, dan kanban-source yang namanya mengandung manifest)
     * untuk 1..N nomor PDS/manifest.
     */
    public function manifestSourceFiles(Request $request)
    {
        $raw = $request->input('pds', []);
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        $wanted = collect((array) $raw)
            ->map(fn ($v) => preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->take(24)
            ->values();

        if ($wanted->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Parameter PDS kosong.'], 422);
        }

        $diskFiles = [];
        foreach (['manifest-source', 'kanban-source'] as $dir) {
            try {
                foreach (Storage::files($dir) as $path) {
                    $diskFiles[$dir][] = $path;
                }
            } catch (\Throwable $e) {
                $diskFiles[$dir] = [];
            }
        }

        $data = [];
        foreach ($wanted as $pds) {
            $matched = [];
            foreach ($diskFiles as $dir => $paths) {
                foreach ((array) $paths as $path) {
                    $base = basename($path);
                    if (stripos($base, $pds) === false) {
                        continue;
                    }
                    $isManifest = $dir === 'manifest-source' || stripos($base, 'manifest') !== false;
                    if (! $isManifest) {
                        continue;
                    }
                    $matched[] = [
                        'file' => $base,
                        'download' => route('dashboard.manifestSourceDownload', $base),
                        'preview' => route('dashboard.manifestSourcePreview', $base),
                    ];
                }
            }
            usort($matched, static fn ($a, $b) => strcmp($a['file'], $b['file']));
            $data[] = ['pds' => $pds, 'files' => $matched];
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function manifestSourceDownload($file)
    {
        $base = basename((string) $file);
        abort_if($base === '' || $base === '.' || $base === '..', 404);

        foreach (['manifest-source', 'kanban-source'] as $dir) {
            $path = storage_path('app/'.$dir.'/'.$base);
            if (is_file($path)) {
                return response()->download($path, $base);
            }
        }
        abort(404);
    }

    public function manifestSourcePreview($file)
    {
        $base = basename((string) $file);
        abort_if($base === '' || $base === '.' || $base === '..', 404);

        foreach (['manifest-source', 'kanban-source'] as $dir) {
            $path = storage_path('app/'.$dir.'/'.$base);
            if (is_file($path)) {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$base.'"',
                ]);
            }
        }
        abort(404);
    }

    /**
     * QZ Tray message signing — hilangkan popup trust per-print (Opsi 1).
     * Setup sekali: admin membuka qz-setup agar keypair dibuat di server aktif.
     * Sertifikat publik dipasang sebagai override.crt di tiap PC printer.
     * Private key TIDAK pernah keluar dari storage/app.
     */
    public function kanbanQzSetup(Request $request)
    {
        try {
            if (! function_exists('openssl_pkey_new')) {
                throw new \RuntimeException('Ekstensi PHP openssl tidak aktif.');
            }

            $dir = storage_path('app/qz-signing');
            if (! is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            $keyPath = $dir.'/private-key.pem';
            $crtPath = $dir.'/digital-certificate.txt';

            $missingKeypair = ! is_file($keyPath) || ! is_file($crtPath)
                || trim((string) @file_get_contents($keyPath)) === ''
                || trim((string) @file_get_contents($crtPath)) === '';
            $rotated = ! $missingKeypair && $this->qzNeedsTrustedRootUpgrade($crtPath);
            $created = false;
            if ($missingKeypair || $rotated) {
                // Config minimal bawaan aplikasi (Windows sering tidak punya openssl.cnf).
                $localCnf = $this->qzEnsureLocalOpenSslConfig($dir);
                @putenv('OPENSSL_CONF='.$localCnf);

                $key = openssl_pkey_new([
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                    'config' => $localCnf,
                ]);
                if ($key === false) {
                    throw new \RuntimeException('Gagal membuat private key.');
                }
                $exported = openssl_pkey_export($key, $privatePem, null, ['config' => $localCnf]);
                if (! $exported || ! isset($privatePem) || trim((string) $privatePem) === '') {
                    throw new \RuntimeException('Gagal mengekspor private key. '.(openssl_error_string() ?: ''));
                }
                $dn = ['commonName' => 'bella-local-qz', 'organizationName' => 'BELLA'];

                // Windows/XAMPP/Laragon sering gagal CSR tanpa openssl.cnf eksplisit.
                $tried = [];
                $csr = false;
                $usedConfig = null;
                $allCandidates = array_merge([$localCnf], $this->qzOpenSslConfigCandidates());
                foreach ($allCandidates as $cnf) {
                    $tried[] = $cnf;
                    $attempt = @openssl_csr_new($dn, $key, ['config' => $cnf, 'digest_alg' => 'sha256']);
                    if ($attempt !== false) {
                        $csr = $attempt;
                        $usedConfig = $cnf;
                        break;
                    }
                }
                if ($csr === false) {
                    $tried[] = '(default system lookup)';
                    $csr = @openssl_csr_new($dn, $key);
                }
                if ($csr === false) {
                    throw new \RuntimeException(
                        'Gagal membuat CSR (openssl.cnf tidak ditemukan). Dicoba: '.implode(', ', $tried)
                    );
                }
                $signArgs = $usedConfig ? [
                    'config' => $usedConfig,
                    'x509_extensions' => 'v3_self',
                ] : [];
                $crt = @openssl_csr_sign($csr, null, $key, 3650, $signArgs);
                if ($crt === false) {
                    throw new \RuntimeException('Gagal self-sign sertifikat.');
                }
                openssl_x509_export($crt, $crtPem);
                file_put_contents($keyPath, $privatePem);
                @chmod($keyPath, 0600);
                file_put_contents($crtPath, $crtPem);
                $created = true;
            }

            $fingerprint = openssl_x509_fingerprint(file_get_contents($crtPath), 'sha256');

            return response()->json([
                'status' => true,
                'created' => $created,
                'rotated' => $rotated,
                'cert_path' => $crtPath,
                'fingerprint_sha256' => $fingerprint ?: null,
                'cert_download' => route('dashboard.kanbanQzCert', ['download' => 1]),
                'next' => 'Download sertifikat, tutup QZ Tray, salin sebagai C:\\Program Files\\QZ Tray\\override.crt, lalu jalankan kembali QZ Tray.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /** Tulis openssl.cnf minimal milik aplikasi (agar tidak tergantung stack server). */
    private function qzEnsureLocalOpenSslConfig(string $dir): string
    {
        $path = $dir.'/openssl.cnf';
        $config = implode("\n", [
            '[ req ]',
            'default_bits = 2048',
            'default_md = sha256',
            'distinguished_name = req_dn',
            'prompt = no',
            'x509_extensions = v3_self',
            '',
            '[ req_dn ]',
            'CN = bella-local-qz',
            'O = BELLA',
            '',
            '[ v3_self ]',
            'basicConstraints = critical,CA:true',
            'keyUsage = critical,digitalSignature,keyCertSign,cRLSign',
            'subjectKeyIdentifier = hash',
            'authorityKeyIdentifier = keyid:always,issuer',
            '',
        ]);

        if (! is_file($path) || file_get_contents($path) !== $config) {
            file_put_contents($path, $config);
        }

        return str_replace('\\', '/', $path);
    }

    /** Rotasi hanya sertifikat self-signed BELLA lama; jangan timpa sertifikat resmi QZ. */
    private function qzNeedsTrustedRootUpgrade(string $path): bool
    {
        $certificate = @openssl_x509_parse(file_get_contents($path));
        if (! is_array($certificate)) {
            return true;
        }

        $subject = $certificate['subject'] ?? [];
        $issuer = $certificate['issuer'] ?? [];
        $constraints = strtoupper(str_replace(' ', '', $certificate['extensions']['basicConstraints'] ?? ''));

        return ($subject['CN'] ?? null) === 'bella-local-qz'
            && $subject === $issuer
            && ! str_contains($constraints, 'CA:TRUE');
    }

    /** Kandidat openssl.cnf di stack Windows umum (XAMPP/Laragon/PHP/Git). */
    private function qzOpenSslConfigCandidates(): array
    {
        $candidates = [];
        $push = function ($p) use (&$candidates) {
            if ($p && is_string($p)) {
                $p = str_replace('\\', '/', $p);
                if (is_file($p) && ! in_array($p, $candidates, true)) {
                    $candidates[] = $p;
                }
            }
        };

        foreach (['OPENSSL_CONF', 'SSL_CERT_FILE'] as $env) {
            $push(getenv($env) ?: null);
        }
        if (defined('PHP_BINARY')) {
            $phpDir = str_replace('\\', '/', dirname((string) PHP_BINARY));
            $push($phpDir.'/extras/ssl/openssl.cnf');
            $push($phpDir.'/../ssl/openssl.cnf');
        }
        foreach (['C:', 'D:'] as $drive) {
            $push($drive.'/xampp/apache/conf/openssl.cnf');
            $push($drive.'/xampp/php/extras/ssl/openssl.cnf');
            $push($drive.'/laragon/bin/apache/apache-2.4.*/conf/openssl.cnf');
            foreach (glob($drive.'/laragon/bin/apache/*/conf/openssl.cnf') ?: [] as $g) {
                $push($g);
            }
            foreach (glob($drive.'/*/bin/php/*/extras/ssl/openssl.cnf') ?: [] as $g) {
                $push($g);
            }
            $push($drive.'/Program Files/Git/usr/ssl/openssl.cnf');
            $push($drive.'/Program Files (x86)/Git/usr/ssl/openssl.cnf');
        }

        return $candidates;
    }

    /** Sertifikat publik QZ (dibaca qz.security.setCertificatePromise). */
    public function kanbanQzCert(Request $request)
    {
        $path = storage_path('app/qz-signing/digital-certificate.txt');
        abort_if(! is_file($path), 404, 'Sertifikat QZ belum dibuat. Buka qz-setup dulu.');
        if ($request->boolean('download')) {
            return response()->download($path, 'override.crt');
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function kanbanQzSign(Request $request)
    {
        $data = $request->validate(['request' => 'required|string|max:12000000']);
        $keyPath = storage_path('app/qz-signing/private-key.pem');
        if (! is_file($keyPath)) {
            \Log::error('QZ sign: private key file missing', ['path' => $keyPath]);
            abort(404, 'Private key QZ belum dibuat. Buka qz-setup dulu.');
        }
        $keyContents = @file_get_contents($keyPath);
        if ($keyContents === false || trim($keyContents) === '') {
            \Log::error('QZ sign: private key empty/unreadable', ['path' => $keyPath, 'size' => @filesize($keyPath)]);
            abort(500, 'Private key QZ tidak valid.');
        }
        $key = openssl_get_privatekey($keyContents);
        if ($key === false) {
            \Log::error('QZ sign: openssl_get_privatekey failed', [
                'path' => $keyPath,
                'size' => strlen($keyContents),
                'openssl_error' => openssl_error_string(),
            ]);
            abort(500, 'Private key QZ tidak valid.');
        }
        $ok = openssl_sign($data['request'], $signature, $key, 'sha512');
        if (! $ok || $signature === null) {
            \Log::error('QZ sign: openssl_sign failed', ['openssl_error' => openssl_error_string()]);
            abort(500, 'Gagal signing request QZ.');
        }

        return response(base64_encode($signature), 200, ['Content-Type' => 'text/plain']);
    }
}
