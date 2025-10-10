<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Supplier;
use App\Imports\PartImport;
use App\Models\Agstar\Ia31;
use App\Traits\prodPlanOps;
use App\Imports\StockImport;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Models\ProductionPlan;
use App\Imports\ManifestImport;
use App\Models\ReceiveSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    use prodPlanOps;
    public function index()
    {

        $lines = [];

        // get all current qty of all internal parts 
        $data = DB::table('internal_parts')
            ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->select('lines.name', 'production_stocks.internal_part_id as id', 'internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.current_stock', 'internal_parts.standard_stock')
            ->groupBy('internal_parts.part_number', 'internal_parts.back_number', 'production_stocks.internal_part_id', 'lines.name', 'production_stocks.current_stock', 'internal_parts.standard_stock')
            ->get();

        foreach ($data as $value) {
            $lineFound = false;
            // Check if line already exists in $lines array
            foreach ($lines as $line) {
                if ($line->line === $value->name) {
                    $lineFound = true;
                    $line->items[] = [
                        'id' => $value->id,
                        'part_number' => $value->part_number,
                        'back_number' => $value->back_number,
                        'qty' => $value->current_stock,
                        'standard' => $value->standard_stock,
                    ];
                    break;
                }
            }
            // If line doesn't exist, create a new object and add it to $lines array
            if (!$lineFound) {
                $lineObject = (object) [
                    'line' => $value->name,
                    'items' => [
                        [
                            'id' => $value->id,
                            'part_number' => $value->part_number,
                            'back_number' => $value->back_number,
                            'qty' => $value->current_stock,
                            'standard' => $value->standard_stock,
                        ],
                    ],
                ];
                $lines[] = $lineObject;
            }
        }

        return view('pages.dashboard', [
            'lines' => $lines,
        ]);
    }

    public function prodResult(Request $request)
    {
        $selectedDate = $request->input('date') ?? now()->toDateString(); // default hari ini

        $data = DB::table('mutations')
            ->join('internal_parts', 'mutations.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->where('mutations.type', 'supply')
            ->whereDate('mutations.created_at', $selectedDate) // ✅ ambil berdasarkan tanggal input
            ->select(
                'internal_parts.back_number',
                'mutations.serial_number',
                'mutations.type',
                'mutations.qty',
                'mutations.date',
                'lines.name as line',
                'internal_parts.id as internal_part_id'
            )
            ->orderBy('mutations.date', 'desc')
            ->get();

        // Organisasi data
        $lines = [];
        foreach ($data as $value) {
            $lineKey = $value->line;
            $backNumber = $value->back_number;

            if (!isset($lines[$lineKey])) {
                $lines[$lineKey] = [
                    'line' => $lineKey,
                    'items' => []
                ];
            }

            if (!isset($lines[$lineKey]['items'][$backNumber])) {
                $lines[$lineKey]['items'][$backNumber] = [
                    'back_number' => $backNumber,
                    'internal_part_id' => $value->internal_part_id,
                    'details' => []
                ];
            }

            $lines[$lineKey]['items'][$backNumber]['details'][] = [
                'serial_number' => $value->serial_number,
                'qty' => $value->qty,
                'date' => $value->date,
            ];
        }

        $result = [];
        foreach ($lines as $line) {
            $line['items'] = array_values($line['items']);
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

    public function prodPlan(Request $request)
    {
        set_time_limit(90);

        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10);
        $end   = $start->copy()->addDay();

        $allBackNos   = collect($this->backNosByLine)->flatten()->unique()->values();
        $forceRefresh = $request->has('force_refresh');

        $lastUpdate = ProductionPlan::where('plan_date', $today->format('Y-m-d'))->max('updated_at');

        // === NEW: cek signature MSSQL ===
        $sigKey  = 'pulling:sig:'.$selectedDate->format('Ymd');
        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();
        $curSig  = $this->externalSignature($selectedDate, $allBackNos); // pakai default excluded
        $prevSig = Cache::get($sigKey);

        $shouldRefresh =
            $forceRefresh ||
            !$lastUpdate ||
            !$prevSig ||
            ($curSig && $curSig !== $prevSig);

        if ($shouldRefresh) {
            try {
                DB::beginTransaction();

                $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
                if ($rawData->isEmpty()) {
                    throw new \Exception("No production data available");
                }

                $processedData = $this->processRawData($rawData, $start, $end);
                $this->updateProductionData($processedData, $this->backNosByLine, $today);

                DB::commit();

                Cache::put($sigKey, $curSig, now()->addHours(6)); // simpan signature terkini
                $lastUpdate  = now();
                $message     = 'Production data updated (upstream changed).';
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
            'grouped'      => $grouped,
            'lastUpdate'   => $lastUpdate ?? now(),
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'message'      => $message ?? null,
            'messageType'  => $messageType ?? null,
        ]);
    }

    public function prodBoard(Request $request)
    {
        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        [$boards] = $this->buildBoardsForDate_($selectedDate);

        return view('pages.pulling.board', [
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'boards'       => $boards,
        ]);
    }

    /**
     * JSON state untuk Board (dipanggil ketika ada SSE update).
     * GET /pulling/board/state?date=YYYY-MM-DD
     */
    public function prodBoardState(Request $request)
    {
        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        [$boards, $stamp] = $this->buildBoardsForDate_($selectedDate);

        return response()->json([
            'date'   => $selectedDate->format('Y-m-d'),
            'at'     => $stamp,       // time of compute
            'boards' => $boards,      // per line
        ]);
    }

    /**
     * ===== Helper utama: hitung state board per tanggal =====
     * - Per line (AS003/AS004) tentukan:
     *   progress (shift), current, nextHighlight, nextList
     * - AUTO-ADVANCE: jika current.complete → next naik, list kiri naik ke next
     */
    private function buildBoardsForDate_(Carbon $selectedDate): array
    {
        $todayISO = $selectedDate->toDateString();
        $nowHHmm  = now()->format('H:i');
        $stamp    = now()->format('H:i:s');

        // helper HH:ii -> menit
        $toMin = function (?string $t) {
            if (!$t || strpos($t, ':') === false) return null;
            [$h,$m] = array_map('intval', explode(':', $t));
            return $h*60 + $m;
        };
        $nowMin = $toMin($nowHHmm);

        // Ambil semua plan hari itu
        $items = ProductionPlan::whereDate('plan_date', $todayISO)
            ->orderBy('working_start')   // aman lexicographic
            ->orderBy('dn_number')
            ->get();

        // Kelompok per line
        $LINES = ['AS003','AS004'];
        $getLineKey = function ($it) {
            $v = strtoupper($it->line_code ?? $it->line ?? $it->assembly_line ?? '');
            return in_array($v, ['AS003','AS004'], true) ? $v : null;
        };
        $byLine = ['AS003' => collect(), 'AS004' => collect()];
        foreach ($items as $it) {
            $key = $getLineKey($it);
            if ($key) $byLine[$key]->push($it);
        }

        // Data shift per line (pakai fungsi existing dari controller ini)
        $grouped = $this->getGroupedData($this->backNosByLine, $selectedDate);

        $buildBoardForLine = function (\Illuminate\Support\Collection $rows, string $lineKey) use ($toMin, $nowMin, $grouped) {

            // Progress (shift) dari grouped (sinkron halaman planning)
            $g    = $grouped[$lineKey] ?? [];
            $mQty = (int)($g['morning_shift_qty']    ?? 0);
            $mAct = (int)($g['morning_shift_actual'] ?? 0);
            $nQty = (int)($g['night_shift_qty']      ?? 0);
            $nAct = (int)($g['night_shift_actual']   ?? 0);
            [$shiftLabel, $orderQty, $actualQty, $status] = $this->resolveShiftProgress($nowMin, $mQty, $mAct, $nQty, $nAct);

            // Current
            $currentItem = $rows->first(function($it) use ($toMin, $nowMin){
                $s = $toMin($it->working_start);
                $e = $toMin($it->working_end);
                return $s !== null && $e !== null && $s <= $nowMin && $nowMin <= $e;
            });
            if (!$currentItem) {
                $currentItem = $rows->first(function($it) use ($toMin, $nowMin){
                    $s = $toMin($it->working_start);
                    return $s !== null && $s > $nowMin;
                }) ?? $rows->last();
            }

            $current = $currentItem ? [
                'back_no'       => $currentItem->back_no,
                'customer'      => $currentItem->customer,
                'dock'          => $currentItem->dock,
                'order_qty'     => (int) $currentItem->order_qty,
                'dp'            => (int) ($currentItem->direct_pulling_qty ?? 0),
                'sc'            => (int) ($currentItem->stock_chute_qty ?? 0),
                'start'         => $currentItem->working_start ?: '--',
            ] : [
                'back_no'   => '—','customer'=>'—','dock'=>'—','order_qty'=>0,'dp'=>0,'sc'=>0,'start'=>'--'
            ];

            // Next candidates = start >= now
            $nextCandidates = $rows->filter(function($it) use ($toMin, $nowMin){
                $s = $toMin($it->working_start);
                return $s !== null && $s >= $nowMin;
            })->values();

            $nh = $nextCandidates->first();
            $nextHighlight = $nh ? [
                'back_no'       => $nh->back_no,
                'customer'      => $nh->customer,
                'dock'          => $nh->dock,
                'order_qty'     => (int) $nh->order_qty,
                'delivery_time' => $nh->delivery_time ?: '--',
                'delivery_date' => $nh->delivery_date ?: '',
            ] : ['back_no'=>'—','customer'=>'—','dock'=>'—','order_qty'=>0,'delivery_time'=>'--','delivery_date'=>''];

            $nextList = $nextCandidates->slice(1, 20)->map(function($it){
                return [
                    'back_no'       => $it->back_no,
                    'customer'      => $it->customer,
                    'dock'          => $it->dock,
                    'order_qty'     => (int) $it->order_qty,
                    'delivery_time' => $it->delivery_time ?: '--',
                    'delivery_date' => $it->delivery_date ?: '',
                ];
            })->values()->all();

            // ===== AUTO-ADVANCE (server-side) =====
            $curOrder = (int)($current['order_qty'] ?? 0);
            $curDone  = (int)($current['dp'] ?? 0) + (int)($current['sc'] ?? 0);
            $hasNextCard = ($nextHighlight['back_no'] ?? '—') !== '—';

            if ($curOrder > 0 && $curDone >= $curOrder && $hasNextCard) {
                // next → current
                $current = [
                    'back_no'   => $nextHighlight['back_no'],
                    'customer'  => $nextHighlight['customer'],
                    'dock'      => $nextHighlight['dock'],
                    'order_qty' => (int)$nextHighlight['order_qty'],
                    'dp'        => 0,
                    'sc'        => 0,
                    'start'     => '--',
                ];

                // list[0] → next
                if (!empty($nextList)) {
                    $first = array_shift($nextList);
                    $nextHighlight = [
                        'back_no'       => $first['back_no'],
                        'customer'      => $first['customer'],
                        'dock'          => $first['dock'],
                        'order_qty'     => (int)$first['order_qty'],
                        'delivery_time' => $first['delivery_time'] ?? '--',
                        'delivery_date' => $first['delivery_date'] ?? '',
                    ];
                } else {
                    $nextHighlight = ['back_no'=>'—','customer'=>'—','dock'=>'—','order_qty'=>0,'delivery_time'=>'--','delivery_date'=>''];
                }
            }

            return [
                'progress' => [
                    'label'  => $shiftLabel,
                    'order'  => $orderQty,
                    'actual' => $actualQty,
                    'status' => $status, // S1/NS/LS1/LS3
                ],
                'current'       => $current,
                'nextHighlight' => $nextHighlight,
                'nextList'      => $nextList,
            ];
        };

        $boards = [];
        foreach ($LINES as $L) {
            $boards[$L] = $buildBoardForLine($byLine[$L] ?? collect(), $L);
        }

        return [$boards, $stamp];
    }


    /** Khusus penyusunan data tabel + KPI shift (delivery-based 09:40 rule) */
    protected function getGroupedData($backNosByLine, $today)
    {
        $grouped   = [];
        $todayISO  = $today->toDateString();
        $nextISO   = $today->copy()->addDay()->toDateString();

        // Window (menit)
        $MORNING_START = 12*60;          // 12:00
        $MORNING_END   = 22*60 + 57;     // 22:57
        $NIGHT_START   = 22*60 + 59;     // 22:59
        $NIGHT_END     =  9*60 + 35;     // 09:35

        $toMin = function ($t) {
            if (!$t) return null;
            try {
                [$h, $m] = array_map('intval', explode(':', $t));
                if (!is_numeric($h) || !is_numeric($m)) return null;
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
                $em = $toMin($item->working_end   ?? null);
                $in = function($min) use ($MORNING_START, $MORNING_END) {
                    return $min !== null && $min >= $MORNING_START && $min <= $MORNING_END;
                };
                return $in($sm) || $in($em);
            };

            $isNight = function ($item) use ($todayISO, $nextISO, $toMin, $NIGHT_START, $NIGHT_END) {
                $dd = $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->toDateString() : null;
                $tm = $toMin($item->delivery_time ?? null);

                // Night = HARI INI >=22:59 ATAU HARI BESOK <=09:35
                if ($dd && $tm !== null) {
                    return ($dd === $todayISO  && $tm >= $NIGHT_START)
                        || ($dd === $nextISO   && $tm <= $NIGHT_END);
                }

                // Fallback: pakai working time (menit) – melintasi tengah malam
                $sm = $toMin($item->working_start ?? null);
                $em = $toMin($item->working_end   ?? null);
                $in = function($min) use ($NIGHT_START, $NIGHT_END) {
                    return $min !== null && ($min >= $NIGHT_START || $min <= $NIGHT_END);
                };
                return $in($sm) || $in($em);
            };

            $morningItems = $lineData->filter($isMorning);
            $nightItems   = $lineData->filter($isNight);

            $morningShiftQty    = (int) $morningItems->sum('order_qty');
            $nightShiftQty      = (int) $nightItems->sum('order_qty');
            $morningShiftActual = (int) $morningItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));
            $nightShiftActual   = (int) $nightItems->sum(fn($i) => (int) ($i->direct_pulling_qty ?? 0));

            $grouped[$line] = [
                'data' => $lineData->groupBy(function ($item) {
                    $cust = trim((string)($item->customer ?? '')) ?: '--';
                    $time = trim((string)($item->delivery_time ?? '')) ?: '--';
                    $dock = trim((string)($item->dock ?? '')) ?: '--';
                    return "{$cust}|{$time}|{$dock}";
                }),
                'morning_shift_qty'    => $morningShiftQty,
                'night_shift_qty'      => $nightShiftQty,
                'total_qty'            => $morningShiftQty + $nightShiftQty,
                'morning_shift_actual' => $morningShiftActual,
                'night_shift_actual'   => $nightShiftActual,
                'total_actual'         => $morningShiftActual + $nightShiftActual,
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
        $MORNING_START = 12*60;          // 720
        $MORNING_END   = 22*60 + 57;     // 1377
        $NIGHT_START   = 22*60 + 59;     // 1379
        $NIGHT_END     =  9*60 + 35;     // 575

        // default: pilih shift yang “berlaku” sekarang; kalau di gap, anggap Morning
        $label = 'Morning'; $order = $mQty; $actual = $mAct;
        $elapsed = 0; $duration = max(1, $MORNING_END - $MORNING_START); // 657

        if ($nowMin >= $MORNING_START && $nowMin <= $MORNING_END) {
            $label = 'Morning';
            $order = $mQty; $actual = $mAct;
            $elapsed = $nowMin - $MORNING_START;
            $duration = max(1, $MORNING_END - $MORNING_START);
        } elseif ($nowMin >= $NIGHT_START || $nowMin <= $NIGHT_END) {
            $label = 'Night';
            $order = $nQty; $actual = $nAct;
            // durasi night melewati tengah malam
            $duration = (1440 - $NIGHT_START) + $NIGHT_END; // 61 + 575 = 636
            $elapsed  = ($nowMin >= $NIGHT_START)
                ? ($nowMin - $NIGHT_START)
                : ((1440 - $NIGHT_START) + $nowMin);
        }

        // status sederhana: S1 (OK), NS (no start > 60min), LS1 (di bawah expected progress)
        if ($order <= 0) {
            return [$label, 0, 0, 'S1'];
        }

        $ratio    = $actual / max(1,$order);
        $expected = min(1, $elapsed / max(1,$duration)); // linear expectation
        $status   = 'S1';

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
        if (!$balance || !preg_match('/^-?\d{2}:\d{2}$/', $balance)) {
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
        Excel::import(new PartImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Part berhasil diupload!');
    }

    public function importManifest(Request $request)
    {
        Excel::import(new ManifestImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Manifest berhasil diupload!');
    }

    public function importStock(Request $request)
    {
        Excel::import(new StockImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Stock berhasil diupload!');
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
            ->where('area', 'LIKE', '%' . $area . '%') // Filter berdasarkan area
            ->get();

        $seriesData = [];

        foreach ($deliveries as $delivery) {
            $date = \Carbon\Carbon::parse($delivery->delivery_date);

            // Lewati data jika tidak termasuk minggu ini
            if (!$date->between($startOfWeek, $endOfWeek)) {
                continue;
            }

            // Format "00:HH:MM", ambil HH dan MM
            $timeParts = explode(':', $delivery->delivery_time);
            $hour = (int)($timeParts[1] ?? 0);
            $minute = (int)($timeParts[2] ?? 0);

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
            'data' => $seriesData
        ]];
        $now = now();

        if ($now->between($startOfWeek, $endOfWeek)) {
            $annotations = $now->timestamp * 1000;
        } else {
            $annotations = null;
        }

        return view('pages.dashboard_receiving', [
            'series' => $series,
            'annotationTimestamp' => $annotations
        ]);
    }
    function applyTimeToDate(Carbon $date, string $time)
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
        if (!$internalPart) {
            return back()->with([
                'error' => "Back Number <strong>{$request->back_number}</strong> tidak ditemukan."
            ])->withInput();
        }

        // Ambil kanban berdasarkan internal_part_id dan serial_number
        $kanban = DB::table('kanbans')
            ->where('internal_part_id', $internalPart->id)
            ->where('serial_number', $request->serial_number)
            ->first();

        // Jika kanban tidak ditemukan
        if (!$kanban) {
            return view('pages.production.kbnCheck', compact('internalPart'))
                ->with([
                    'back_number' => $request->back_number,
                    'serial_number' => $request->serial_number,
                    'error' => "Serial Number <strong>{$request->serial_number}</strong> tidak ditemukan untuk Back Number <strong>{$request->back_number}</strong>."
                ]);
        }

        // Tampilkan hasil jika ditemukan
        return view('pages.production.kbnCheck', compact('internalPart', 'kanban'))
            ->with([
                'back_number' => $request->back_number,
                'serial_number' => $request->serial_number
            ]);
    }
}
