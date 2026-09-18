<?php

namespace App\Services\Ai;

use Carbon\Carbon;

class BellaBriefService
{
    public function build(array $overview, Carbon $date): array
    {
        $kpi = $overview['kpi'] ?? [];
        $delta = $overview['delta'] ?? [];
        $atRisk = collect($overview['atRisk'] ?? []);
        $topUnder = collect($overview['topUnder'] ?? [])->take(3);
        $errRate = (float) ($overview['errRate'] ?? 0);

        $prepTotal = (int) ($kpi['prepTotal'] ?? 0);
        $prepComplete = (int) ($kpi['prepComplete'] ?? 0);
        $prepPct = (int) ($kpi['prepPct'] ?? 0);
        $underStock = (int) ($kpi['underStock'] ?? 0);
        $errPeriod = (int) ($kpi['errPeriod'] ?? 0);
        $atRiskCount = $atRisk->count();

        $bullets = [
            "Preparation: {$prepComplete}/{$prepTotal} loading list lengkap ({$prepPct}%).",
            'Pulling: '.number_format((int) ($kpi['pullingDone'] ?? 0), 0, ',', '.').' record · Kanban scan: '.number_format((int) ($kpi['kanbanScanned'] ?? 0), 0, ',', '.').'.',
            'Produksi: '.number_format((int) ($kpi['produksi'] ?? 0), 0, ',', '.').' supply · PIS scan: '.number_format((int) ($kpi['pisScan'] ?? 0), 0, ',', '.').'.',
        ];

        if ($underStock > 0) {
            $bullets[] = "Stok: {$underStock} part understock perlu dicek.";
        }

        if ($atRiskCount > 0) {
            $first = $atRisk->first();
            $label = $first->number ?? $first->customer ?? 'delivery';
            $bullets[] = "Prioritas: {$atRiskCount} delivery berisiko — contoh {$label}.";
        }

        if ($errPeriod > 0) {
            $bullets[] = "Error: {$errPeriod} di hari ini (rate {$errRate}%).";
        } elseif ($errRate > 0) {
            $bullets[] = "Error rate: {$errRate}%.";
        }

        $deltaPulling = (int) ($delta['pulling'] ?? 0);
        if ($deltaPulling !== 0) {
            $bullets[] = 'Pulling vs periode sebelumnya: '.($deltaPulling > 0 ? '+' : '').$deltaPulling.'%.';
        }

        if ($topUnder->isNotEmpty()) {
            $parts = $topUnder->map(fn ($p) => ($p->back_number ?? '-').' (gap '.(int) ($p->gap ?? 0).')')->implode(', ');
            $bullets[] = 'Part paling kritis: '.$parts.'.';
        }

        return [
            'id' => 'bella-morning-'.$date->toDateString(),
            'type' => 'morning_brief',
            'title' => 'Brief BELLA — '.$date->translatedFormat('d M Y'),
            'summary' => implode("\n", array_map(fn ($line) => '- '.$line, $bullets)),
            'bullets' => $bullets,
            'metrics' => [
                'prep_pct' => $prepPct,
                'at_risk' => $atRiskCount,
                'under_stock' => $underStock,
                'errors' => $errPeriod,
            ],
            'url' => route('dashboard.overview'),
            'ask_url' => route('dashboard.overview').'?bella=brief',
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
