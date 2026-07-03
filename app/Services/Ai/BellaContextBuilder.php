<?php

namespace App\Services\Ai;

use App\Models\MasterCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BellaContextBuilder
{
    public function build(array $overview, Carbon $from, Carbon $to, string $question): array
    {
        $start = $from->copy()->startOfDay()->toDateTimeString();
        $end = $to->copy()->endOfDay()->toDateTimeString();
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();
        $modules = $this->expandRelatedModules($this->resolveModules($question));
        [$focusDate, $focusLabel] = $this->resolveQuestionDate($question);
        $loadingListQtyRanking = $this->buildLoadingListQtyRanking($focusDate);

        $context = [
            'scope' => [
                'periode_filter' => ['from' => $fromDate, 'to' => $toDate],
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'mode' => 'data operasional dari database Body Plant (bukan akses SQL bebas)',
                'catatan' => 'Gunakan petunjuk_data hanya sebagai kamus arti field (bukan checklist). insights berisi jawaban siap pakai untuk pertanyaan umum. ringkasan_operasional = agregat periode filter dashboard.',
                'modules_loaded' => $modules,
            ],
            'insights' => $this->buildInsights($focusDate, $focusLabel, $loadingListQtyRanking),
            'petunjuk_data' => $this->dataDictionary(),
            'referensi' => $this->buildReferenceData(),
            'ringkasan_operasional' => array_merge(
                $this->buildOperationalSnapshot($overview, $from, $to),
                ['loading_list_qty_tertinggi' => $loadingListQtyRanking['top_1'] ?? null]
            ),
            'overview' => $this->buildOverviewContext($overview, $from, $to),
            'modules' => [],
            'search_hits' => $this->buildSearchHits($question, $from, $to),
        ];

        $preparationDeadline = $this->buildPreparationDeadlineContext($question);
        if ($preparationDeadline !== null) {
            $context['preparation_deadline'] = $preparationDeadline;
        }

        if (in_array('loading_list_pds', $modules, true) && $this->tableExists('loading_lists')) {
            $ll = DB::table('loading_lists as ll')
                ->leftJoin('customers as c', 'c.id', '=', 'll.customer_id')
                ->whereBetween('ll.delivery_date', [$start, $end]);

            $context['modules']['loading_list_pds'] = [
                'summary_periode' => [
                    'total' => (clone $ll)->count(),
                    'total_pds' => (clone $ll)->distinct('ll.pds_number')->count('ll.pds_number'),
                    'truck_status' => $this->columnExists('loading_lists', 'truck_status')
                        ? $this->rows((clone $ll)->select('ll.truck_status', DB::raw('COUNT(*) as total'))->groupBy('ll.truck_status')->get())
                        : [],
                ],
                'per_customer' => $this->rows((clone $ll)
                    ->select(DB::raw('COALESCE(c.name, "Unknown") as customer'), DB::raw('COUNT(*) as total'))
                    ->groupBy('c.name')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get()),
                'per_cycle' => $this->rows((clone $ll)
                    ->select('ll.cycle', DB::raw('COUNT(*) as total'))
                    ->groupBy('ll.cycle')
                    ->orderBy('ll.cycle')
                    ->limit(12)
                    ->get()),
                'terbaru' => $this->formatRows((clone $ll)
                    ->select('ll.number as loading_list', 'll.pds_number', 'll.cycle', 'll.delivery_date', 'll.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('ll.delivery_date')
                    ->limit(12)
                    ->get()),
            ];

            if ($this->tableExists('loading_list_details')) {
                $qty = (array) DB::table('loading_lists as ll')
                    ->join('loading_list_details as d', 'd.loading_list_id', '=', 'll.id')
                    ->whereBetween('ll.delivery_date', [$start, $end])
                    ->selectRaw('SUM(COALESCE(d.kanban_qty, 0)) as target_kanban, SUM(COALESCE(d.actual_kanban_qty, 0)) as actual_kanban, SUM(COALESCE(d.total_qty, 0)) as target_qty')
                    ->first();
                $targetKanban = (int) ($qty['target_kanban'] ?? 0);
                $actualKanban = (int) ($qty['actual_kanban'] ?? 0);
                $context['modules']['loading_list_pds']['qty_periode'] = $qty + [
                    'progress_pct' => $targetKanban > 0 ? round($actualKanban / $targetKanban * 100, 1) : 0,
                    'sisa_kanban' => max(0, $targetKanban - $actualKanban),
                ];

                $context['modules']['loading_list_pds']['belum_lengkap'] = $this->formatRows(
                    DB::table('loading_lists as ll')
                        ->leftJoin('customers as c', 'c.id', '=', 'll.customer_id')
                        ->join('loading_list_details as d', 'd.loading_list_id', '=', 'll.id')
                        ->whereBetween('ll.delivery_date', [$start, $end])
                        ->selectRaw('
                            ll.id as loading_list_id,
                            ll.number as loading_list,
                            ll.pds_number,
                            ll.cycle,
                            COALESCE(c.name, "Unknown") as customer,
                            ll.delivery_date,
                            SUM(COALESCE(d.kanban_qty, 0)) as target_kanban,
                            SUM(COALESCE(d.actual_kanban_qty, 0)) as actual_kanban
                        ')
                        ->groupBy('ll.id', 'll.number', 'll.pds_number', 'll.cycle', 'c.name', 'll.delivery_date')
                        ->havingRaw('SUM(COALESCE(d.kanban_qty, 0)) > 0 AND SUM(COALESCE(d.actual_kanban_qty, 0)) < SUM(COALESCE(d.kanban_qty, 0))')
                        ->orderBy('ll.delivery_date')
                        ->limit(12)
                        ->get()
                        ->map(function ($row) {
                            $target = (int) $row->target_kanban;
                            $actual = (int) $row->actual_kanban;

                            return [
                                'loading_list' => $row->loading_list,
                                'pds_number' => $row->pds_number,
                                'cycle' => $row->cycle,
                                'customer' => $row->customer,
                                'delivery_date' => $this->formatDateValue($row->delivery_date),
                                'target_kanban' => $target,
                                'actual_kanban' => $actual,
                                'progress_pct' => $target > 0 ? round($actual / $target * 100, 1) : 0,
                                'sisa_kanban' => max(0, $target - $actual),
                            ];
                        })
                );

                $context['modules']['loading_list_pds']['fokus_tanggal'] = [
                    'label' => $focusLabel,
                    'tanggal' => $focusDate->toDateString(),
                    'ranking_by_qty' => $loadingListQtyRanking,
                ];
            }
        }

        if (in_array('pulling', $modules, true) && $this->tableExists('pullings')) {
            $pullings = DB::table('pullings as p')
                ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
                ->whereBetween('p.pulling_date', [$start, $end]);

            $context['modules']['pulling'] = [
                'summary_periode' => [
                    'total' => (clone $pullings)->count(),
                    'total_pds' => (clone $pullings)->distinct('p.pds_number')->count('p.pds_number'),
                    'total_loading_list' => (clone $pullings)->distinct('p.loading_list')->count('p.loading_list'),
                ],
                'per_customer' => $this->rows((clone $pullings)
                    ->select(DB::raw('COALESCE(c.name, "Unknown") as customer'), DB::raw('COUNT(*) as total'))
                    ->groupBy('c.name')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get()),
                'terbaru' => $this->formatRows((clone $pullings)
                    ->select('p.loading_list', 'p.pds_number', 'p.cycle', 'p.pulling_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('p.pulling_date')
                    ->limit(12)
                    ->get()),
                'per_hari' => $this->rows((clone $pullings)
                    ->selectRaw('DATE(p.pulling_date) as tanggal, COUNT(*) as total')
                    ->groupBy('tanggal')
                    ->orderBy('tanggal')
                    ->limit(14)
                    ->get()),
            ];
        }

        if (in_array('production_plan', $modules, true) && $this->tableExists('production_plans')) {
            $plans = DB::table('production_plans as pp')
                ->whereBetween('pp.plan_date', [$fromDate, $toDate]);

            $context['modules']['production_plan'] = [
                'summary_periode' => (array) (clone $plans)
                    ->selectRaw('COUNT(*) as total_plan, SUM(order_qty) as order_qty, SUM(direct_pulling_qty) as direct_pulling_qty, SUM(stock_chute_qty) as stock_chute_qty')
                    ->first(),
                'per_line' => $this->rows((clone $plans)
                    ->select('pp.line', DB::raw('COUNT(*) as total_plan'), DB::raw('SUM(pp.order_qty) as order_qty'), DB::raw('SUM(pp.direct_pulling_qty) as direct_pulling_qty'))
                    ->groupBy('pp.line')
                    ->orderByDesc('order_qty')
                    ->limit(10)
                    ->get()),
                'plan_terbaru' => $this->rows((clone $plans)
                    ->select('pp.plan_date', 'pp.line', 'pp.customer', 'pp.dock', 'pp.cycle', 'pp.back_no', 'pp.order_qty', 'pp.direct_pulling_qty', 'pp.delivery_time', 'pp.balance_time')
                    ->orderByDesc('pp.plan_date')
                    ->orderBy('pp.delivery_time')
                    ->limit(12)
                    ->get()),
            ];
        }

        if (in_array('production_actual_scan', $modules, true) && $this->tableExists('part_scans')) {
            $scans = DB::table('part_scans as ps')
                ->whereBetween('ps.scanned_at', [$start, $end]);

            $context['modules']['production_actual_scan'] = [
                'summary_periode' => [
                    'total_scan' => (clone $scans)->count(),
                    'unique_kanban' => (clone $scans)->distinct('ps.kanban_id')->count('ps.kanban_id'),
                ],
                'per_line_model' => $this->rows((clone $scans)
                    ->select('ps.line', 'ps.model', DB::raw('COUNT(*) as total_scan'))
                    ->groupBy('ps.line', 'ps.model')
                    ->orderByDesc('total_scan')
                    ->limit(10)
                    ->get()),
                'scan_terbaru' => $this->rows((clone $scans)
                    ->select('ps.line', 'ps.model', 'ps.dandori_board', 'ps.last4', 'ps.scanned_at')
                    ->orderByDesc('ps.scanned_at')
                    ->limit(12)
                    ->get()),
            ];
        }

        if (in_array('stock', $modules, true) && $this->tableExists('internal_parts') && $this->tableExists('production_stocks') && $this->columnExists('internal_parts', 'standard_stock')) {
            $stock = DB::table('internal_parts as ip')
                ->leftJoin('lines as l', 'l.id', '=', 'ip.line_id')
                ->leftJoin('production_stocks as ps', 'ps.internal_part_id', '=', 'ip.id');

            $context['modules']['stock'] = [
                'summary_latest' => (array) (clone $stock)
                    ->selectRaw('COUNT(ip.id) as total_part, SUM(CASE WHEN COALESCE(ps.current_stock, 0) < COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as under_stock, SUM(CASE WHEN COALESCE(ps.current_stock, 0) = COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as low_stock, SUM(CASE WHEN COALESCE(ps.current_stock, 0) > COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as in_stock')
                    ->first(),
                'per_line' => $this->rows((clone $stock)
                    ->select(DB::raw('COALESCE(l.name, "-") as line'), DB::raw('COUNT(ip.id) as total_part'), DB::raw('SUM(CASE WHEN COALESCE(ps.current_stock, 0) < COALESCE(ip.standard_stock, 0) THEN 1 ELSE 0 END) as under_stock'))
                    ->groupBy('l.name')
                    ->orderByDesc('under_stock')
                    ->limit(10)
                    ->get()),
                'understock_teratas' => $this->rows((clone $stock)
                    ->select('ip.part_number', 'ip.back_number', 'ip.part_name', DB::raw('COALESCE(l.name, "-") as line'), DB::raw('COALESCE(ps.current_stock, 0) as current_stock'), 'ip.standard_stock', DB::raw('(COALESCE(ip.standard_stock, 0) - COALESCE(ps.current_stock, 0)) as gap'))
                    ->whereRaw('COALESCE(ps.current_stock, 0) < COALESCE(ip.standard_stock, 0)')
                    ->orderByDesc('gap')
                    ->limit(12)
                    ->get()),
            ];
        }

        if (in_array('pis', $modules, true) && $this->tableExists('pis_scans')) {
            $pis = DB::table('pis_scans as ps')
                ->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id')
                ->whereBetween('ps.delivery_date', [$start, $end]);

            [$focusDate] = $this->resolveQuestionDate($question);
            $focusStart = $focusDate->copy()->startOfDay()->toDateTimeString();
            $focusEnd = $focusDate->copy()->endOfDay()->toDateTimeString();
            $pisFocus = DB::table('pis_scans as ps')
                ->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id')
                ->whereBetween('ps.delivery_date', [$focusStart, $focusEnd]);

            $context['modules']['pis'] = [
                'summary_periode' => [
                    'total_scan' => (clone $pis)->count(),
                    'total_pds' => (clone $pis)->distinct('ps.pds_number')->count('ps.pds_number'),
                ],
                'per_customer' => $this->rows((clone $pis)
                    ->select(DB::raw('COALESCE(c.name, "Unknown") as customer'), DB::raw('COUNT(*) as total'))
                    ->groupBy('c.name')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get()),
                'terbaru' => $this->formatRows((clone $pis)
                    ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'ps.delivery_date', 'ps.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                    ->orderByDesc('ps.delivery_date')
                    ->limit(12)
                    ->get()),
                'fokus_tanggal' => [
                    'tanggal' => $focusDate->toDateString(),
                    'total_scan' => (clone $pisFocus)->count(),
                    'terbaru' => $this->formatRows((clone $pisFocus)
                        ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'ps.delivery_date', 'ps.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                        ->orderByDesc('ps.delivery_date')
                        ->limit(8)
                        ->get()),
                ],
            ];

            if ($this->tableExists('pis_scan_details')) {
                $qty = (array) DB::table('pis_scans as ps')
                    ->join('pis_scan_details as d', 'd.pis_scan_id', '=', 'ps.id')
                    ->whereBetween('ps.delivery_date', [$start, $end])
                    ->selectRaw('SUM(COALESCE(d.target_qty, 0)) as target_qty, SUM(COALESCE(d.scanned_qty, 0)) as scanned_qty, SUM(COALESCE(d.remaining_qty, 0)) as remaining_qty')
                    ->first();
                $targetQty = (int) ($qty['target_qty'] ?? 0);
                $scannedQty = (int) ($qty['scanned_qty'] ?? 0);
                $context['modules']['pis']['qty_periode'] = $qty + [
                    'completion_pct' => $targetQty > 0 ? round($scannedQty / $targetQty * 100, 1) : 0,
                ];

                $qtyFocus = (array) DB::table('pis_scans as ps')
                    ->join('pis_scan_details as d', 'd.pis_scan_id', '=', 'ps.id')
                    ->whereBetween('ps.delivery_date', [$focusStart, $focusEnd])
                    ->selectRaw('SUM(COALESCE(d.target_qty, 0)) as target_qty, SUM(COALESCE(d.scanned_qty, 0)) as scanned_qty, SUM(COALESCE(d.remaining_qty, 0)) as remaining_qty')
                    ->first();
                $focusTarget = (int) ($qtyFocus['target_qty'] ?? 0);
                $focusScanned = (int) ($qtyFocus['scanned_qty'] ?? 0);
                $context['modules']['pis']['fokus_tanggal']['qty'] = $qtyFocus + [
                    'completion_pct' => $focusTarget > 0 ? round($focusScanned / $focusTarget * 100, 1) : 0,
                ];
            }
        }

        if (in_array('receiving', $modules, true) && $this->tableExists('receiving_logs')) {
            $receiving = DB::table('receiving_logs as rl')
                ->whereBetween('rl.expected_time', [$start, $end]);

            $context['modules']['receiving'] = [
                'summary_periode' => [
                    'total_log' => (clone $receiving)->count(),
                    'per_status' => $this->rows((clone $receiving)->select('rl.status', DB::raw('COUNT(*) as total'))->groupBy('rl.status')->orderBy('rl.status')->get()),
                ],
                'log_terbaru' => $this->rows((clone $receiving)
                    ->select('rl.pick_list', 'rl.supplier_code', 'rl.expected_time', 'rl.status', 'rl.notified_at')
                    ->orderByDesc('rl.expected_time')
                    ->limit(10)
                    ->get()),
            ];
        }

        if (in_array('quality', $modules, true) && $this->tableExists('quality_inspections')) {
            $quality = DB::table('quality_inspections as qi')
                ->whereBetween('qi.checked_at', [$start, $end]);

            $context['modules']['quality'] = [
                'summary_periode' => [
                    'total_inspection' => (clone $quality)->count(),
                    'per_judgement' => $this->rows((clone $quality)->select('qi.judgement', DB::raw('COUNT(*) as total'))->groupBy('qi.judgement')->orderByDesc('total')->get()),
                    'per_approval' => $this->rows((clone $quality)->select('qi.approval_status', DB::raw('COUNT(*) as total'))->groupBy('qi.approval_status')->orderByDesc('total')->get()),
                ],
                'not_ok_terbaru' => $this->rows((clone $quality)
                    ->select('qi.kanban_code', 'qi.check_type', 'qi.line', 'qi.shift', 'qi.pic', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.judgement', 'qi.approval_status', 'qi.checked_at')
                    ->where('qi.judgement', '<>', 'OK')
                    ->orderByDesc('qi.checked_at')
                    ->limit(10)
                    ->get()),
            ];
        }

        if (in_array('error_log', $modules, true) && $this->tableExists('error_logs')) {
            $errors = DB::table('error_logs as el')
                ->whereBetween('el.date', [$start, $end]);

            $context['modules']['error_log'] = [
                'summary_periode' => [
                    'total_error' => (clone $errors)->count(),
                    'per_area' => $this->rows((clone $errors)->select('el.area', DB::raw('COUNT(*) as total'))->groupBy('el.area')->orderByDesc('total')->limit(8)->get()),
                ],
                'terbaru' => $this->rows((clone $errors)
                    ->select('el.area', 'el.message', 'el.expected', 'el.scanned', 'el.date')
                    ->orderByDesc('el.date')
                    ->limit(12)
                    ->get()),
            ];
        }

        if (in_array('mutation_stock_flow', $modules, true) && $this->tableExists('mutations')) {
            $mutations = DB::table('mutations as m')
                ->whereBetween('m.date', [$start, $end]);

            $context['modules']['mutation_stock_flow'] = [
                'summary_periode' => [
                    'total_mutation' => (clone $mutations)->count(),
                    'per_type' => $this->rows((clone $mutations)->select('m.type', DB::raw('COUNT(*) as total'), DB::raw('SUM(m.qty) as qty'))->groupBy('m.type')->orderByDesc('qty')->get()),
                ],
            ];
        }

        return $context;
    }

    public function actionLinks(string $question, array $context = []): array
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
        $seen = [];
        $push = function (array $action) use (&$actions, &$seen): void {
            $url = (string) ($action['url'] ?? '');
            if ($url === '' || isset($seen[$url])) {
                return;
            }
            $seen[$url] = true;
            $actions[] = [
                'label' => (string) ($action['label'] ?? 'Buka halaman'),
                'url' => $url,
            ];
        };

        $loadingListUrl = function ($row) use ($push): void {
            $id = $row['loading_list_id'] ?? $row['id'] ?? null;
            $number = $row['loading_list'] ?? $row['loading_list_number'] ?? $row['number'] ?? null;
            $key = $id ?: $number;
            if (! $key) {
                return;
            }
            $push([
                'label' => 'LL '.Str::limit((string) ($number ?: $key), 18, ''),
                'url' => route('loadingList.detail', ['loadingList' => $key]),
            ]);
        };

        $matches = $context['search_hits']['matches'] ?? [];
        foreach (['loading_list_pds', 'pulling', 'pis'] as $bucket) {
            foreach (($matches[$bucket] ?? []) as $row) {
                if ($bucket === 'pis') {
                    $loadingListUrl($row);
                } else {
                    $loadingListUrl($row);
                }
            }
        }

        foreach (($matches['master_part_stock'] ?? []) as $row) {
            $back = trim((string) ($row['back_number'] ?? ''));
            if ($back !== '') {
                $push([
                    'label' => 'Stok '.$back,
                    'url' => route('dashboard.productionStock', ['back_number' => $back]),
                ]);
            }
        }

        foreach (($context['modules']['loading_list_pds']['belum_lengkap'] ?? []) as $row) {
            $loadingListUrl($row);
        }

        foreach (($context['modules']['stock']['understock_teratas'] ?? []) as $row) {
            $back = trim((string) ($row['back_number'] ?? ''));
            if ($back !== '') {
                $push([
                    'label' => 'Stok '.$back,
                    'url' => route('dashboard.productionStock', ['back_number' => $back]),
                ]);
            }
        }

        foreach (($context['overview']['delivery_berisiko'] ?? []) as $row) {
            $loadingListUrl([
                'loading_list_id' => $row['loading_list_id'] ?? null,
                'loading_list' => $row['loading_list'] ?? null,
            ]);
        }

        foreach ($this->expandRelatedModules($this->resolveModules($question)) as $module) {
            if (isset($catalog[$module])) {
                $push($catalog[$module]);
            }
        }

        if ($actions === []) {
            $push([
                'label' => 'Dashboard Overview',
                'url' => route('dashboard.overview'),
            ]);
        }

        return array_slice($actions, 0, 5);
    }

    private function buildOverviewContext(array $data, Carbon $from, Carbon $to): array
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
            'kpi_labels' => [
                'prepTotal' => 'Total loading list di periode (delivery date)',
                'prepComplete' => 'Loading list preparation sudah 100% kanban',
                'prepPct' => 'Persentase preparation lengkap',
                'kanbanScanned' => 'Total mutasi checkout (kanban scan/pulling) di periode',
                'pullingDone' => 'Total record pulling di periode',
                'produksi' => 'Total mutasi supply (produksi) di periode',
                'totalPart' => 'Jumlah part internal terdaftar',
                'underStock' => 'Part dengan stok di bawah standard',
                'pisScan' => 'Jumlah log scan PIS di periode',
                'pisDone' => 'Detail PIS yang scanned_qty >= target_qty (snapshot)',
                'errPeriod' => 'Total error log di periode',
            ],
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

    private function buildPreparationDeadlineContext(string $question): ?array
    {
        $q = Str::lower($question);
        $mentionsPreparation = str_contains($q, 'preparation') || str_contains($q, 'prep');
        $mentionsDeadline = str_contains($q, 'deadline')
            || str_contains($q, 'telat')
            || str_contains($q, 'terlambat')
            || str_contains($q, 'lewat')
            || str_contains($q, 'melewati');

        if (! $mentionsPreparation || ! $mentionsDeadline || ! $this->tableExists('loading_lists') || ! $this->tableExists('master_cycles')) {
            return null;
        }

        [$targetDate, $dateLabel] = $this->resolveQuestionDate($question);

        $masters = MasterCycle::get(['customer_id', 'cycle_name', 'time', 'prep_end_time', 'prep_day_offset', 'prep_end_day_offset', 'truck_time']);
        $masterExact = [];
        $masterDigit = [];
        $prepEndOffsets = [];
        foreach ($masters as $master) {
            $exactKey = ((int) $master->customer_id).'|'.$this->normalizeCycleLabel($master->cycle_name);
            $masterExact[$exactKey] = $master;
            $digit = $this->cycleDigit($master->cycle_name);
            if ($digit !== '') {
                $masterDigit[((int) $master->customer_id).'|'.$digit] = $master;
            }

            if (! empty($master->time) && ! empty($master->prep_end_time)) {
                $prepStart = substr((string) $master->time, 0, 5);
                $prepEnd = substr((string) $master->prep_end_time, 0, 5);
                $truckTime = $master->truck_time ? substr((string) $master->truck_time, 0, 5) : null;
                $prepDayOffset = $this->resolvePrepDayOffset((int) ($master->prep_day_offset ?? 0), $prepStart, $truckTime);
                $prepEndOffsets[] = $this->computePrepEndDayOffset($prepDayOffset, $prepStart, $prepEnd);
            }
        }

        $minOffset = $prepEndOffsets !== [] ? min($prepEndOffsets) : 0;
        $maxOffset = $prepEndOffsets !== [] ? max($prepEndOffsets) : 0;
        $lookback = $targetDate->copy()->subDays($maxOffset)->startOfDay();
        $lookahead = $targetDate->copy()->subDays($minOffset)->endOfDay();

        $loadingLists = DB::table('loading_lists as ll')
            ->leftJoin('customers as c', 'c.id', '=', 'll.customer_id')
            ->leftJoin('loading_list_details as d', 'd.loading_list_id', '=', 'll.id')
            ->whereBetween('ll.delivery_date', [$lookback, $lookahead])
            ->selectRaw('
                ll.id,
                ll.customer_id,
                COALESCE(c.name, "-") as customer,
                ll.number as loading_list,
                ll.pds_number,
                ll.cycle,
                ll.delivery_date,
                SUM(COALESCE(d.kanban_qty, 0)) as target_kanban,
                SUM(COALESCE(d.actual_kanban_qty, 0)) as actual_kanban
            ')
            ->groupBy('ll.id', 'll.customer_id', 'c.name', 'll.number', 'll.pds_number', 'll.cycle', 'll.delivery_date')
            ->get();

        if ($loadingLists->isEmpty()) {
            return $this->emptyPreparationDeadlineContext($targetDate, $dateLabel, 0);
        }

        $pullingByPds = collect();
        $pdsNumbers = $loadingLists->pluck('pds_number')->filter()->unique()->values();
        if ($pdsNumbers->isNotEmpty() && $this->tableExists('pullings')) {
            $pullingByPds = DB::table('pullings')
                ->select('customer_id', 'pds_number', 'loading_list', DB::raw('MAX(pulling_date) as latest_pulling_date'))
                ->whereIn('pds_number', $pdsNumbers->all())
                ->whereNotNull('pulling_date')
                ->groupBy('customer_id', 'pds_number', 'loading_list')
                ->get()
                ->groupBy(fn ($row) => ((int) $row->customer_id).'|'.(string) $row->pds_number);
        }

        $lateItems = [];
        $seenItems = [];
        $matchedDeadlineRows = 0;
        $unconfiguredRows = 0;

        foreach ($loadingLists as $ll) {
            $master = $masterExact[((int) $ll->customer_id).'|'.$this->normalizeCycleLabel($ll->cycle)]
                ?? ($masterDigit[((int) $ll->customer_id).'|'.$this->cycleDigit($ll->cycle)] ?? null);

            if (! $master || empty($master->prep_end_time) || empty($ll->delivery_date)) {
                $unconfiguredRows++;
                continue;
            }

            $deadline = $this->preparationDeadlineForLoadingList($ll->delivery_date, $master);
            if (! $deadline || $deadline->toDateString() !== $targetDate->toDateString()) {
                continue;
            }

            $matchedDeadlineRows++;
            $pullingItems = $pullingByPds->get(((int) $ll->customer_id).'|'.(string) $ll->pds_number, collect());
            foreach ($pullingItems as $pulling) {
                $pullingDate = Carbon::parse($pulling->latest_pulling_date);
                if (! $pullingDate->gt($deadline)) {
                    continue;
                }

                $loadingListNumber = $pulling->loading_list ? (string) $pulling->loading_list : (string) $ll->loading_list;
                $key = implode('|', [(string) $ll->pds_number, $loadingListNumber, $pullingDate->toIso8601String()]);
                if (isset($seenItems[$key])) {
                    continue;
                }
                $seenItems[$key] = true;

                $delayMinutes = $deadline->diffInMinutes($pullingDate);
                $targetKanban = (int) ($ll->target_kanban ?? 0);
                $actualKanban = (int) ($ll->actual_kanban ?? 0);

                $lateItems[] = [
                    'customer' => (string) $ll->customer,
                    'cycle' => (string) $ll->cycle,
                    'pds_number' => (string) $ll->pds_number,
                    'loading_list' => $loadingListNumber,
                    'delivery_date' => Carbon::parse($ll->delivery_date)->toDateString(),
                    'deadline_preparation' => $deadline->format('Y-m-d H:i:s'),
                    'pulling_date' => $pullingDate->format('Y-m-d H:i:s'),
                    'delay_minutes' => $delayMinutes,
                    'delay_label' => $this->formatDelayLabel($delayMinutes),
                    'actual_kanban' => $actualKanban,
                    'target_kanban' => $targetKanban,
                    'progress_pct' => $targetKanban > 0 ? round(($actualKanban / $targetKanban) * 100, 1) : 0,
                ];
            }
        }

        usort($lateItems, fn ($a, $b) => ($b['delay_minutes'] <=> $a['delay_minutes']));

        return [
            'target_date' => $targetDate->toDateString(),
            'date_label' => $dateLabel,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'definition' => 'Telat preparation = pulling_date lebih besar dari deadline_preparation / Finish Preparation.',
            'matched_deadline_bucket_count' => $matchedDeadlineRows,
            'unconfigured_bucket_count' => $unconfiguredRows,
            'total_late_items' => count($lateItems),
            'summary_by_customer' => collect($lateItems)
                ->groupBy('customer')
                ->map(fn ($rows, $customer) => ['customer' => $customer, 'late_items' => $rows->count()])
                ->sortByDesc('late_items')
                ->values()
                ->all(),
            'items' => array_slice($lateItems, 0, 30),
            'is_truncated' => count($lateItems) > 30,
        ];
    }

    private function emptyPreparationDeadlineContext(Carbon $targetDate, string $dateLabel, int $unconfiguredRows): array
    {
        return [
            'target_date' => $targetDate->toDateString(),
            'date_label' => $dateLabel,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'definition' => 'Telat preparation = pulling_date lebih besar dari deadline_preparation / Finish Preparation.',
            'matched_deadline_bucket_count' => 0,
            'unconfigured_bucket_count' => $unconfiguredRows,
            'total_late_items' => 0,
            'summary_by_customer' => [],
            'items' => [],
            'is_truncated' => false,
        ];
    }

    private function buildLoadingListQtyRanking(Carbon $date, int $limit = 10): array
    {
        if (! $this->tableExists('loading_lists') || ! $this->tableExists('loading_list_details')) {
            return [
                'tanggal' => $date->toDateString(),
                'urutan' => 'target_qty (SUM total_qty detail) tertinggi ke terendah',
                'top_1' => null,
                'items' => [],
            ];
        }

        $start = $date->copy()->startOfDay()->toDateTimeString();
        $end = $date->copy()->endOfDay()->toDateTimeString();

        $items = DB::table('loading_lists as ll')
            ->leftJoin('customers as c', 'c.id', '=', 'll.customer_id')
            ->join('loading_list_details as d', 'd.loading_list_id', '=', 'll.id')
            ->whereBetween('ll.delivery_date', [$start, $end])
            ->selectRaw('
                ll.id as loading_list_id,
                ll.number as loading_list,
                ll.pds_number,
                ll.cycle,
                COALESCE(c.name, "Unknown") as customer,
                ll.delivery_date,
                SUM(COALESCE(d.total_qty, 0)) as target_qty,
                SUM(COALESCE(d.kanban_qty, 0)) as target_kanban,
                SUM(COALESCE(d.actual_kanban_qty, 0)) as actual_kanban
            ')
            ->groupBy('ll.id', 'll.number', 'll.pds_number', 'll.cycle', 'c.name', 'll.delivery_date')
            ->orderByDesc('target_qty')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $targetKanban = (int) $row->target_kanban;
                $actualKanban = (int) $row->actual_kanban;

                return [
                    'loading_list_id' => $row->loading_list_id,
                    'loading_list' => $row->loading_list,
                    'pds_number' => $row->pds_number,
                    'cycle' => $row->cycle,
                    'customer' => $row->customer,
                    'delivery_date' => $this->formatDateValue($row->delivery_date),
                    'target_qty' => (int) $row->target_qty,
                    'target_kanban' => $targetKanban,
                    'actual_kanban' => $actualKanban,
                    'progress_pct' => $targetKanban > 0 ? round($actualKanban / $targetKanban * 100, 1) : 0,
                ];
            })
            ->values()
            ->all();

        return [
            'tanggal' => $date->toDateString(),
            'urutan' => 'target_qty (SUM total_qty detail) tertinggi ke terendah',
            'top_1' => $items[0] ?? null,
            'items' => $items,
        ];
    }

    private function buildInsights(Carbon $focusDate, string $focusLabel, array $loadingListQtyRanking): array
    {
        $top = $loadingListQtyRanking['top_1'] ?? null;

        return [
            'loading_list_qty' => [
                'label_tanggal' => $focusLabel,
                'tanggal' => $focusDate->toDateString(),
                'ranking' => $loadingListQtyRanking,
                'jawaban_singkat' => $top !== null
                    ? sprintf(
                        'Loading list qty tertinggi %s (%s): %s — target_qty %d (customer %s, cycle %s, PDS %s)',
                        $focusLabel,
                        $focusDate->toDateString(),
                        $top['loading_list'],
                        $top['target_qty'],
                        $top['customer'],
                        $top['cycle'],
                        $top['pds_number']
                    )
                    : sprintf('Tidak ada loading list dengan delivery %s (%s).', $focusLabel, $focusDate->toDateString()),
            ],
        ];
    }

    private function resolveQuestionDate(string $question): array
    {
        return $this->parseQuestionDate($question) ?? [Carbon::today(), 'hari ini'];
    }

    /** Tanggal spesifik yang disebut user (tertulis/angka/relatif/nama hari) — null jika tidak ada. */
    public function explicitQuestionDate(string $question): ?Carbon
    {
        $parsed = $this->parseQuestionDate($question);

        return $parsed === null ? null : $parsed[0];
    }

    /** Parse berbagai format tanggal dari pertanyaan → [Carbon, label] atau null. */
    private function parseQuestionDate(string $question): ?array
    {
        $q = Str::lower($question);
        $today = Carbon::today();

        // ISO: YYYY-MM-DD
        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $question, $m)) {
            $d = $this->safeDate((int) $m[1], (int) $m[2], (int) $m[3]);
            if ($d) {
                return [$d, $d->toDateString()];
            }
        }

        // Angka DD-MM-YYYY / DD/MM/YY(YY) / DD.MM.YYYY (day-first, konvensi Indonesia)
        if (preg_match('#\b(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2,4})\b#', $q, $m)) {
            $year = (int) $m[3] < 100 ? (int) $m[3] + 2000 : (int) $m[3];
            $d = $this->safeDate($year, (int) $m[2], (int) $m[1]);
            if ($d) {
                return [$d, $d->toDateString()];
            }
        }

        // "DD <bulan> [YYYY]" — bulan Indonesia/Inggris + singkatan
        $months = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'pebruari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'ags' => 8, 'agt' => 8, 'aug' => 8, 'august' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
            'november' => 11, 'nov' => 11, 'nop' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
        ];
        if (preg_match('/\b(\d{1,2})\s+([a-z]{3,12})\.?(?:\s+(\d{4}))?\b/', $q, $m) && isset($months[$m[2]])) {
            $year = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : (int) $today->year;
            $d = $this->safeDate($year, $months[$m[2]], (int) $m[1]);
            if ($d) {
                return [$d, $d->toDateString()];
            }
        }

        // "N hari (yang) lalu/sebelumnya"
        if (preg_match('/(\d{1,3})\s*hari\s*(?:yang\s*)?(?:lalu|sebelumnya)/', $q, $m)) {
            return [$today->copy()->subDays((int) $m[1]), $m[1].' hari lalu'];
        }

        if (str_contains($q, 'kemarin lusa')) {
            return [$today->copy()->subDays(2), 'kemarin lusa'];
        }
        if (str_contains($q, 'lusa')) {
            return [$today->copy()->addDays(2), 'lusa'];
        }
        if (str_contains($q, 'kemarin')) {
            return [$today->copy()->subDay(), 'kemarin'];
        }
        if (str_contains($q, 'besok') || str_contains($q, 'esok')) {
            return [$today->copy()->addDay(), 'besok'];
        }
        if (str_contains($q, 'hari ini') || str_contains($q, 'today') || str_contains($q, 'sekarang')) {
            return [$today->copy(), 'hari ini'];
        }

        // "tanggal DD" pada bulan berjalan
        if (preg_match('/tanggal\s*(\d{1,2})\b/', $q, $m)) {
            $d = $this->safeDate((int) $today->year, (int) $today->month, (int) $m[1]);
            if ($d) {
                return [$d, $d->toDateString()];
            }
        }

        // Nama hari → kejadian terakhir (≤ hari ini). "minggu" sengaja tidak dipetakan (ambigu dengan "minggu ini/lalu").
        $days = [
            'senin' => Carbon::MONDAY, 'monday' => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY, 'tuesday' => Carbon::TUESDAY,
            'rabu' => Carbon::WEDNESDAY, 'wednesday' => Carbon::WEDNESDAY,
            'kamis' => Carbon::THURSDAY, 'thursday' => Carbon::THURSDAY,
            'jumat' => Carbon::FRIDAY, 'friday' => Carbon::FRIDAY,
            'sabtu' => Carbon::SATURDAY, 'saturday' => Carbon::SATURDAY,
            'ahad' => Carbon::SUNDAY, 'sunday' => Carbon::SUNDAY,
        ];
        foreach ($days as $name => $dow) {
            if (preg_match('/\b'.$name.'\b/', $q)) {
                $d = $today->dayOfWeek === $dow ? $today->copy() : $today->copy()->previous($dow);

                return [$d, $name];
            }
        }

        return null;
    }

    /** Bentuk Carbon valid (tolak overflow seperti 31 Feb / bulan > 12) atau null. */
    private function safeDate(int $year, int $month, int $day): ?Carbon
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31 || $year < 2000 || $year > 2100) {
            return null;
        }

        try {
            $d = Carbon::create($year, $month, $day, 0, 0, 0);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $d instanceof Carbon || $d->day !== $day || $d->month !== $month || $d->year !== $year) {
            return null;
        }

        return $d->startOfDay();
    }

    private function preparationDeadlineForLoadingList($deliveryDate, $master): ?Carbon
    {
        $prepStart = $master->time ? substr((string) $master->time, 0, 5) : '';
        $prepEnd = $master->prep_end_time ? substr((string) $master->prep_end_time, 0, 5) : '';
        if ($prepStart === '' || $prepEnd === '') {
            return null;
        }

        $truckTime = $master->truck_time ? substr((string) $master->truck_time, 0, 5) : null;
        $prepDayOffset = $this->resolvePrepDayOffset((int) ($master->prep_day_offset ?? 0), $prepStart, $truckTime);
        $prepEndDayOffset = $this->computePrepEndDayOffset($prepDayOffset, $prepStart, $prepEnd);

        return Carbon::parse($deliveryDate)
            ->startOfDay()
            ->addDays($prepEndDayOffset)
            ->setTimeFromTimeString($prepEnd);
    }

    private function resolvePrepDayOffset(int $storedOffset, string $prepStart, ?string $truckTime): int
    {
        if ($truckTime !== null && $this->clockMinutes($prepStart) > $this->clockMinutes($truckTime)) {
            return -1;
        }

        return max(-14, min(14, $storedOffset));
    }

    private function computePrepEndDayOffset(int $prepDayOffset, string $prepStart, string $prepEnd): int
    {
        return $this->clockMinutes($prepEnd) <= $this->clockMinutes($prepStart)
            ? $prepDayOffset + 1
            : $prepDayOffset;
    }

    private function clockMinutes(string $clock): int
    {
        [$hour, $minute] = array_pad(explode(':', substr(trim($clock), 0, 5)), 2, '0');

        return ((int) $hour * 60) + (int) $minute;
    }

    private function normalizeCycleLabel($cycle): string
    {
        return Str::lower(trim((string) $cycle));
    }

    private function cycleDigit($cycle): string
    {
        preg_match('/\d+/', (string) $cycle, $match);

        return $match[0] ?? '';
    }

    private function formatDelayLabel(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        if ($hours > 0 && $mins > 0) {
            return '+'.$hours.'j '.$mins.'m';
        }
        if ($hours > 0) {
            return '+'.$hours.'j';
        }

        return '+'.$minutes.'m';
    }

    private function resolveModules(string $question): array
    {
        $q = strtolower($question);
        $map = [
            'loading_list_pds' => ['loading list', 'loading-list', 'll ', 'pds', 'delivery', 'shipping', 'kirim', 'muat', 'truck', 'dock', 'manifest', 'prep', 'preparation', 'persiapan', 'deadline', 'telat', 'terlambat', 'ppic', 'kanban', 'belum lengkap', 'qty', 'quantity', 'paling banyak', 'terbanyak', 'terbesar', 'paling besar'],
            'pulling' => ['pulling', 'pull ', 'tarik', 'ditarik', 'narik', 'checkout'],
            'production_plan' => ['production plan', 'plan produksi', 'plan ', 'rencana', 'jadwal', 'order qty', 'order ', 'dn number', 'dn ', 'balance time', 'target produksi'],
            'production_actual_scan' => ['part scan', 'scan part', 'produksi', 'produski', 'output', 'hasil produksi', 'dandori', 'supply', 'aktual'],
            'stock' => ['stock', 'stok', 'understock', 'inventory', 'inventori', 'persediaan', 'back number', 'back no', 'part number', 'chute'],
            'pis' => ['pis', 'packing', 'inspection sheet', 'part inspection'],
            'receiving' => ['receiving', 'receive', 'kedatangan', 'datang', 'material', 'supplier', 'pick list', 'incoming'],
            'quality' => ['quality', 'kualitas', 'mutu', 'inspeksi', 'judgement', 'approval', 'not ok', 'ng ', 'reject', 'cacat', 'defect', 'checksheet', 'qc'],
            'error_log' => ['error', 'gagal', 'salah', 'kesalahan', 'masalah', 'trouble', 'issue'],
            'mutation_stock_flow' => ['mutation', 'mutasi', 'checkout', 'supply', 'aktivitas', 'heatmap', 'arus stok', 'flow'],
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

        // Pertanyaan umum/tak terklasifikasi: muat SEMUA modul (token-fitter otomatis memampatkan bila kegedean).
        if ($modules === []) {
            return array_keys($map);
        }

        return array_values(array_unique($modules));
    }

    private function expandRelatedModules(array $modules): array
    {
        $related = [
            'pis' => ['loading_list_pds'],
            'loading_list_pds' => ['pulling'],
            'pulling' => ['loading_list_pds', 'mutation_stock_flow'],
            'production_plan' => ['production_actual_scan', 'stock'],
            'production_actual_scan' => ['production_plan'],
            'quality' => ['production_actual_scan'],
            'error_log' => ['mutation_stock_flow'],
        ];

        $expanded = $modules;
        foreach ($modules as $module) {
            foreach ($related[$module] ?? [] as $extra) {
                $expanded[] = $extra;
            }
        }

        return array_values(array_unique($expanded));
    }

    private function dataDictionary(): array
    {
        return [
            'preparation' => 'Progress kanban loading list: actual_kanban vs target_kanban. Lengkap jika actual >= target.',
            'pulling' => 'Aktivitas pull part ke line; pulling_date = waktu pull.',
            'pis' => 'Part Inspection Sheet scan; target_qty/scanned_qty/remaining_qty dari pis_scan_details.',
            'stock' => 'Snapshot stok chute: current_stock vs standard_stock. under = di bawah standard.',
            'error_log' => 'Log error operasional per area (pulling, produksi, PIS, dll).',
            'delivery_berisiko' => 'Loading list hari ini/besok yang belum 100% preparation + urgency deadline.',
            'search_hits' => 'Hasil pencarian keyword dari pertanyaan user — prioritas untuk pertanyaan spesifik.',
            'fokus_tanggal' => 'Subset data untuk tanggal yang disebut user (hari ini/kemarin/besok/lusa/nama hari/N hari lalu/tanggal DD/YYYY-MM-DD).',
            'referensi' => 'Daftar nilai valid di sistem: line, customer, area error, judgement quality. Pakai untuk memetakan istilah user ke data & menjawab "apa saja X".',
        ];
    }

    /** Daftar nilai valid (line, customer, area error, judgement) agar model bisa memetakan istilah user ke data nyata. Di-cache 5 menit (nyaris statis). */
    private function buildReferenceData(): array
    {
        return Cache::remember('bella_ai_reference_v1', 300, function () {
            $ref = [];

            if ($this->tableExists('lines')) {
                $ref['lines'] = DB::table('lines')->orderBy('name')->limit(40)->pluck('name')->filter()->values()->all();
            }
            if ($this->tableExists('customers')) {
                $ref['customers'] = DB::table('customers')->orderBy('name')->limit(60)->pluck('name')->filter()->values()->all();
            }
            if ($this->tableExists('error_logs')) {
                $ref['error_areas'] = DB::table('error_logs')->select('area')->distinct()->orderBy('area')->limit(30)->pluck('area')->filter()->values()->all();
            }
            if ($this->tableExists('quality_inspections')) {
                $ref['quality_judgements'] = DB::table('quality_inspections')->select('judgement')->distinct()->limit(15)->pluck('judgement')->filter()->values()->all();
                $ref['quality_check_types'] = DB::table('quality_inspections')->select('check_type')->distinct()->limit(15)->pluck('check_type')->filter()->values()->all();
            }

            return array_filter($ref, fn ($items) => is_array($items) && count($items) > 0);
        });
    }

    private function buildOperationalSnapshot(array $data, Carbon $from, Carbon $to): array
    {
        $kpi = $data['kpi'] ?? [];
        $stock = $data['stock'] ?? [];
        $atRisk = $data['atRisk'] ?? [];

        return [
            'periode' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'preparation' => [
                'total_loading_list' => (int) ($kpi['prepTotal'] ?? 0),
                'lengkap' => (int) ($kpi['prepComplete'] ?? 0),
                'pct_lengkap' => (int) ($kpi['prepPct'] ?? 0),
            ],
            'pulling' => [
                'mutasi_checkout' => (int) ($kpi['kanbanScanned'] ?? 0),
                'record_pulling' => (int) ($kpi['pullingDone'] ?? 0),
            ],
            'produksi' => [
                'mutasi_supply' => (int) ($kpi['produksi'] ?? 0),
            ],
            'pis' => [
                'scan_log_periode' => (int) ($kpi['pisScan'] ?? 0),
                'detail_lengkap_snapshot' => (int) ($kpi['pisDone'] ?? 0),
            ],
            'stok' => [
                'total_part' => (int) ($kpi['totalPart'] ?? 0),
                'under_stock' => (int) ($stock['under'] ?? $kpi['underStock'] ?? 0),
                'low_stock' => (int) ($stock['low'] ?? 0),
                'in_stock' => (int) ($stock['in'] ?? 0),
            ],
            'error' => [
                'total_periode' => (int) ($kpi['errPeriod'] ?? 0),
                'error_rate_pct' => (float) ($data['errRate'] ?? 0),
            ],
            'delivery_berisiko_jumlah' => count($atRisk),
            'delta_vs_periode_sebelumnya' => $data['delta'] ?? [],
        ];
    }

    private function buildSearchHits(string $question, Carbon $from, Carbon $to): array
    {
        $terms = $this->searchTerms($question);
        $limit = count($terms) > 0 ? 12 : 0;
        if ($limit === 0) {
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

        if ($this->tableExists('loading_lists')) {
            $query = DB::table('loading_lists as ll')->leftJoin('customers as c', 'c.id', '=', 'll.customer_id');
            $matches['loading_list_pds'] = $this->rows($applySearch($query, ['ll.number', 'll.pds_number', 'll.cycle', 'c.name'])
                ->select('ll.id as loading_list_id', 'll.number as loading_list', 'll.pds_number', 'll.cycle', 'll.delivery_date', 'll.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('ll.delivery_date')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('pullings')) {
            $query = DB::table('pullings as p')->leftJoin('customers as c', 'c.id', '=', 'p.customer_id');
            $matches['pulling'] = $this->rows($applySearch($query, ['p.loading_list', 'p.pds_number', 'p.cycle', 'c.name'])
                ->select('p.loading_list as loading_list', 'p.pds_number', 'p.cycle', 'p.pulling_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('p.pulling_date')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('internal_parts')) {
            $query = DB::table('internal_parts as ip')->leftJoin('lines as l', 'l.id', '=', 'ip.line_id');
            $matches['master_part_stock'] = $this->rows($applySearch($query, ['ip.part_number', 'ip.back_number', 'ip.part_name', 'l.name'])
                ->leftJoin('production_stocks as ps', 'ps.internal_part_id', '=', 'ip.id')
                ->select('ip.part_number', 'ip.back_number', 'ip.part_name', DB::raw('COALESCE(l.name, "-") as line'), DB::raw('COALESCE(ip.standard_stock, 0) as standard_stock'), DB::raw('COALESCE(ps.current_stock, 0) as current_stock'))
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('production_plans')) {
            $query = DB::table('production_plans as pp');
            $matches['production_plan'] = $this->rows($applySearch($query, ['pp.line', 'pp.customer', 'pp.dock', 'pp.back_no', 'pp.dn_number'])
                ->select('pp.plan_date', 'pp.line', 'pp.customer', 'pp.dock', 'pp.cycle', 'pp.back_no', 'pp.order_qty', 'pp.direct_pulling_qty', 'pp.delivery_time', 'pp.balance_time', 'pp.dn_number')
                ->orderByDesc('pp.plan_date')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('pis_scans')) {
            $query = DB::table('pis_scans as ps')->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id');
            $matches['pis'] = $this->rows($applySearch($query, ['ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'c.name'])
                ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'ps.delivery_date', 'ps.shipping_date', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('ps.delivery_date')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('quality_inspections')) {
            $query = DB::table('quality_inspections as qi');
            $matches['quality'] = $this->rows($applySearch($query, ['qi.kanban_code', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.line', 'qi.pic', 'qi.judgement', 'qi.approval_status'])
                ->select('qi.kanban_code', 'qi.check_type', 'qi.line', 'qi.shift', 'qi.pic', 'qi.part_name', 'qi.part_number', 'qi.back_number', 'qi.judgement', 'qi.approval_status', 'qi.checked_at')
                ->orderByDesc('qi.checked_at')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('error_logs')) {
            $query = DB::table('error_logs as el');
            $matches['error_log'] = $this->rows($applySearch($query, ['el.area', 'el.message', 'el.expected', 'el.scanned'])
                ->select('el.area', 'el.message', 'el.expected', 'el.scanned', 'el.date')
                ->orderByDesc('el.date')
                ->limit($limit)
                ->get());
        }

        if ($this->tableExists('pis_scans') && $this->tableExists('pis_scan_details')) {
            $query = DB::table('pis_scans as ps')
                ->join('pis_scan_details as d', 'd.pis_scan_id', '=', 'ps.id')
                ->leftJoin('customers as c', 'c.id', '=', 'ps.customer_id')
                ->whereBetween('ps.delivery_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
            $matches['pis_detail'] = $this->rows($applySearch($query, ['ps.loading_list_number', 'ps.pds_number', 'd.part_number_int', 'd.part_number_cust'])
                ->select('ps.loading_list_number', 'ps.pds_number', 'ps.cycle', 'd.part_number_int', 'd.part_number_cust', 'd.target_qty', 'd.scanned_qty', 'd.remaining_qty', DB::raw('COALESCE(c.name, "Unknown") as customer'))
                ->orderByDesc('ps.delivery_date')
                ->limit($limit)
                ->get());
        }

        return ['terms' => $terms, 'matches' => array_filter($matches)];
    }

    private function searchTerms(string $question): array
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

    private function formatRows($rows): array
    {
        return collect($rows)->map(function ($row) {
            $item = (array) $row;
            foreach ($item as $key => $value) {
                if (is_string($value) && preg_match('/date|time|at$/i', (string) $key)) {
                    $item[$key] = $this->formatDateValue($value);
                }
            }

            return $item;
        })->values()->all();
    }

    private function formatDateValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function rows($rows): array
    {
        return collect($rows)->map(fn ($row) => (array) $row)->values()->all();
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
