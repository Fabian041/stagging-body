<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QualityInspectionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $inspections) {}

    public function collection(): Collection
    {
        return $this->inspections->flatMap(function ($inspection) {
            if ($inspection->details->isEmpty()) {
                return collect([(object) [
                    'inspection' => $inspection,
                    'detail' => null,
                ]]);
            }

            return $inspection->details->map(fn ($detail) => (object) [
                'inspection' => $inspection,
                'detail' => $detail,
            ]);
        });
    }

    public function headings(): array
    {
        return [
            'Checked At',
            'Kanban Code',
            'Part Number',
            'Part Name',
            'Back Number',
            'Check Type',
            'Inspection Mode',
            'Line',
            'Shift',
            'PIC',
            'Qty / Box',
            'Total Box',
            'Total Part',
            'Judgement',
            'Approval Status',
            'Approved By',
            'Approved At',
            'Supervisor Note',
            'Item Check',
            'Standard',
            'Result',
            'Remark / Actual',
        ];
    }

    public function map($row): array
    {
        $inspection = $row->inspection;
        $detail = $row->detail;

        return [
            optional($inspection->checked_at)->format('Y-m-d H:i:s') ?? '-',
            $inspection->kanban_code ?? '-',
            $inspection->part_number ?? '-',
            $inspection->part_name ?? '-',
            $inspection->back_number ?? '-',
            $inspection->check_type ?? '-',
            $inspection->inspection_mode ?? '-',
            $inspection->line ?? '-',
            $inspection->shift ?? '-',
            $inspection->pic ?? '-',
            $inspection->qty_per_box ?? '-',
            $inspection->total_box ?? '-',
            $inspection->total_part ?? '-',
            $inspection->judgement ?? '-',
            $inspection->approval_status ?? '-',
            $inspection->approved_by ?? '-',
            optional($inspection->approved_at)->format('Y-m-d H:i:s') ?? '-',
            $inspection->supervisor_note ?? '-',
            $detail->check_item ?? '-',
            $detail->standard ?? '-',
            $detail->result ?? '-',
            $detail->remark ?? '-',
        ];
    }
}