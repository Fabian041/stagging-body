<?php

namespace App\Exports;

use App\Models\ErrorLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ErrorLogsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private ?string $area = null,
        private ?string $startDate = null,
        private ?string $endDate = null,
    ) {
    }

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

        return ErrorLog::when($this->area, function ($q) {
                $q->where('area', $this->area);
            })
            ->when($start || $end, function ($q) use ($start, $end) {
                if ($start && $end) {
                    $q->whereBetween('date', [$start, $end]);
                } elseif ($start) {
                    $q->where('date', '>=', $start);
                } elseif ($end) {
                    $q->where('date', '<=', $end);
                }
            })
            ->latest('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Area',
            'Message',
            'Expected',
            'Scanned',
            'Date',
        ];
    }

    /**
     * @param  \App\Models\ErrorLog  $log
     */
    public function map($log): array
    {
        return [
            $log->area ?? '',
            $log->message ?? '',
            $log->expected ?? '',
            $log->scanned ?? '',
            $log->date ?? '',
        ];
    }
}

