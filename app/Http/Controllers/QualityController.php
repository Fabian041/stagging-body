<?php

namespace App\Http\Controllers;

use App\Exports\QualityInspectionsExport;
use App\Models\InternalPart;
use App\Models\MasterItemCheck;
use App\Models\QualityInspection;
use App\Models\QualityInspectionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QualityController extends Controller
{
    private array $defaultCheckItems = [
        [
            'item' => 'Mass check',
            'standard' => 'Std Upper: 158 gr | Std Lower: 140 gr',
            'std_upper' => '158 gr',
            'std_lower' => '140 gr',
        ],
    ];

    public function index()
    {
        $summary = [
            'total' => QualityInspection::count(),
            'ok' => QualityInspection::where('judgement', 'OK')->count(),
            'ng' => QualityInspection::where('judgement', 'NG')->count(),
            'pending' => QualityInspection::where('approval_status', 'NEED_APPROVAL')->count(),
        ];

        $recentInspections = QualityInspection::latest('checked_at')
            ->limit(8)
            ->get();

        return view('pages.quality.index', compact('summary', 'recentInspections'));
    }

    public function scan(Request $request)
    {
        $mode = $request->input('mode') === 'full' ? 'full' : 'sampling';

        return view('pages.quality.scan', compact('mode'));
    }

    public function lookupKanban(Request $request)
    {
        $request->validate([
            'kanban_code' => ['required', 'string', 'max:500'],
            'check_type' => ['nullable', 'in:FIRST,LAST,100% CHECK'],
        ]);

        $kanban = strtoupper(trim($request->kanban_code));

        if (str_contains($kanban, 'KP45')) {
            $firstInspection = $request->check_type === 'LAST'
                ? $this->latestFirstInspectionForPart('KP45', 'KP45', $request->kanban_code, 'PART PROTOTYPE KP45')
                : null;

            return response()->json([
                'success' => true,
                'message' => 'Prototype KP45 berhasil ditampilkan.',
                'data' => [
                    'kanban_code' => $request->kanban_code,
                    'part_name' => 'PART PROTOTYPE KP45',
                    'part_number' => 'KP45',
                    'internal_part' => 'KP45',
                    'back_number' => 'KP45',
                    'qty_per_box' => 1,
                    'photo' => null,
                    'customer' => '-',
                    'line' => 'QC GATE R1',
                    'check_options' => ['FIRST', 'LAST', '100% CHECK'],
                    'check_items' => [
                        [
                            'item' => 'Mass check',
                            'standard' => 'Std Upper: 158 gr | Std Lower: 140 gr',
                            'std_upper' => '158 gr',
                            'std_lower' => '140 gr',
                        ],
                    ],
                    'first_check_items' => $this->mapFirstInspectionDetails($firstInspection),
                    'is_prototype' => true,
                ],
            ]);
        }

        $part = $this->resolvePartFromKanban($kanban);

        $customerPart = $part?->customerPart;
        $partNumber = $part?->part_number ?? $customerPart?->part_number ?? $this->extractReadablePartNumber($kanban);
        $backNumber = $part?->back_number ?? '-';
        $partName = $part?->part_name ?? $part?->name ?? 'PART SAMPLE QC ASSY';
        $firstInspection = $request->check_type === 'LAST'
            ? $this->latestFirstInspectionForPart($partNumber, $backNumber, $request->kanban_code, $partName)
            : null;

        $payload = [
            'kanban_code' => $request->kanban_code,
            'part_name' => $partName,
            'part_number' => $partNumber,
            'internal_part' => $part?->part_number ?? '-',
            'back_number' => $backNumber,
            'qty_per_box' => $customerPart?->qty_per_kanban ?? $this->extractQty($kanban),
            'photo' => $part?->photo ?? null,
            'customer' => $customerPart?->customer?->name ?? $customerPart?->customer?->customer_name ?? '-',
            'line' => 'QC GATE R1',
            'check_options' => ['FIRST', 'LAST', '100% CHECK'],
            'check_items' => $this->getCheckItemsForPart($part),
            'first_check_items' => $this->mapFirstInspectionDetails($firstInspection),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail part berhasil ditampilkan dari hasil scan kanban.',
            'data' => $payload,
        ]);
    }

    public function storeInspection(Request $request)
    {
        $request->validate([
            'kanban_code' => ['required', 'string', 'max:500'],
            'inspection_mode' => ['nullable', 'in:SAMPLING,FULL'],
            'check_type' => ['required', 'in:FIRST,LAST,100% CHECK'],
            'shift' => ['nullable', 'string', 'max:20'],
            'line' => ['nullable', 'string', 'max:50'],
            'pic' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'qty_per_box' => ['nullable', 'string', 'max:50'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.item' => ['required', 'string', 'max:150'],
            'details.*.standard' => ['nullable', 'string', 'max:255'],
            'details.*.result' => ['required', 'in:OK,NG,NA'],
            'details.*.remark' => ['nullable', 'string', 'max:255'],
            'total_box' => ['nullable', 'integer', 'min:0'],
            'total_part' => ['nullable', 'integer', 'min:0'],
        ]);

        $firstInspection = null;

        if ($request->check_type === 'LAST') {
            $firstInspection = $this->latestFirstInspectionForPart(
                $request->part_number,
                $request->back_number,
                $request->kanban_code,
                $request->part_name
            );

            if (!$firstInspection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scan First terlebih dahulu, kemudian lakukan Scan Last menggunakan part yang sama dengan Scan First.',
                ], 422);
            }
        }

        $detailsToSave = collect($request->details);

        if ($firstInspection) {
            $firstDetails = $firstInspection->details->map(function ($detail) {
                return [
                    'item' => $detail->check_item,
                    'standard' => $detail->standard,
                    'result' => $detail->result,
                    'remark' => trim('Scan FIRST | ' . ($detail->remark ?: '-')),
                ];
            });

            $lastDetails = $detailsToSave->map(function ($detail) {
                return [
                    'item' => $detail['item'],
                    'standard' => $detail['standard'] ?? null,
                    'result' => $detail['result'],
                    'remark' => trim('Scan LAST | ' . ($detail['remark'] ?? '-')),
                ];
            });

            $detailsToSave = $firstDetails->merge($lastDetails);
        }

        $judgement = $detailsToSave->contains(fn ($detail) => ($detail['result'] ?? null) === 'NG') ? 'NG' : 'OK';
        $inspectionMode = $request->inspection_mode === 'FULL' || $request->check_type === '100% CHECK'
            ? 'FULL'
            : 'SAMPLING';

        $inspection = DB::transaction(function () use ($request, $judgement, $inspectionMode, $detailsToSave) {
            $inspectionData = [
                'kanban_code' => $request->kanban_code,
                'check_type' => $request->check_type,
                'line' => $request->line ?: 'QC GATE R1',
                'shift' => $request->shift,
                'pic' => $request->pic ?: optional(Auth::user())->name,
                'part_name' => $request->part_name,
                'part_number' => $request->part_number,
                'back_number' => $request->back_number,
                'qty_per_box' => $request->qty_per_box,
                'judgement' => $judgement,
                'approval_status' => $judgement === 'NG' ? 'NEED_APPROVAL' : 'AUTO_RELEASE',
                'checked_at' => now(),
            ];

            if (Schema::hasColumn('quality_inspections', 'inspection_mode')) {
                $inspectionData['inspection_mode'] = $inspectionMode;
            }

            if (Schema::hasColumn('quality_inspections', 'total_box')) {
                $inspectionData['total_box'] = $inspectionMode === 'FULL'
                    ? (int) $request->input('total_box', 1)
                    : null;
            }

            if (Schema::hasColumn('quality_inspections', 'total_part')) {
                $inspectionData['total_part'] = $inspectionMode === 'FULL'
                    ? (int) $request->input('total_part', 0)
                    : null;
            }

            $inspection = QualityInspection::create($inspectionData);

            foreach ($detailsToSave as $detail) {
                QualityInspectionDetail::create([
                    'quality_inspection_id' => $inspection->id,
                    'check_item' => $detail['item'],
                    'standard' => $detail['standard'] ?? null,
                    'result' => $detail['result'],
                    'remark' => $detail['remark'] ?? null,
                ]);
            }

            return $inspection;
        });

        return response()->json([
            'success' => true,
            'message' => $judgement === 'NG'
                ? 'Hasil inspection NG tersimpan dan membutuhkan approval supervisor.'
                : 'Hasil inspection OK berhasil disimpan.',
            'data' => [
                'inspection_id' => $inspection->id,
                'judgement' => $inspection->judgement,
                'approval_status' => $inspection->approval_status,
            ],
        ]);
    }

    public function history(Request $request)
    {
        $selectedProcess = $request->input('process', 'Quality');
        $availableProcesses = ['PPIC', 'Production', 'PIS', 'Quality', 'Error Log'];

        $query = $this->qualityHistoryQuery($request);

        if ($selectedProcess !== 'Quality') {
            $query->whereRaw('1 = 0');
        }

        $inspections = $query
            ->latest('checked_at')
            ->paginate(20)
            ->withQueryString();

        return view('pages.quality.history', compact('inspections', 'selectedProcess', 'availableProcesses'));
    }

    public function exportHistory(Request $request)
    {
        $selectedProcess = $request->input('process', 'Quality');
        $query = $this->qualityHistoryQuery($request);

        if ($selectedProcess !== 'Quality') {
            $query->whereRaw('1 = 0');
        }

        $inspections = $query->latest('checked_at')->get();
        $fileName = 'quality-inspections-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new QualityInspectionsExport($inspections), $fileName);
    }

    public function show(QualityInspection $inspection)
    {
        $inspection->load('details');
        $part = $this->resolvePartFromInspection($inspection);
        $customerName = $part->customerPart->customer->name ?? $part->customerPart->customer->customer_name ?? '-';

        return view('pages.quality.show', compact('inspection', 'customerName'));
    }

    public function exportInspection(QualityInspection $inspection)
    {
        $inspection->load('details');

        $part = $inspection->part_number ?: 'inspection-' . $inspection->id;
        $fileName = 'quality-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $part) . '-' . $inspection->id . '.xlsx';

        $templatePath = storage_path('app/templates/quality_inspection_sheet_model_660a.xlsx');

        if (!file_exists($templatePath)) {
            return Excel::download(new QualityInspectionsExport(collect([$inspection])), $fileName);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $this->fillQualityTemplate($spreadsheet, $inspection);

        $tempPath = tempnam(storage_path('app'), 'quality-template-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function pareto()
    {
        $summary = [
            'total' => QualityInspection::count(),
            'ok' => QualityInspection::where('judgement', 'OK')->count(),
            'ng' => QualityInspection::where('judgement', 'NG')->count(),
            'pending' => QualityInspection::where('approval_status', 'NEED_APPROVAL')->count(),
        ];

        $topDefects = QualityInspectionDetail::select('check_item', DB::raw('COUNT(*) as total'))
            ->where('result', 'NG')
            ->groupBy('check_item')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topParts = QualityInspection::select('part_number', DB::raw('COUNT(*) as total'))
            ->where('judgement', 'NG')
            ->groupBy('part_number')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('pages.quality.pareto', compact('summary', 'topDefects', 'topParts'));
    }

    public function approval(Request $request, QualityInspection $inspection)
    {
        $request->validate([
            'approval_status' => ['required', 'in:RECHECK,HOLD,REPAIR,REJECT,RELEASE'],
            'supervisor_note' => ['nullable', 'string', 'max:255'],
        ]);

        $inspection->update([
            'approval_status' => $request->approval_status,
            'supervisor_note' => $request->supervisor_note,
            'approved_by' => optional(Auth::user())->name,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('quality.history')
            ->with('success', 'Approval hasil QC berhasil diperbarui.');
    }

    private function getCheckItemsForPart(?InternalPart $part): array
    {
        if (!$part) {
            return [];
        }

        $partIds = collect([$part->id]);

        if (!empty($part->part_number) || !empty($part->back_number)) {
            $relatedPartIds = InternalPart::query()
                ->when(!empty($part->part_number), fn ($query) => $query->where('part_number', $part->part_number))
                ->when(!empty($part->back_number), fn ($query) => $query->where('back_number', $part->back_number))
                ->pluck('id');

            $partIds = $partIds->merge($relatedPartIds);
        }

        $items = MasterItemCheck::query()
            ->with('standards')
            ->whereIn('part_id', $partIds->filter()->unique()->values())
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        return $items->map(function (MasterItemCheck $itemCheck) {
            $standard = $itemCheck->standards->first();
            $lower = $this->formatStandardValue($standard?->min_value);
            $upper = $this->formatStandardValue($standard?->max_value);

            return [
                'item' => $itemCheck->item_check,
                'standard' => trim("Std Upper: {$upper} | Std Lower: {$lower}"),
                'std_upper' => $upper,
                'std_lower' => $lower,
            ];
        })->values()->all();
    }

    private function qualityHistoryQuery(Request $request)
    {
        return QualityInspection::query()
            ->with('details')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('checked_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('checked_at', '<=', $request->date_to))
            ->when($request->filled('part_number'), fn ($q) => $q->where('part_number', 'like', '%' . $request->part_number . '%'))
            ->when($request->filled('judgement'), fn ($q) => $q->where('judgement', $request->judgement));
    }

    private function fillQualityTemplate(Spreadsheet $spreadsheet, QualityInspection $inspection): void
    {
        $checkedAt = $inspection->checked_at ?? now();
        $monthName = strtoupper($checkedAt->locale('id')->translatedFormat('F'));
        $year = $checkedAt->format('Y');

        if ($spreadsheet->sheetNameExists('Sampul')) {
            $cover = $spreadsheet->getSheetByName('Sampul');
            $cover->setCellValue('M1', $monthName);
            $cover->setCellValue('N1', $year);
        }

        foreach ($spreadsheet->getWorksheetIterator() as $templateSheet) {
            if ($templateSheet->getTitle() !== 'Sampul') {
                $this->forceTemplateModelColumnsToRightLeft($templateSheet);
            }
        }

        $sheet = $this->findTemplateSheet($spreadsheet, $inspection) ?? $spreadsheet->getSheet(1);
        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheet));

        $this->replaceTemplateLabelValue($sheet, 'Line Number', $inspection->line ?: 'QC GATE R1');
        $this->replaceTemplateLabelValue($sheet, 'Part Number', $inspection->part_number ?: '-');
        $this->replaceTemplateLabelValue($sheet, 'Part Name', $inspection->part_name ?: '-');

        $side = $this->detectInspectionSide($inspection) ?? 'R';
        $targetColumn = $this->findTemplateResultColumn($sheet, (int) $checkedAt->format('j'), $side) ?? 5;
        $shiftText = $this->normalizeShiftText($inspection->shift) ?: 'shift1';

        foreach ($inspection->details as $detail) {
            $detailSheet = $this->findTemplateSheetForDetail($spreadsheet, $inspection, $detail, $shiftText) ?? $sheet;
            $targetColumn = $this->findTemplateResultColumn($detailSheet, (int) $checkedAt->format('j'), $side) ?? 5;
            $value = $this->extractActualFromRemark($detail->remark) ?: ($detail->remark ?: $detail->result);
            $row = $this->findTemplateValueRow($detailSheet, $detail->check_item, $shiftText, $value)
                ?? $this->findTemplateDetailRow($detailSheet, $detail->check_item, $shiftText);

            if (!$row) {
                continue;
            }

            $detailSheet->setCellValueByColumnAndRow($targetColumn, $row, $value);
        }

    }

    private function findTemplateSheet(Spreadsheet $spreadsheet, QualityInspection $inspection): ?Worksheet
    {
        $partNumber = $this->compactPartText($inspection->part_number);
        $partName = $this->compactPartText($inspection->part_name);

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($sheet->getTitle() === 'Sampul') {
                continue;
            }

            $highestRow = min($sheet->getHighestDataRow(), 20);
            $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            for ($row = 1; $row <= $highestRow; $row++) {
                for ($column = 1; $column <= $highestColumn; $column++) {
                    $value = $this->compactPartText((string) $sheet->getCellByColumnAndRow($column, $row)->getValue());

                    if (($partNumber && str_contains($value, $partNumber)) || ($partName && str_contains($value, $partName))) {
                        return $sheet;
                    }
                }
            }
        }

        return null;
    }

    private function findTemplateSheetForDetail(Spreadsheet $spreadsheet, QualityInspection $inspection, QualityInspectionDetail $detail, string $shiftText): ?Worksheet
    {
        $partNumber = $this->compactPartText($inspection->part_number);
        $partName = $this->compactPartText($inspection->part_name);
        $partTokens = $this->partNumberTokens($inspection->part_number);
        $itemCheckCandidates = $this->itemCheckCandidates($detail->check_item);
        $bestSheet = null;
        $bestScore = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($sheet->getTitle() === 'Sampul') {
                continue;
            }

            $text = $this->templateSearchText($sheet, 1, min($sheet->getHighestDataRow(), 70));
            $title = $this->compactPartText($sheet->getTitle());
            $score = 0;

            foreach ($itemCheckCandidates as $index => $itemCheck) {
                if ($itemCheck && str_contains($text, $itemCheck)) {
                    $score += $index === 0 ? 20 : 18;
                    break;
                }
            }

            if ($partNumber && str_contains($text, $partNumber)) {
                $score += 12;
            } elseif ($partTokens->contains(fn ($token) => str_contains($text, $token))) {
                $score += 10;
            }

            if ($partName && str_contains($text, $partName)) {
                $score += 4;
            }

            if ($shiftText && (str_contains($text, strtoupper($shiftText)) || str_contains($title, strtoupper($shiftText)))) {
                $score += 2;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestSheet = $sheet;
            }
        }

        return $bestScore >= 18 ? $bestSheet : null;
    }

    private function partNumberTokens(?string $partNumber): \Illuminate\Support\Collection
    {
        preg_match_all('/\d{5,}/', (string) $partNumber, $matches);

        return collect($matches[0] ?? [])->unique()->values();
    }

    private function itemCheckCandidates(string $itemCheck): array
    {
        $compact = $this->compactPartText($itemCheck);
        $withoutTrailingGroup = preg_replace('/[ABC]$/', '', $compact) ?: $compact;
        $withoutCheckWords = str_replace(['CHECK', 'ITEM'], '', $withoutTrailingGroup);

        return collect([$compact, $withoutTrailingGroup, $withoutCheckWords])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function templateSearchText(Worksheet $sheet, int $startRow, int $endRow): string
    {
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $text = '';

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($column = 1; $column <= $highestColumn; $column++) {
                $text .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }
        }

        return $this->compactPartText($text);
    }

    private function replaceTemplateLabelValue(Worksheet $sheet, string $label, string $value): void
    {
        $highestRow = min($sheet->getHighestDataRow(), 25);
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($column = 1; $column <= $highestColumn; $column++) {
                $cellValue = trim((string) $sheet->getCellByColumnAndRow($column, $row)->getValue());

                if (!str_contains(strtolower($cellValue), strtolower($label))) {
                    continue;
                }

                for ($targetColumn = $column + 1; $targetColumn <= min($column + 12, $highestColumn); $targetColumn++) {
                    if (str_contains((string) $sheet->getCellByColumnAndRow($targetColumn, $row)->getValue(), ':')) {
                        $sheet->setCellValueByColumnAndRow($targetColumn + 1, $row, $value);
                        return;
                    }
                }
            }
        }
    }

    private function findTemplateResultColumn(Worksheet $sheet, int $day, ?string $side): ?int
    {
        $highestRow = min($sheet->getHighestDataRow(), 35);
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowText = '';
            for ($column = 1; $column <= min($highestColumn, 6); $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            if (!str_contains($this->compactPartText($rowText), 'TIME')) {
                continue;
            }

            for ($column = 1; $column <= $highestColumn; $column++) {
                $cellValue = $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();

                if (!is_numeric($cellValue) || (int) $cellValue !== $day) {
                    continue;
                }

                if (!$side) {
                    return $column;
                }

                foreach ([$row + 1, $row + 2] as $modelRow) {
                    $modelValue = strtoupper(trim((string) $sheet->getCellByColumnAndRow($column, $modelRow)->getFormattedValue()));

                    if ($modelValue === $side) {
                        return $column;
                    }

                    $nextModelValue = strtoupper(trim((string) $sheet->getCellByColumnAndRow($column + 1, $modelRow)->getFormattedValue()));

                    if ($nextModelValue === $side) {
                        return $column + 1;
                    }
                }

                return $side === 'L' ? $column + 1 : $column;
            }
        }

        return null;
    }

    private function forceTemplateModelColumnsToRightLeft(Worksheet $sheet): void
    {
        $highestRow = min($sheet->getHighestDataRow(), 35);
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowText = '';
            for ($column = 1; $column <= min($highestColumn, 6); $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            if (!str_contains($this->compactPartText($rowText), 'TIME')) {
                continue;
            }

            foreach ([$row + 1, $row + 2] as $modelRow) {
                $modelLabel = $this->compactPartText((string) $sheet->getCellByColumnAndRow(1, $modelRow)->getFormattedValue()
                    . ' ' . $sheet->getCellByColumnAndRow(2, $modelRow)->getFormattedValue()
                    . ' ' . $sheet->getCellByColumnAndRow(3, $modelRow)->getFormattedValue());

                if (!str_contains($modelLabel, 'MODEL')) {
                    continue;
                }

                for ($column = 1; $column <= $highestColumn; $column++) {
                    $dayValue = $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();

                    if (!is_numeric($dayValue)) {
                        continue;
                    }

                    $sheet->setCellValueByColumnAndRow($column, $modelRow, 'R');

                    if ($column + 1 <= $highestColumn) {
                        $sheet->setCellValueByColumnAndRow($column + 1, $modelRow, 'L');
                    }
                }

                return;
            }

            return;
        }
    }

    private function detectInspectionSide(QualityInspection $inspection): ?string
    {
        $text = strtoupper(implode(' ', array_filter([
            $inspection->kanban_code,
            $inspection->part_number,
            $inspection->part_name,
            $inspection->back_number,
        ])));

        if (preg_match('/\b(LH|LEFT|L)\b/', $text)) {
            return 'L';
        }

        if (preg_match('/\b(RH|RIGHT|R)\b/', $text)) {
            return 'R';
        }

        return null;
    }

    private function findTemplateValueRow(Worksheet $sheet, string $itemCheck, string $shiftText, string $actual): ?int
    {
        $actualCandidates = $this->actualValueCandidates($actual);
        $itemNeedle = $this->compactPartText($itemCheck);
        $groupLetter = $this->extractTemplateGroupLetter($itemCheck) ?? 'A';
        $shiftStart = 1;
        $shiftEnd = $sheet->getHighestDataRow();

        foreach ($this->findShiftSections($sheet) as $index => $section) {
            if ($shiftText && !str_contains($section['text'], strtoupper($shiftText))) {
                continue;
            }

            $shiftStart = $section['row'];
            $shiftEnd = $this->findShiftSections($sheet)[$index + 1]['row'] ?? $sheet->getHighestDataRow();
            break;
        }

        for ($row = $shiftStart; $row <= $shiftEnd; $row++) {
            $rowText = '';

            for ($column = 1; $column <= 5; $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            $normalized = $this->compactPartText($rowText);

            if ($itemNeedle && str_contains($normalized, $itemNeedle)) {
                continue;
            }

            foreach ($actualCandidates as $candidate) {
                if ($candidate !== '' && str_contains($normalized, $candidate)) {
                    return $row;
                }
            }
        }

        if ($groupLetter) {
            $nearestRow = $this->findNearestTemplateScaleRow($sheet, $shiftStart, $shiftEnd, $groupLetter, $actual);

            if ($nearestRow) {
                return $nearestRow;
            }

            $groupRow = $this->findTemplateGroupValueRow($sheet, $shiftStart, $shiftEnd, $groupLetter);

            if ($groupRow) {
                return $groupRow;
            }
        }

        return null;
    }

    private function findNearestTemplateScaleRow(Worksheet $sheet, int $shiftStart, int $shiftEnd, string $groupLetter, string $actual): ?int
    {
        $actualNumber = $this->parseTemplateNumber($actual);

        if ($actualNumber === null) {
            return null;
        }

        $groupStart = null;
        $groupEnd = $shiftEnd;

        for ($row = $shiftStart; $row <= $shiftEnd; $row++) {
            $rowText = '';

            for ($column = 1; $column <= 5; $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            $normalized = $this->compactPartText($rowText);

            if ($groupStart !== null && in_array($normalized, ['A', 'B', 'C'], true)) {
                $groupEnd = $row - 1;
                break;
            }

            if ($normalized === $groupLetter) {
                $groupStart = $row;
            }
        }

        if ($groupStart === null) {
            return null;
        }

        $bestRow = null;
        $bestDiff = null;

        for ($row = $groupStart + 1; $row <= $groupEnd; $row++) {
            for ($column = 1; $column <= 5; $column++) {
                $scale = $this->parseTemplateNumber((string) $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue());

                if ($scale === null) {
                    continue;
                }

                $diff = abs($scale - $actualNumber);

                if ($bestDiff === null || $diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestRow = $row;
                }
            }
        }

        return $bestRow;
    }

    private function parseTemplateNumber(string $value): ?float
    {
        if (!preg_match('/-?\d+(?:[,.]\d+)?/', $value, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[0]);
    }

    private function extractTemplateGroupLetter(string $itemCheck): ?string
    {
        $text = strtoupper(trim($itemCheck));

        if (preg_match('/(?:^|\s)([ABC])(?:\s|$)/', $text, $matches)) {
            return $matches[1];
        }

        $compact = $this->compactPartText($itemCheck);

        if (preg_match('/([ABC])$/', $compact, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function findTemplateGroupValueRow(Worksheet $sheet, int $shiftStart, int $shiftEnd, string $groupLetter): ?int
    {
        for ($row = $shiftStart; $row <= $shiftEnd; $row++) {
            $rowText = '';

            for ($column = 1; $column <= 5; $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            if ($this->compactPartText($rowText) !== $groupLetter) {
                continue;
            }

            for ($valueRow = $row + 1; $valueRow <= min($row + 8, $shiftEnd); $valueRow++) {
                $valueText = '';

                for ($column = 1; $column <= 5; $column++) {
                    $valueText .= ' ' . $sheet->getCellByColumnAndRow($column, $valueRow)->getFormattedValue();
                }

                if (preg_match('/\d/', $valueText)) {
                    return $valueRow;
                }
            }

            return $row + 1;
        }

        return null;
    }

    private function findShiftSections(Worksheet $sheet): array
    {
        $sections = [];
        $highestRow = $sheet->getHighestDataRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $text = '';

            for ($column = 1; $column <= 5; $column++) {
                $text .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue();
            }

            $normalized = $this->compactPartText($text);

            if (str_contains($normalized, 'SHIFT1') || str_contains($normalized, 'SHIFT2') || str_contains($normalized, 'SHIFT3')) {
                $sections[] = ['row' => $row, 'text' => $normalized];
            }
        }

        return $sections;
    }

    private function actualValueCandidates(string $actual): array
    {
        $actual = trim($actual);
        $numeric = str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $actual) ?? '');
        $candidates = [$this->compactPartText($actual), $this->compactPartText($numeric)];

        if (is_numeric($numeric)) {
            $value = (float) $numeric;
            $candidates[] = $this->compactPartText((string) $value);
            $candidates[] = $this->compactPartText(number_format($value, 2, '.', ''));

            if ($value >= 10) {
                $scaled = $value / 100;
                $candidates[] = $this->compactPartText((string) $scaled);
                $candidates[] = $this->compactPartText(number_format($scaled, 2, '.', ''));
            }
        }

        return collect($candidates)->filter()->unique()->values()->all();
    }

    private function findTemplateDetailRow(Worksheet $sheet, string $itemCheck, string $shiftText): ?int
    {
        $needle = $this->compactPartText($itemCheck);
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = min(Coordinate::columnIndexFromString($sheet->getHighestDataColumn()), 8);

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowText = '';

            for ($column = 1; $column <= $highestColumn; $column++) {
                $rowText .= ' ' . $sheet->getCellByColumnAndRow($column, $row)->getValue();
            }

            $normalized = $this->compactPartText($rowText);

            if ($needle && str_contains($normalized, $needle) && (!$shiftText || str_contains($normalized, strtoupper($shiftText)))) {
                return $row;
            }
        }

        return null;
    }

    private function normalizeShiftText(?string $shift): string
    {
        if (!$shift) {
            return '';
        }

        return str_contains($shift, '1') ? 'shift1' : (str_contains($shift, '3') ? 'shift3' : $this->compactPartText($shift));
    }

    private function latestFirstInspectionForPart(?string $partNumber, ?string $backNumber = null, ?string $kanbanCode = null, ?string $partName = null): ?QualityInspection
    {
        $candidates = collect([$partNumber, $backNumber, $kanbanCode, $partName])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        $inspection = QualityInspection::with('details')
            ->where('check_type', 'FIRST')
            ->where(function ($query) use ($candidates) {
                foreach ($candidates as $candidate) {
                    $query->orWhere('part_number', $candidate)
                        ->orWhere('back_number', $candidate)
                        ->orWhere('kanban_code', $candidate)
                        ->orWhere('part_name', $candidate);
                }
            })
            ->latest('checked_at')
            ->first();

        if ($inspection) {
            return $inspection;
        }

        $compactCandidates = $candidates
            ->map(fn ($value) => $this->compactPartText($value))
            ->filter()
            ->unique();

        return QualityInspection::with('details')
            ->where('check_type', 'FIRST')
            ->latest('checked_at')
            ->get()
            ->first(function (QualityInspection $inspection) use ($compactCandidates) {
                $inspectionValues = collect([
                    $inspection->part_number,
                    $inspection->back_number,
                    $inspection->kanban_code,
                    $inspection->part_name,
                ])->map(fn ($value) => $this->compactPartText((string) $value));

                return $compactCandidates->contains(function ($candidate) use ($inspectionValues) {
                    return $inspectionValues->contains(function ($inspectionValue) use ($candidate) {
                        return $candidate !== ''
                            && $inspectionValue !== ''
                            && (str_contains($candidate, $inspectionValue) || str_contains($inspectionValue, $candidate));
                    });
                });
            });
    }

    private function mapFirstInspectionDetails(?QualityInspection $inspection): array
    {
        if (!$inspection) {
            return [];
        }

        return $inspection->details->map(function (QualityInspectionDetail $detail) use ($inspection) {
            return [
                'pic' => $inspection->pic,
                'item' => $detail->check_item,
                'standard' => $detail->standard,
                'std_upper' => $this->extractStandardPart($detail->standard, 'Upper'),
                'std_lower' => $this->extractStandardPart($detail->standard, 'Lower'),
                'actual' => $this->extractActualFromRemark($detail->remark),
                'result' => $detail->result,
                'remark' => $detail->remark,
            ];
        })->values()->all();
    }

    private function extractStandardPart(?string $standard, string $label): string
    {
        if (!$standard) {
            return '-';
        }

        preg_match('/Std ' . $label . ':\s*([^|]+)/i', $standard, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '-';
    }

    private function extractActualFromRemark(?string $remark): string
    {
        if (!$remark) {
            return '';
        }

        preg_match('/Actual:\s*([^|]+)/i', $remark, $matches);

        return isset($matches[1]) ? trim($matches[1]) : '';
    }

    private function formatStandardValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $numeric = (float) $value;
        $formatted = fmod($numeric, 1.0) === 0.0
            ? number_format($numeric, 0, ',', '')
            : rtrim(rtrim(number_format($numeric, 2, ',', ''), '0'), ',');

        return "{$formatted} gram";
    }

    private function resolvePartFromKanban(string $kanban): ?InternalPart
    {
        $normalizedKanban = strtoupper(trim($kanban));
        $compactKanban = $this->compactPartText($normalizedKanban);

        $parts = InternalPart::query()
            ->select('id', 'part_number', 'back_number', 'part_name', 'photo')
            ->with(['customerPart.customer'])
            ->where(function ($query) {
                $query->whereNotNull('part_number')
                    ->orWhereNotNull('back_number')
                    ->orWhereNotNull('part_name');
            })
            ->orderByRaw('CHAR_LENGTH(COALESCE(part_number, "")) DESC')
            ->get();

        return $parts->first(function ($part) use ($normalizedKanban, $compactKanban) {
            foreach ($this->getPartLookupCandidates($part) as $candidate) {
                $normalizedCandidate = strtoupper(trim((string) $candidate));
                $compactCandidate = $this->compactPartText($normalizedCandidate);

                if ($normalizedCandidate !== '' && str_contains($normalizedKanban, $normalizedCandidate)) {
                    return true;
                }

                if ($compactCandidate !== '' && str_contains($compactKanban, $compactCandidate)) {
                    return true;
                }
            }

            return false;
        });
    }

    private function resolvePartFromInspection(QualityInspection $inspection): ?InternalPart
    {
        if (!empty($inspection->part_number)) {
            $part = InternalPart::query()
                ->select('id', 'part_number', 'back_number', 'part_name', 'photo')
                ->with(['customerPart.customer'])
                ->where('part_number', $inspection->part_number)
                ->first();

            if ($part) {
                return $part;
            }
        }

        if (!empty($inspection->kanban_code)) {
            return $this->resolvePartFromKanban($inspection->kanban_code);
        }

        return null;
    }

    private function getPartLookupCandidates(InternalPart $part): array
    {
        $customerPart = $part->customerPart;

        return array_values(array_filter([
            $part->part_number,
            $part->back_number,
            $part->part_name,
            $customerPart->part_number ?? null,
            $customerPart->back_number ?? null,
            $this->compactPartText((string) $part->part_number),
            $this->compactPartText((string) ($customerPart->part_number ?? '')),
            str_replace('-', '', (string) $part->part_number),
            str_replace('-', '', (string) ($customerPart->part_number ?? '')),
        ]));
    }

    private function compactPartText(string $text): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($text)) ?? '';
    }

    private function extractReadablePartNumber(string $kanban): string
    {
        preg_match('/[A-Z0-9-]{8,}/', $kanban, $matches);

        return $matches[0] ?? substr($kanban, 0, 30);
    }

    private function extractBackNumber(string $kanban): string
    {
        preg_match('/[A-Z]{2,5}\d{1,5}/', $kanban, $matches);

        return $matches[0] ?? '-';
    }

    private function extractQty(string $kanban): string
    {
        preg_match('/QTY[:\s-]*(\d+)/i', $kanban, $matches);

        return $matches[1] ?? '-';
    }
}