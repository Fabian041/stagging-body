<?php

namespace App\Exports;

use App\Models\PisScan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PisScanListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate
    ) {}

    public function collection(): Collection
    {
        $start = null;
        $end = null;
        try {
            $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        } catch (\Throwable $e) {
            $start = null;
        }
        try {
            $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;
        } catch (\Throwable $e) {
            $end = null;
        }

        return PisScan::with(['customer', 'details'])
            ->when($start || $end, function ($q) use ($start, $end) {
                $q->whereHas('details', function ($dq) use ($start, $end) {
                    if ($start && $end) {
                        $dq->whereBetween('updated_at', [$start, $end]);
                    } elseif ($start) {
                        $dq->where('updated_at', '>=', $start);
                    } elseif ($end) {
                        $dq->where('updated_at', '<=', $end);
                    }
                });
            })
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Scan Time',
            'Loading List Number',
            'PDS Number',
            'Customer',
            'Total Target',
            'Total Scanned',
            'Progress (%)',
            'Status',
        ];
    }

    /**
     * @param  \App\Models\PisScan  $scan
     */
    public function map($scan): array
    {
        $totalTarget = 0;
        $totalScanned = 0;
        $latestScanTime = null;

        foreach (($scan->details ?? []) as $detail) {
            $totalTarget += (int) ($detail->target_qty ?? 0);
            $totalScanned += (int) ($detail->scanned_qty ?? 0);
            if ($detail->updated_at) {
                if (!$latestScanTime || $detail->updated_at->gt($latestScanTime)) {
                    $latestScanTime = $detail->updated_at;
                }
            }
        }

        $progressPercentage = ($totalTarget > 0) ? round(($totalScanned / $totalTarget) * 100) : 0;
        $status = ($totalScanned >= $totalTarget && $totalTarget > 0) ? 'COMPLETE' : 'IN PROGRESS';

        return [
            $latestScanTime ? $latestScanTime->format('Y-m-d H:i') : '-',
            rtrim((string) ($scan->loading_list_number ?? ''), ' A'),
            $scan->pds_number ?? '',
            $scan->customer->name ?? '',
            $totalTarget,
            $totalScanned,
            $progressPercentage,
            $status,
        ];
    }
}

