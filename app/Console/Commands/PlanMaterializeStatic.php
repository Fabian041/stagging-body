<?php

// app/Console/Commands/PlanMaterializeStatic.php
namespace App\Console\Commands;

use App\Models\ProductionPlan;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PlanMaterializeStatic extends Command
{
    protected $signature = 'plan:materialize-static
                            {--date= : YYYY-MM-DD (default: today)}
                            {--lines= : Comma list, ex: MA001,MA002 (default: all MA in master)}
                            {--reset : Delete static rows first for that date}';

    protected $description = 'Materialize static sequences (MA001–MA008) into production_plans for a date';

    private function normLine(string $s): string
    {
        $s = strtoupper(trim($s));
        if (preg_match('/^MA(\d)$/i', $s, $m))  return sprintf('MA%03d', (int)$m[1]);  // MA1  -> MA001
        if (preg_match('/^MA(\d{2})$/i', $s, $m)) return sprintf('MA%03d', (int)$m[1]); // MA12 -> MA012
        return $s;
    }

    public function handle()
    {
        $date     = $this->option('date') ? Carbon::parse($this->option('date'))->toDateString() : now()->toDateString();
        $linesOpt = $this->option('lines');

        // Ambil daftar line dari master (PAKAI kolom `line`, bukan `line_code`)
        $allInMaster = DB::table('line_static_sequences')
            ->select('line')->where('is_active', 1)->distinct()
            ->pluck('line')
            ->map(fn ($v) => $this->normLine($v))
            ->unique()->values();

        $lines = $linesOpt
            ? collect(explode(',', $linesOpt))->map(fn ($s) => $this->normLine($s))->filter()->values()
            : $allInMaster;

        if ($lines->isEmpty()) {
            $this->warn('No MA lines in master.');
            return self::SUCCESS;
        }

        if ($this->option('reset')) {
            ProductionPlan::whereDate('plan_date', $date)
                ->where('plan_source', 'static')
                ->whereIn('line', $lines)
                ->delete();
        }

        foreach ($lines as $L) {
            $rows = DB::table('line_static_sequences')
                ->where('is_active', 1)
                ->where('line', $L)             // <<— ganti line_code -> line
                ->orderBy('seq_no')
                ->get();

            if ($rows->isEmpty()) {
                $this->warn("No static rows for {$L} — skip");
                continue;
            }

            $cursor = Carbon::parse($date . ' 06:00:00'); // start kerja default (ubah bila perlu)

            foreach ($rows as $r) {
                $order = (int)($r->default_order_qty ?? 0);
                $prod  = $r->default_prod_time ? Carbon::parse($r->default_prod_time)->format('i:s') : '00:00';

                [$mm, $ss] = array_map('intval', explode(':', $prod));
                $durSec    = ($mm * 60 + $ss) * max(1, $order);

                $start = $cursor->copy();
                $end   = $cursor->copy()->addSeconds($durSec);

                // Preserve actuals jika sudah ada
                $where = [
                    'plan_date' => $date,
                    'line'      => $L,
                    'back_no'   => $r->back_no,
                    'dn_number' => 'STATIC-' . $r->seq_no, // unique harian per seq
                ];
                $existing = ProductionPlan::where($where)->first();

                $payload = [
                    'customer'           => '—',
                    'dock'               => $r->dock_hint ?? '—', // DOM/EXP/… untuk CI13
                    'cycle'              => null,
                    'order_qty'          => $order,
                    'prod_time'          => $prod,                 // mm:ss
                    'working_start'      => $start->format('H:i'),
                    'working_end'        => $end->format('H:i'),
                    'working_duration'   => gmdate('H:i:s', max(0, $durSec)),
                    'delivery_time'      => '--',
                    'delivery_date'      => $date,
                    'balance_time'       => null,
                    'plan_source'        => 'static',
                    'seq_no'             => (int)$r->seq_no,
                    'updated_at'         => now(),
                ];

                if ($existing) {
                    $payload['direct_pulling_qty'] = $existing->direct_pulling_qty;
                    $payload['stock_chute_qty']    = $existing->stock_chute_qty;
                    // jaga created_at lama
                    $payload['created_at']         = $existing->created_at ?? now();
                } else {
                    $payload['direct_pulling_qty'] = 0;
                    $payload['stock_chute_qty']    = 0;
                    $payload['created_at']         = now();
                }

                ProductionPlan::updateOrCreate($where, $payload);

                $cursor = $end;
            }

            $this->info("Materialized {$rows->count()} rows into {$L} for {$date}");
        }

        return self::SUCCESS;
    }
}
