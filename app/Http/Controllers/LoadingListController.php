<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Kanban;
use App\Models\KanbanAfterPulling;
use App\Models\LoadingList;
use App\Models\LoadingListDetail;
use App\Models\MasterCycle;
use App\Models\Mutation;
use App\Models\PisScan;
use App\Models\SkidDetail;
use App\Support\ConvertsCustomerPartNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pusher\Pusher;
use Yajra\DataTables\Facades\DataTables;

class LoadingListController extends Controller
{
    use ConvertsCustomerPartNumber;

    public $temp_serial_number;

    public function pushData($is_updated)
    {
        // connection to pusher
        $options = [
            'cluster' => 'ap1',
            'encrypted' => true,
        ];

        $pusher = new Pusher(
            '78dc86268a49904a688d',
            '19c222ee916e49372796',
            '1720799',
            $options
        );

        // sending data
        $result = $pusher->trigger('loading-list', 'loadingListUpdated', $is_updated);

        return $result;
    }

    public function index()
    {
        return view('pages.loadingList', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function searchManifests(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        $perPage = 30;

        $query = LoadingList::query()
            ->whereNotNull('pds_number')
            ->where('pds_number', '<>', '')
            ->select('pds_number')
            ->distinct()
            ->orderBy('pds_number');

        if ($term !== '') {
            $query->where('pds_number', 'like', addcslashes($term, '%_\\').'%');
        }

        $manifests = $query->paginate($perPage);

        return response()->json([
            'results' => collect($manifests->items())->map(function ($manifest) {
                return [
                    'id' => $manifest->pds_number,
                    'text' => $manifest->pds_number,
                ];
            })->values(),
            'pagination' => ['more' => $manifests->hasMorePages()],
        ]);
    }

    public function driverAbsen()
    {
        return view('pages.driverAbsen', [
            'customers' => Customer::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function getDriverAbsenData(Request $request)
    {
        try {
            $masters = MasterCycle::query()
                ->with(['customer:id,name'])
                ->get();
            $masterMeta = $masters->map(function (MasterCycle $m) {
                return $this->formatMasterCycleMetaForChart($m);
            });

            $query = LoadingList::query()
                ->with(['customer:id,name,code'])
                ->whereNotNull('customer_id')
                ->latest();

            if ($request->filled('customer_id')) {
                $query->where('customer_id', (int) $request->get('customer_id'));
            }

            if ($request->filled('delivery_date')) {
                $start = Carbon::parse($request->get('delivery_date'))->startOfDay();
                $end = (clone $start)->endOfDay();
                $query->whereBetween('delivery_date', [$start, $end]);
            } else {
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                $query->whereBetween('delivery_date', [$start, $end]);
            }

            if ($request->filled('truck_status') && in_array($request->get('truck_status'), ['pending', 'picked'], true)) {
                $query->where('truck_status', $request->get('truck_status'));
            }

            $data = $query->get();

            $groupedData = $data->groupBy(function ($item) {
                $dateStr = $item->delivery_date ? Carbon::parse($item->delivery_date)->format('Y-m-d') : 'nodate';

                return $item->customer_id.'|'.$item->cycle.'|'.$dateStr;
            })->map(function ($items) {
                $first = $items->first();

                $pickedCount = $items->where('truck_status', 'picked')->count();
                $totalCount = $items->count();

                if ($pickedCount === 0) {
                    $truckStatus = 'pending';
                } elseif ($pickedCount >= $totalCount) {
                    $truckStatus = 'picked';
                } else {
                    $truckStatus = 'partial';
                }

                $pickedAt = $items->max('truck_picked_at');

                $numbers = $items->pluck('number')->toArray();
                if (count($numbers) > 2) {
                    $numberDisplay = '<strong>'.count($numbers).' LL</strong> <span class="text-muted" style="font-size: 11px;">('.implode(', ', array_slice($numbers, 0, 2)).'...)</span>';
                } else {
                    $numberDisplay = '<strong>'.count($numbers).' LL</strong> <span class="text-muted" style="font-size: 11px;">('.implode(', ', $numbers).')</span>';
                }

                return (object) [
                    'id' => $first->id,
                    'number' => $numberDisplay,
                    'customer_name' => optional($first->customer)->name ?? '-',
                    'cycle' => $first->cycle,
                    'delivery_date_raw' => $first->delivery_date,
                    'truck_status' => $truckStatus,
                    'truck_picked_at' => $pickedAt,
                    'customer_id' => $first->customer_id,
                ];
            })->values();

            return DataTables::of($groupedData)
                ->addColumn('delivery_date_fmt', function ($row) {
                    return $row->delivery_date_raw
                        ? Carbon::parse($row->delivery_date_raw)->format('d/m/Y H:i')
                        : '-';
                })
                ->addColumn('truck_status_badge', function ($row) {
                    return $this->formatTruckStatusBadge($row->truck_status ?? 'pending', true);
                })
                ->addColumn('picked_at_fmt', function ($row) {
                    return $row->truck_picked_at
                        ? Carbon::parse($row->truck_picked_at)->format('d/m/Y H:i')
                        : '-';
                })
                ->addColumn('arrival_status_badge', function ($row) use ($masterMeta) {
                    if ($row->truck_status !== 'picked') {
                        return '<span class="text-muted">-</span>';
                    }

                    $master = $this->findMasterCycleForDeliveryBucket(
                        (int) $row->customer_id,
                        (string) $row->cycle,
                        $masterMeta
                    );

                    $deliveryDateStr = $row->delivery_date_raw
                        ? Carbon::parse($row->delivery_date_raw)->format('Y-m-d')
                        : Carbon::now()->format('Y-m-d');

                    $status = $this->calculateTruckArrivalStatus(
                        $deliveryDateStr,
                        $row->truck_picked_at,
                        $master
                    );

                    return $this->formatTruckArrivalStatusBadge($status);
                })
                ->rawColumns(['truck_status_badge', 'number', 'arrival_status_badge'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: '.$e->getMessage(),
            ]);
        }
    }

    public function scanDriverAbsenCustomer(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:500',
            'delivery_date' => 'nullable|date',
        ]);

        $customer = $this->resolveCustomerFromLabelScan($validated['barcode']);
        if (! $customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan dari scan label.',
            ], 422);
        }

        $deliveryDate = $validated['delivery_date'] ?? null;
        $cycles = $this->getDistinctCyclesForCustomer($customer->id, $deliveryDate);

        return response()->json([
            'status' => 'success',
            'customer' => [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
            ],
            'cycles' => $cycles,
        ]);
    }

    public function getDriverAbsenCycles(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'nullable|date',
        ]);

        return response()->json([
            'cycles' => $this->getDistinctCyclesForCustomer((int) $validated['customer_id'], $validated['delivery_date'] ?? null),
        ]);
    }

    public function submitDriverAbsen(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'cycle' => 'required|string|max:120',
            'barcode' => 'nullable|string|max:500',
            'delivery_date' => 'nullable|date',
        ]);

        $customerId = (int) $validated['customer_id'];
        $cycleInput = trim($validated['cycle']);
        $deliveryDate = $validated['delivery_date'] ?? null;

        if (! empty($validated['barcode'])) {
            $resolved = $this->resolveCycleFromBarcode($customerId, $validated['barcode']);
            if (! $resolved) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cycle / barcode tidak dikenali untuk customer ini.',
                ], 422);
            }
            $cycleInput = $resolved;
        }

        $loadingLists = $this->findLoadingListsByCustomerAndCycle($customerId, $cycleInput, $deliveryDate);
        if ($loadingLists->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loading list untuk customer dan cycle tersebut tidak ditemukan.',
            ], 404);
        }

        $alreadyPicked = $loadingLists->every(fn (LoadingList $ll) => ($ll->truck_status ?? 'pending') === 'picked');
        if ($alreadyPicked) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Truk untuk cycle ini sudah pernah di-absen (sudah diambil).',
                'updated_count' => 0,
                'loading_lists' => $loadingLists->pluck('number')->values(),
            ]);
        }

        $now = now();
        $userId = Auth::id();

        DB::transaction(function () use ($loadingLists, $now, $userId) {
            LoadingList::query()
                ->whereIn('id', $loadingLists->pluck('id'))
                ->update([
                    'truck_status' => 'picked',
                    'truck_picked_at' => $now,
                    'truck_picked_by' => $userId,
                ]);
        });

        $this->pushData(true);

        return response()->json([
            'status' => 'success',
            'message' => 'Absen driver berhasil. Status truk diperbarui menjadi sudah diambil.',
            'updated_count' => $loadingLists->count(),
            'cycle' => $cycleInput,
            'loading_lists' => $loadingLists->pluck('number')->values(),
        ]);
    }

    public function getLoadingList(Request $request)
    {
        try {
            $draw = max(0, (int) $request->input('draw', 0));
            $start = max(0, (int) $request->input('start', 0));
            $length = min(100, max(10, (int) $request->input('length', 10)));

            $totalRecords = Cache::remember('loading_list_pds_count_v2', 30, function () {
                return LoadingList::query()
                    ->whereNotNull('pds_number')
                    ->where('pds_number', '<>', '')
                    ->distinct()
                    ->count('pds_number');
            });

            $baseQuery = DB::table('loading_lists as ll')
                ->whereNotNull('ll.pds_number')
                ->where('ll.pds_number', '<>', '');

            $this->applyLoadingListTableFilters($baseQuery, $request);

            $hasFilters = collect([
                $request->input('manifest'),
                $request->input('customer'),
                $request->input('cycle'),
                $request->input('delivery_date'),
                $request->input('search.value'),
            ])->contains(fn ($value) => trim((string) $value) !== '');
            $filteredRecords = $hasFilters
                ? (clone $baseQuery)->distinct()->count('ll.pds_number')
                : $totalRecords;

            $summaryQuery = (clone $baseQuery)
                ->selectRaw('ll.pds_number,
                    MIN(ll.customer_id) as customer_id,
                    MIN(NULLIF(ll.cycle, "")) as cycle,
                    MIN(ll.delivery_date) as delivery_date,
                    COUNT(*) as loading_list_count,
                    0 as truck_picked_count,
                    MAX(ll.updated_at) as updated_at')
                ->groupBy('ll.pds_number');

            $orderMap = [
                0 => 'pds_number',
                1 => 'customer',
                2 => 'cycle',
                3 => 'delivery_date',
            ];
            $orderIndex = (int) $request->input('order.0.column', 3);
            $orderColumn = $orderMap[$orderIndex] ?? 'delivery_date';
            $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $groups = $summaryQuery
                ->orderBy($orderColumn, $orderDirection)
                ->orderBy('pds_number')
                ->offset($start)
                ->limit($length)
                ->get();

            $pdsNumbers = $groups->pluck('pds_number')->filter()->values()->all();
            $kanbanTotals = collect();
            if ($pdsNumbers !== []) {
                $kanbanTotals = DB::table('loading_lists as ll')
                    ->leftJoin('loading_list_details as lld', 'lld.loading_list_id', '=', 'll.id')
                    ->whereIn('ll.pds_number', $pdsNumbers)
                    ->selectRaw('ll.pds_number,
                        COALESCE(SUM(lld.kanban_qty), 0) as total_kanban,
                        COALESCE(SUM(lld.actual_kanban_qty), 0) as actual_kanban')
                    ->groupBy('ll.pds_number')
                    ->get()
                    ->keyBy('pds_number');
            }

            $customerNames = Customer::query()
                ->whereIn('id', $groups->pluck('customer_id')->filter()->unique()->all())
                ->pluck('name', 'id');

            $data = $groups->map(function ($group) use ($kanbanTotals, $customerNames) {
                $totals = $kanbanTotals->get($group->pds_number);
                $totalKanban = (int) ($totals->total_kanban ?? 0);
                $actualKanban = (int) ($totals->actual_kanban ?? 0);
                $loadingListCount = (int) $group->loading_list_count;
                $pickedCount = (int) $group->truck_picked_count;
                $truckStatus = $pickedCount === 0
                    ? 'pending'
                    : ($pickedCount >= $loadingListCount ? 'picked' : 'partial');

                return [
                    'id' => 'pds-'.$group->pds_number,
                    'DT_RowId' => 'row-pds-'.$group->pds_number,
                    'pds_number' => $group->pds_number,
                    'customer' => $customerNames[$group->customer_id] ?? '-',
                    'cycle' => $group->cycle ?: '-',
                    'delivery_date' => $group->delivery_date
                        ? Carbon::parse($group->delivery_date)->format('Y-m-d H:i:s')
                        : '-',
                    'progress_percentage' => $this->calculateLoadingListProgress($totalKanban, $actualKanban),
                    'progress' => $this->formatLoadingListProgress($totalKanban, $actualKanban),
                    'loading_and_status' => $this->formatLoadingListActions(
                        $group->pds_number,
                        $totalKanban,
                        $actualKanban
                    ),
                    'truck_status' => $truckStatus,
                    'truck_status_badge' => $this->formatTruckStatusBadge($truckStatus, true),
                    'updated_at' => $group->updated_at,
                ];
            })->values();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: '.$e->getMessage(),
            ]);
        }
    }

    // New method to get loading lists by PDS number for accordion
    public function getLoadingListsByPds(Request $request)
    {
        try {
            $pdsNumber = $request->get('pds_number');

            if (! $pdsNumber) {
                return response()->json(['error' => 'PDS number is required'], 400);
            }

            $loadingLists = LoadingList::where('pds_number', $pdsNumber)
                ->with(['customer'])
                ->withSum('detail as total_kanban', 'kanban_qty')
                ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                ->orderBy('number', 'asc')
                ->get()
                ->map(function ($loadingList) {
                    return [
                        'id' => $loadingList->id,
                        'number' => $loadingList->number,
                        'pds_number' => $loadingList->pds_number,
                        'customer_name' => $loadingList->customer->name ?? null,
                        'cycle' => $loadingList->cycle,
                        'delivery_date' => $loadingList->delivery_date,
                        'truck_status' => $loadingList->truck_status ?? 'pending',
                        'truck_status_badge' => $this->formatTruckStatusBadge($loadingList->truck_status ?? 'pending'),
                        'truck_picked_at' => $loadingList->truck_picked_at,
                        'total_kanban' => $loadingList->total_kanban ?? 0,
                        'actual_kanban' => $loadingList->actual_kanban ?? 0,
                        'created_at' => $loadingList->created_at,
                        'updated_at' => $loadingList->updated_at,
                    ];
                });

            return response()->json([
                'loading_lists' => $loadingLists,
                'pds_number' => $pdsNumber,
                'total_count' => $loadingLists->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to load loading lists: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkLoadingListUpdates(Request $request)
    {
        try {
            $currentState = $request->input('state', []);
            $currentPdsCount = (int) ($currentState['pdsCount'] ?? 0);
            $latestPdsNumbers = $currentState['latestPdsNumbers'] ?? [];

            $serverPdsCount = LoadingList::query()
                ->whereNotNull('pds_number')
                ->where('pds_number', '<>', '')
                ->distinct()
                ->count('pds_number');
            $serverLatestPds = LoadingList::query()
                ->whereNotNull('pds_number')
                ->where('pds_number', '<>', '')
                ->selectRaw('pds_number, MAX(created_at) as latest_created_at')
                ->groupBy('pds_number')
                ->orderByDesc('latest_created_at')
                ->limit(10)
                ->pluck('pds_number')
                ->toArray();

            $hasNewData = $serverPdsCount !== $currentPdsCount
                || count(array_diff($serverLatestPds, $latestPdsNumbers)) > 0;

            return response()->json([
                'hasNewData' => $hasNewData,
                'serverPdsCount' => $serverPdsCount,
                'serverLatestPds' => $serverLatestPds,
                'totalRecords' => $serverPdsCount,
                'latestPdsNumbers' => $serverLatestPds,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true], 500);
        }
    }

    public function getLoadingListUpdates(Request $request)
    {
        try {
            $pdsNumbers = collect($request->input('ids', []))
                ->map(fn ($id) => trim(str_replace(['row-pds-', 'pds-'], '', (string) $id)))
                ->filter()
                ->unique()
                ->take(100)
                ->values();

            if ($pdsNumbers->isEmpty()) {
                return response()->json(['updatedRows' => [], 'deletedRows' => []]);
            }

            $groups = DB::table('loading_lists as ll')
                ->leftJoin('loading_list_details as lld', 'lld.loading_list_id', '=', 'll.id')
                ->whereIn('ll.pds_number', $pdsNumbers->all())
                ->selectRaw('ll.pds_number,
                    COALESCE(SUM(lld.kanban_qty), 0) as total_kanban,
                    COALESCE(SUM(lld.actual_kanban_qty), 0) as actual_kanban,
                    MAX(ll.updated_at) as loading_list_updated_at,
                    MAX(lld.updated_at) as detail_updated_at')
                ->groupBy('ll.pds_number')
                ->get()
                ->keyBy('pds_number');

            $updatedGroups = $groups->map(function ($group) {
                $totalKanban = (int) $group->total_kanban;
                $actualKanban = (int) $group->actual_kanban;

                return [
                    'id' => 'row-pds-'.$group->pds_number,
                    'progress_percentage' => $this->calculateLoadingListProgress($totalKanban, $actualKanban),
                    'progress' => $this->formatLoadingListProgress($totalKanban, $actualKanban),
                    'loading_and_status' => $this->formatLoadingListActions(
                        $group->pds_number,
                        $totalKanban,
                        $actualKanban
                    ),
                    'updated_at' => max(
                        (string) ($group->loading_list_updated_at ?? ''),
                        (string) ($group->detail_updated_at ?? '')
                    ),
                ];
            })->values();

            $deletedRows = $pdsNumbers
                ->reject(fn ($pdsNumber) => $groups->has($pdsNumber))
                ->map(fn ($pdsNumber) => 'row-pds-'.$pdsNumber)
                ->values();

            return response()->json([
                'updatedRows' => $updatedGroups,
                'deletedRows' => $deletedRows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Unable to get row updates',
            ], 500);
        }
    }

    private function applyLoadingListTableFilters($query, Request $request): void
    {
        $manifest = trim((string) $request->input('manifest', ''));
        if ($manifest !== '') {
            $query->where('ll.pds_number', $manifest);
        }

        $customer = trim((string) $request->input('customer', ''));
        if ($customer !== '') {
            $customerId = Customer::query()->where('name', $customer)->value('id');
            $customerId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('ll.customer_id', $customerId);
        }

        $cycleRaw = trim((string) $request->input('cycle', ''));
        if ($cycleRaw !== '') {
            $cycleNumber = null;
            if (preg_match('/\d+/', $cycleRaw, $matches)) {
                $cycleNumber = (string) ((int) $matches[0]);
            }

            $query->where(function ($cycleQuery) use ($cycleRaw, $cycleNumber) {
                $cycleQuery->where('ll.cycle', $cycleRaw);
                if ($cycleNumber !== null) {
                    $cycleQuery->orWhere('ll.cycle', $cycleNumber)
                        ->orWhere('ll.cycle', str_pad($cycleNumber, 2, '0', STR_PAD_LEFT))
                        ->orWhereRaw('LOWER(ll.cycle) = ?', ['cycle '.$cycleNumber]);
                }
            });
        }

        if ($request->filled('delivery_date')) {
            $dateStart = Carbon::parse($request->input('delivery_date'))->startOfDay();
            $dateEnd = $dateStart->copy()->endOfDay();
            $query->whereBetween('ll.delivery_date', [$dateStart, $dateEnd]);
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $customerIds = Customer::query()->where('name', 'like', $like)->pluck('id')->all();
            $query->where(function ($searchQuery) use ($like, $customerIds) {
                $searchQuery->where('ll.pds_number', 'like', $like)
                    ->orWhere('ll.cycle', 'like', $like);
                if ($customerIds !== []) {
                    $searchQuery->orWhereIn('ll.customer_id', $customerIds);
                }
            });
        }
    }

    private function formatLoadingListProgress(int $totalKanban, int $actualKanban): string
    {
        $progressPercentage = $this->calculateLoadingListProgress($totalKanban, $actualKanban);
        $statusColor = $actualKanban >= $totalKanban && $totalKanban > 0
            ? '#16a34a'
            : ($actualKanban > 0 ? '#d97706' : '#dc2626');

        return '<div class="text-small font-weight-bold text-muted mb-1 text-center">'
            .$actualKanban.' / '.$totalKanban
            .'</div><div class="progress" data-height="20" style="height:18px;">'
            .'<div class="progress-bar" role="progressbar" style="width:'.$progressPercentage
            .'%;background-color:'.$statusColor.' !important" aria-valuenow="'.$progressPercentage
            .'" aria-valuemin="0" aria-valuemax="100"><small class="text-white font-weight-bold">'
            .$progressPercentage.'%</small></div></div>';
    }

    private function calculateLoadingListProgress(int $totalKanban, int $actualKanban): int
    {
        return $totalKanban > 0
            ? (int) round(($actualKanban / $totalKanban) * 100)
            : 0;
    }

    private function formatLoadingListActions(string $pdsNumber, int $totalKanban, int $actualKanban): string
    {
        $buttonStyle = 'style="min-width:120px;padding:8px 12px;font-size:13px;font-weight:500;text-align:center;white-space:nowrap;"';
        $loadingButton = '<button class="btn btn-info text-white mr-2 show-loading-lists" '
            .$buttonStyle.' data-pds="'.e($pdsNumber).'"><i class="fas fa-info-circle mr-1"></i>Detail</button>';

        if ($actualKanban >= $totalKanban && $totalKanban > 0) {
            $statusButton = '<button class="btn btn-success" '.$buttonStyle
                .'><i class="fas fa-check mr-1"></i>COMPLETE</button>';
        } elseif ($actualKanban > 0) {
            $statusButton = '<button class="btn btn-outline-warning" '.$buttonStyle.'>IN PROGRESS</button>';
        } else {
            $statusButton = '<button class="btn btn-outline-danger" '.$buttonStyle.'>INCOMPLETE</button>';
        }

        return '<div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;align-items:center;">'
            .$loadingButton.$statusButton.'</div>';
    }

    public function detail(LoadingList $loadingList)
    {
        $loadingList->loadMissing('customer:id,name');
        $prepContext = $this->resolveLoadingListPrepContext($loadingList);

        return view('pages.loadingListDetail', [
            'loadingListDetail' => $loadingList,
            'loadingListId' => $loadingList->id,
            'prepContext' => $prepContext,
        ]);
    }

    private function resolveLoadingListPrepContext(LoadingList $loadingList): array
    {
        $context = [
            'master_cycle_configured' => false,
            'prep_range_label' => '-',
            'deadline_iso' => null,
            'deadline_label' => '-',
            'pulling_iso' => null,
            'pulling_label' => '-',
            'delta_seconds' => null,
            'delta_label' => '-',
            'status' => 'unknown',
            'status_label' => 'Belum ada pulling',
        ];

        $deadline = null;
        $master = MasterCycle::query()
            ->where('customer_id', (int) $loadingList->customer_id)
            ->get()
            ->first(function (MasterCycle $master) use ($loadingList) {
                return $this->cycleLabelMatchesMaster($master->cycle_name, $loadingList->cycle);
            });

        if ($master && $loadingList->delivery_date) {
            $meta = $this->formatMasterCycleForApi($master);
            $absolute = $this->computePrepAbsoluteDatetimes(
                Carbon::parse($loadingList->delivery_date)->toDateString(),
                (int) ($meta['prep_day_offset'] ?? 0),
                (int) ($meta['prep_end_day_offset'] ?? 0),
                $meta['time'] ?? null,
                $meta['prep_end_time'] ?? null
            );

            if (! empty($absolute['absolute_end_datetime'])) {
                $deadline = Carbon::parse($absolute['absolute_end_datetime']);
                $context['master_cycle_configured'] = true;
                $context['deadline_iso'] = $deadline->toIso8601String();
                $context['deadline_label'] = $this->formatPrepContextDateTime($deadline);
                $context['prep_range_label'] = trim(($meta['time'] ?? '-').' - '.($meta['prep_end_time'] ?? '-'));
            }
        }

        $pullingDate = $this->resolveScopedLoadingListPullingDate($loadingList)
            ?: $this->resolveFallbackPullingDate($loadingList);

        $context = $this->applyPrepContextPullingDate($context, $pullingDate);

        return $context;
    }

    private function resolveScopedLoadingListPullingDate(LoadingList $loadingList): ?string
    {
        if (! $loadingList->id) {
            return null;
        }

        // Precise: pakai linkage loading_list_detail_id supaya tidak salah ambil pull dari cycle lain.
        $detailIds = DB::table('loading_list_details')
            ->where('loading_list_id', $loadingList->id)
            ->pluck('id');

        if ($detailIds->isNotEmpty()) {
            $linked = DB::table('mutations')
                ->where('type', 'checkout')
                ->whereIn('loading_list_detail_id', $detailIds)
                ->selectRaw('MAX(COALESCE(date, created_at)) as latest')
                ->value('latest');

            if ($linked) {
                return (string) $linked;
            }
        }

        // Fallback (data lama tanpa linkage): window waktu detail.
        $latest = DB::table('loading_list_details as lld')
            ->join('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
            ->join('mutations as m', function ($join) {
                $join->on('m.internal_part_id', '=', 'cp.internal_part_id')
                    ->where('m.type', '=', 'checkout')
                    ->whereColumn('m.created_at', '>=', 'lld.created_at')
                    ->whereColumn('m.created_at', '<=', 'lld.updated_at');
            })
            ->where('lld.loading_list_id', $loadingList->id)
            ->selectRaw('MAX(COALESCE(m.date, m.created_at)) as latest')
            ->value('latest');

        return $latest ? (string) $latest : null;
    }

    private function resolveFallbackPullingDate(LoadingList $loadingList): ?string
    {
        $pullingDate = null;
        if ($loadingList->number) {
            $pullingDate = DB::table('pullings')
                ->where('customer_id', (int) $loadingList->customer_id)
                ->where('loading_list', $loadingList->number)
                ->whereNotNull('pulling_date')
                ->max('pulling_date');
        }
        if (! $pullingDate && $loadingList->pds_number) {
            $pullingDate = DB::table('pullings')
                ->where('customer_id', (int) $loadingList->customer_id)
                ->where('pds_number', $loadingList->pds_number)
                ->whereNotNull('pulling_date')
                ->max('pulling_date');
        }

        return $pullingDate ? (string) $pullingDate : null;
    }

    private function applyPrepContextPullingDate(array $context, $pullingDate): array
    {
        $context['pulling_iso'] = null;
        $context['pulling_label'] = '-';
        $context['delta_seconds'] = null;
        $context['delta_label'] = '-';
        $context['status'] = 'unknown';

        if ($pullingDate) {
            $pulling = Carbon::parse($pullingDate);
            $deadline = ! empty($context['deadline_iso'])
                ? Carbon::parse($context['deadline_iso'])
                : null;

            $context['pulling_iso'] = $pulling->toIso8601String();
            $context['pulling_label'] = $this->formatPrepContextDateTime($pulling);

            return array_merge($context, $this->buildPrepDeltaPayload($pulling, $deadline));
        }

        $context['status_label'] = ! empty($context['deadline_iso'])
            ? 'Belum ada pulling'
            : 'Master cycle belum lengkap';

        return $context;
    }

    private function buildPrepDeltaPayload(?Carbon $eventAt, ?Carbon $deadline): array
    {
        if (! $eventAt || ! $deadline) {
            return [
                'delta_seconds' => null,
                'delta_label' => '-',
                'status' => 'unknown',
                'status_label' => 'Belum bisa dihitung',
            ];
        }

        $deltaSeconds = $eventAt->getTimestamp() - $deadline->getTimestamp();

        return [
            'delta_seconds' => $deltaSeconds,
            'delta_label' => $this->formatSignedDeltaSeconds($deltaSeconds),
            'status' => $deltaSeconds > 0 ? 'late' : 'safe',
            'status_label' => $deltaSeconds > 0 ? 'Telat' : 'Aman',
        ];
    }

    private function buildMutationPrepDeltaPayload($eventAt, array $prepContext): array
    {
        if (empty($prepContext['deadline_iso']) || empty($eventAt)) {
            return [
                'prep_delta_seconds' => null,
                'prep_delta_label' => '-',
                'prep_status' => 'unknown',
                'prep_status_label' => 'Belum bisa dihitung',
            ];
        }

        $delta = $this->buildPrepDeltaPayload(
            Carbon::parse($eventAt),
            Carbon::parse($prepContext['deadline_iso'])
        );

        return [
            'prep_delta_seconds' => $delta['delta_seconds'],
            'prep_delta_label' => $delta['delta_label'],
            'prep_status' => $delta['status'],
            'prep_status_label' => $delta['status_label'],
        ];
    }

    private function formatPrepContextDateTime(?Carbon $date): string
    {
        return $date ? $date->locale('id')->translatedFormat('d M, H.i') : '-';
    }

    private function formatSignedDeltaSeconds(int $seconds): string
    {
        $minutes = (int) round(abs($seconds) / 60);
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        $prefix = $seconds > 0 ? '+' : ($seconds < 0 ? '-' : '');

        if ($hours > 0 && $mins > 0) {
            return $prefix.$hours.'j '.$mins.'m';
        }
        if ($hours > 0) {
            return $prefix.$hours.'j';
        }

        return $prefix.$minutes.'m';
    }

    public function getLoadingListDetail(LoadingList $loadingList)
    {
        $details = LoadingListDetail::with(['customerPart.internalPart'])
            ->where('loading_list_id', $loadingList->id)
            ->get()
            ->map(function (LoadingListDetail $detail) {
                $customerPart = $detail->customerPart;
                $internalPart = $customerPart?->internalPart;

                return [
                    'id' => $detail->id,
                    'loading_list_id' => $detail->loading_list_id,
                    'customer_part_id' => $detail->customer_part_id,
                    'cust_partno' => $customerPart?->part_number ?? '-',
                    'int_partno' => $internalPart?->part_number ?? '-',
                    'cust_backno' => $customerPart?->back_number ?? '-',
                    'int_backno' => $internalPart?->back_number ?? '-',
                    'kbn_qty' => (int) ($detail->kanban_qty ?? 0),
                    'actual_kbn_qty' => (int) ($detail->actual_kanban_qty ?? 0),
                ];
            });

        return response()->json(['data' => $details]);
    }

    public function getLoadingListDetailComparison(LoadingListDetail $detail)
    {
        $detail->loadMissing('customerPart:id,internal_part_id', 'loadingList.customer:id,name');
        $internalPartId = $detail->customerPart?->internal_part_id;
        $prepContext = $detail->loadingList
            ? $this->resolveLoadingListPrepContext($detail->loadingList)
            : [];
        $start = $detail->created_at;
        $end = $detail->updated_at;

        if (! $internalPartId || ! $start || ! $end || $start->equalTo($end)) {
            return response()->json([
                'detail_id' => $detail->id,
                'prep_context' => $prepContext,
                'pulling' => [],
                'production' => [],
            ]);
        }

        // Scope presisi ke detail ini via loading_list_detail_id; data lama (belum ada linkage) fallback ke window waktu.
        $hasLinkage = DB::table('mutations')
            ->where('loading_list_detail_id', $detail->id)
            ->where('type', 'checkout')
            ->exists();

        $checkouts = DB::table('mutations as checkout')
            ->select('checkout.id', 'checkout.serial_number', 'checkout.customer_serial', 'checkout.qty', 'checkout.date', 'checkout.created_at')
            ->selectSub(function ($query) {
                $query->from('mutations as supply')
                    ->select('supply.id')
                    ->whereColumn('supply.internal_part_id', 'checkout.internal_part_id')
                    ->where('supply.type', 'supply')
                    ->whereColumn('supply.serial_number', 'checkout.serial_number')
                    ->whereColumn('supply.created_at', '<', 'checkout.created_at')
                    ->orderByDesc('supply.created_at')
                    ->orderByDesc('supply.id')
                    ->limit(1);
            }, 'supply_id')
            ->where('checkout.internal_part_id', $internalPartId)
            ->where('checkout.type', 'checkout')
            ->when(
                $hasLinkage,
                fn ($q) => $q->where('checkout.loading_list_detail_id', $detail->id),
                fn ($q) => $q->whereBetween('checkout.created_at', [$start, $end])
            )
            ->orderBy('checkout.created_at')
            ->get();

        $detailPullingDate = $checkouts
            ->map(fn ($checkout) => $checkout->date ?: $checkout->created_at)
            ->filter()
            ->max();
        $prepContext = $this->applyPrepContextPullingDate($prepContext, $detailPullingDate);

        $supplies = DB::table('mutations')
            ->whereIn('id', $checkouts->pluck('supply_id')->filter()->unique()->values()->all())
            ->get(['id', 'date', 'qty'])
            ->keyBy('id');

        $pulling = $checkouts->map(function ($checkout) use ($prepContext) {
            $eventAt = $checkout->date ?: $checkout->created_at;

            return array_merge([
                'id' => $checkout->id,
                'serial_number' => $checkout->serial_number,
                'customer_serial' => $checkout->customer_serial,
                'date' => $checkout->date,
                'created_at' => $checkout->created_at,
                'qty' => $checkout->qty,
            ], $this->buildMutationPrepDeltaPayload($eventAt, $prepContext));
        });

        $production = $checkouts->map(function ($checkout) use ($supplies) {
            $supply = $supplies->get($checkout->supply_id);
            if (! $supply) {
                return null;
            }

            return [
                'id' => $supply->id,
                'serial_number' => $checkout->serial_number,
                'date' => $supply->date,
                'qty' => $supply->qty,
            ];
        })->filter()->values();

        return response()->json([
            'detail_id' => $detail->id,
            'prep_context' => $prepContext,
            'pulling' => $pulling,
            'production' => $production,
        ]);
    }

    public function deletePullingMutation(LoadingListDetail $detail, Mutation $mutation)
    {
        $internalPartId = optional(optional($detail->customerPart)->internalPart)->id;
        if (! $internalPartId) {
            return response()->json(['status' => 'error', 'message' => 'Internal part tidak ditemukan.'], 404);
        }

        $start = $detail->created_at;
        $end = $detail->updated_at;

        // Validasi: mutation harus checkout milik internal_part ini, dan benar milik detail ini.
        // Utamakan linkage presisi (loading_list_detail_id); data lama tanpa linkage fallback ke window waktu.
        if ($mutation->loading_list_detail_id !== null) {
            $matchesDetail = (int) $mutation->loading_list_detail_id === (int) $detail->id;
        } else {
            if (! $start || ! $end || $start->equalTo($end)) {
                return response()->json(['status' => 'error', 'message' => 'Window waktu detail tidak valid.'], 422);
            }
            $matchesDetail = $mutation->created_at >= $start && $mutation->created_at <= $end;
        }

        $isValid = $mutation->internal_part_id == $internalPartId
            && $mutation->type === 'checkout'
            && $matchesDetail;

        if (! $isValid) {
            return response()->json(['status' => 'error', 'message' => 'Mutation tidak valid untuk dihapus.'], 403);
        }

        DB::transaction(function () use ($detail, $mutation) {
            // delete 1 record mutation
            $mutation->delete();

            // ✅ setiap mutation = 1 scan, jadi -1
            $current = (int) ($detail->actual_kanban_qty ?? 0);
            $newVal = $current - 1;
            if ($newVal < 0) {
                $newVal = 0;
            }

            $detail->actual_kanban_qty = $newVal;
            $detail->save();
        });

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil hapus pulling mutation id: {$mutation->id} dan actual qty dikurangi 1.",
            'data' => [
                'detail_id' => $detail->id,
                'actual_kanban_qty' => (int) $detail->fresh()->actual_kanban_qty,
            ],
        ]);
    }

    public function destroy(LoadingList $loadingList)
    {
        try {
            // loading_list_details ikut terhapus via FK onDelete cascade
            $loadingList->delete();
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus loading list: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Loading list berhasil dihapus.',
        ]);
    }

    public function editLoadingListDetail($loadingList, $customerPart, $backNumber, $newActual)
    {
        if ($backNumber == 'null') {
            $backNumber = null;
        }

        // get customer part id
        $customerPartId = CustomerPart::select('id')
                            ->where('part_number', $customerPart)
                            ->where('back_number', $backNumber)
                            ->first();

        // get kanban qty
        $maxKanbanQty = LoadingListDetail::select('id', 'kanban_qty')
                                        ->where('loading_list_id', $loadingList)
                                        ->where('customer_part_id', $customerPartId->id)
                                        ->first();

        if ($newActual > $maxKanbanQty->kanban_qty) {
            return [
                'status' => 'error',
                'message' => 'Tidak boleh lebih dari kuantitas kanban!',
            ];
        }

        try {
            DB::beginTransaction();

            // update loading list detail based on loading list id and customer part
            LoadingListDetail::where('loading_list_id', $loadingList)
                                ->where('customer_part_id', $customerPartId->id)
                                ->update([
                                    'actual_kanban_qty' => $newActual,
                                ]);

            DB::commit();

            // push to websocket
            // $this->pushData(true);

            return response()->json([
                'status' => 'success',
                'data' => $newActual,
                'message' => 'Data berhasil diupdate!',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function store($loadingList, $pds, $cycle, $customerCode, $shippingDate, $deliveryDate)
    {
        // Kondisi khusus: customer_code 7A00022 dan PDS mengandung 'RK11'
        if ($customerCode == '7A00022' && str_contains($pds, 'KK11')) {
            $customer = Customer::select('id')
                ->where('code', $customerCode)
                ->where('name', 'like', '%SUZUKI RKK11%')
                ->first();
        } else {
            $customer = Customer::select('id')
                ->where('code', $customerCode)
                ->first();
        }

        if (! $customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan!',
            ], 404);
        }

        $check = LoadingList::where('number', $loadingList)->first();

        if (! $check) {
            try {
                DB::beginTransaction();

                LoadingList::create([
                    'number' => $loadingList,
                    'pds_number' => $pds,
                    'cycle' => $cycle,
                    'customer_id' => $customer->id,
                    'delivery_date' => $deliveryDate,
                    'shipping_date' => $shippingDate,
                ]);

                // push to websocket
                // $this->pushData(true);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Loading list tersimpan!',
        ], 200);
    }

    public function storeDetail($loadingList, $customerPart, $internalPart, $kbnQty, $qtyPerKanban, $totalQty, $actualKanbanQty)
    {
        // get loading list id
        $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        if (! $loadingListId) {
            return [
                'status' => 'llNotExists',
            ];
        }

        $convertedPartNumber = $this->convertCustomerPartNumber($customerPart, $loadingListId->customer_id);

        // get customer part id
        $customerPartId = DB::table('customer_parts')
                        ->join('internal_parts', 'internal_parts.id', '=', 'customer_parts.internal_part_id')
                        ->select('customer_parts.id')
                        ->where('customer_parts.customer_id', $loadingListId->customer_id)
                        ->where('internal_parts.part_number', $internalPart)
                        ->where('customer_parts.part_number', $convertedPartNumber)
                        ->first();

        if (! $customerPartId) {
            return [
                'status' => 'partNotExists',
                'data' => [
                    'int' => $internalPart,
                    'cust' => $convertedPartNumber,
                ],
            ];
        }

        // check and insert if the loading list exist and customer part does not exist
        $loadingListCheck = LoadingListDetail::firstOrNew([
            'loading_list_id' => $loadingListId->id,
            'customer_part_id' => $customerPartId->id,
        ]);

        if (! $loadingListCheck->exists) {
            try {
                LoadingListDetail::create([
                    'loading_list_id' => $loadingListId->id,
                    'customer_part_id' => $customerPartId->id,
                    'kanban_qty' => $kbnQty,
                    'qty_per_kanban' => $qtyPerKanban,
                    'total_qty' => $totalQty,
                    'actual_kanban_qty' => $actualKanbanQty,
                ]);
            } catch (\Throwable $th) {
                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail loading list tersimpan!',
        ], 200);
    }

    /**
     * Bulk simpan detail loading list dalam SATU request + SATU transaksi.
     * Menggantikan pemanggilan storeDetail berulang (yang sebelumnya di-stagger 200ms/item).
     */
    public function storeDetails(Request $request)
    {
        $validated = $request->validate([
            'loadingList' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.part_number_cust' => 'required|string',
            'items.*.part_number_int' => 'required|string',
            'items.*.total_kanban_qty' => 'required|numeric',
            'items.*.total_qty' => 'required|numeric',
            'items.*.actual_kanban_qty' => 'nullable|numeric',
        ]);

        $loadingList = LoadingList::select('id', 'customer_id')
            ->where('number', $validated['loadingList'])
            ->first();

        if (! $loadingList) {
            return response()->json([
                'status' => 'llNotExists',
                'message' => 'Loading list tidak terdaftar!',
            ], 404);
        }

        // 1) Resolve setiap item: konversi part number + cari internal_part_id (bulk, hindari N+1)
        $internalNumbers = collect($validated['items'])
            ->pluck('part_number_int')
            ->map(fn ($v) => trim((string) $v))
            ->unique()
            ->values()
            ->all();

        $internalParts = InternalPart::whereIn('part_number', $internalNumbers)
            ->get(['id', 'part_number'])
            ->keyBy(fn ($part) => trim((string) $part->part_number));

        $resolved = [];
        foreach ($validated['items'] as $item) {
            $custRaw = trim((string) $item['part_number_cust']);
            $intRaw = trim((string) $item['part_number_int']);
            $kbnQty = (int) $item['total_kanban_qty'];

            $resolved[] = [
                'cust_raw' => $custRaw,
                'int_raw' => $intRaw,
                'converted' => $this->convertCustomerPartNumber($custRaw, $loadingList->customer_id),
                'internal_part_id' => optional($internalParts->get($intRaw))->id,
                'kanban_qty' => $kbnQty,
                'total_qty' => (int) $item['total_qty'],
                'actual' => (int) ($item['actual_kanban_qty'] ?? 0),
                'qty_per_kanban' => $kbnQty > 0 ? ($item['total_qty'] / $kbnQty) : 0,
            ];
        }

        // 2) Ambil customer_parts sekali jalan (match internal_part_id + part_number)
        $internalIds = collect($resolved)->pluck('internal_part_id')->filter()->unique()->values()->all();
        $convertedNumbers = collect($resolved)->pluck('converted')->filter()->unique()->values()->all();

        $custPartIndex = [];
        if (! empty($internalIds) && ! empty($convertedNumbers)) {
            DB::table('customer_parts')
                ->select('id', 'internal_part_id', 'part_number')
                ->where('customer_id', $loadingList->customer_id)
                ->whereIn('internal_part_id', $internalIds)
                ->whereIn('part_number', $convertedNumbers)
                ->get()
                ->each(function ($cp) use (&$custPartIndex) {
                    $custPartIndex[$cp->internal_part_id.'|'.$cp->part_number] = $cp->id;
                });
        }

        // 3) Susun baris insert, skip yang sudah ada / tidak ketemu
        $failed = [];
        $now = now();

        DB::transaction(function () use ($resolved, $loadingList, $custPartIndex, &$failed, $now) {
            $existing = LoadingListDetail::where('loading_list_id', $loadingList->id)
                ->pluck('customer_part_id')
                ->flip(); // customer_part_id => index, untuk cek cepat

            $insert = [];
            foreach ($resolved as $r) {
                if (! $r['internal_part_id']) {
                    $failed[] = ['int' => $r['int_raw'], 'cust' => $r['cust_raw'], 'reason' => 'internal part tidak ditemukan'];

                    continue;
                }

                $cpId = $custPartIndex[$r['internal_part_id'].'|'.$r['converted']] ?? null;
                if (! $cpId) {
                    $failed[] = ['int' => $r['int_raw'], 'cust' => $r['converted'], 'reason' => 'customer part tidak ditemukan'];

                    continue;
                }

                if ($existing->has($cpId)) {
                    continue; // sudah tersimpan, jangan duplikat
                }

                $insert[] = [
                    'loading_list_id' => $loadingList->id,
                    'customer_part_id' => $cpId,
                    'kanban_qty' => $r['kanban_qty'],
                    'qty_per_kanban' => $r['qty_per_kanban'],
                    'total_qty' => $r['total_qty'],
                    'actual_kanban_qty' => $r['actual'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $existing->put($cpId, true); // cegah duplikat dalam batch yang sama
            }

            if (! empty($insert)) {
                LoadingListDetail::insert($insert); // satu bulk insert
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Detail loading list tersimpan!',
            'failed' => $failed,
        ], 200);
    }

    public function kanbanScanned(Request $request)
    {
        $loadingList = $request->loadingList;
        $customerPart = $request->customerPart;
        $internalPart = $request->internalPart;

        // get loadingList id
        $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        if (! $loadingListId) {
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!',
            ];
        }

        $convertedPartNumber = $this->convertCustomerPartNumber($customerPart, $loadingListId->customer_id);

        // get current actual kanban qty from the exact loading-list detail row
        $currentQty = LoadingListDetail::query()
                        ->from('loading_list_details as lld')
                        ->join('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
                        ->join('internal_parts as ip', 'ip.id', '=', 'cp.internal_part_id')
                        ->select('lld.kanban_qty', 'lld.actual_kanban_qty', 'lld.loading_list_id', 'lld.customer_part_id')
                        ->where('lld.loading_list_id', $loadingListId->id)
                        ->where('cp.customer_id', $loadingListId->customer_id)
                        ->where('ip.part_number', $internalPart)
                        ->where('cp.part_number', $convertedPartNumber)
                        ->first();

        if (! $currentQty) {
            return [
                'status' => 'notExists',
                'message' => 'Part number customer / loading list tidak sesuai!',
                'data' => [
                    'int' => $internalPart,
                    'cust' => $convertedPartNumber,
                ],
            ];
        }

        $actualQty = (int) $currentQty->actual_kanban_qty;
        $targetQty = (int) $currentQty->kanban_qty;

        try {
            DB::beginTransaction();

            // check if actual is below target qty
            if ($actualQty < $targetQty) {
                // update actual kanban quantity or scanned kanban quantity
                LoadingListDetail::where('loading_list_id', $loadingListId->id)
                                ->where('customer_part_id', $currentQty->customer_part_id)
                                ->update([
                                    'actual_kanban_qty' => $actualQty + 1,
                                ]);
            } else {
                return [
                    'status' => 'error',
                    'message' => 'kanban sudah penuh',
                ];
            }

            // push to websocket
            // $this->pushData(true);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $actualQty,
        ], 200);
    }

    public function fetchLoadingList($pds)
    {
        // Fetch LoadingList models with their related models preloaded
        $loadingLists = LoadingList::with([
            'detail.customerPart.internalPart.kanbanAfterPulling' => function ($query) {
                $query->latest();
            },
            'detail.customerPart.internalPart.kanbanAfterPulling.kanban',
        ])
        ->where('pds_number', $pds)
        ->get();

        // Initialize the results array and the total series variable
        $groupedResults = [];
        $totalSeries = 0;

        // Iterate over each LoadingList
        foreach ($loadingLists as $loadingList) {
            // Check if the 'detail' relationship has loaded items
            if ($loadingList->detail && $loadingList->detail->count() > 0) {
                // Iterate over each LoadingListDetail
                foreach ($loadingList->detail as $detail) {
                    // Fetch the related KanbanAfterPulling and Kanban details if available
                    $kanbanAfterPullings = $detail->customerPart->internalPart->kanbanAfterPulling;

                    // Define the maximum number of serial numbers allowed
                    $maxSerialNumbers = $detail->kanban_qty;

                    // Group results by customer part ID
                    $customerPartId = $detail->customerPart->part_number;

                    // Initialize the group if it doesn't exist
                    if (! isset($groupedResults[$customerPartId])) {
                        $groupedResults[$customerPartId] = [
                            'customer_part_id' => $customerPartId,
                            'serial_number' => [],
                        ];
                    }

                    // Collect all serial numbers
                    foreach ($kanbanAfterPullings as $kanbanAfterPulling) {
                        $kanban = optional($kanbanAfterPulling)->kanban;
                        if ($kanban && $kanban->serial_number) {
                            $groupedResults[$customerPartId]['serial_number'][] = $kanban->serial_number;
                        }
                    }

                    // Remove duplicates and slice to the maximum number allowed
                    $uniqueSerials = collect($groupedResults[$customerPartId]['serial_number'])
                        ->unique()
                        ->slice(0, $maxSerialNumbers)
                        ->values()
                        ->all();

                    // Update the grouped results with unique and limited serial numbers
                    $groupedResults[$customerPartId]['serial_number'] = $uniqueSerials;

                    // Update the total series count
                    $totalSeries += count($uniqueSerials);
                }
            }
        }

        // Convert the results to a simple array
        $results = array_values($groupedResults);

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'total_series' => $totalSeries, // Return the total series count in the response
        ]);
    }

    public function edclDetail($loadingListId, $customerPartId)
    {
        // get loading list detail id
        $loadingListDetail = LoadingListDetail::select('id')
                                ->where('loading_list_id', $loadingListId)
                                ->where('customer_part_id', $customerPartId)
                                ->first();

        if (! $loadingListDetail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found!', // Return the total series count in the response
            ]);
        }

        // get skid detail
        $skidData = SkidDetail::where('loading_list_detail_id', $loadingListDetail->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $skidData,
        ]);
    }

    /**
     * Query loading list untuk dashboard delivery (filter sama untuk tabel & grafik).
     */
    private function deliveryDashboardFilteredQuery(Request $request)
    {
        $q = LoadingList::query()
            ->with(['customer'])
            ->withSum('detail as total_kanban', 'kanban_qty')
            ->withSum('detail as actual_kanban', 'actual_kanban_qty')
            ->whereNotNull('delivery_date');

        if ($request->filled('cycle_filter')) {
            $q->where('cycle', $request->cycle_filter);
        }
        if ($request->filled('customer_filter')) {
            $q->whereHas('customer', function ($q2) use ($request) {
                $q2->where('name', $request->customer_filter);
            });
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $q->whereDate('delivery_date', '>=', $request->start_date)
                ->whereDate('delivery_date', '<=', $request->end_date);
        } elseif ($request->filled('start_date')) {
            $q->whereDate('delivery_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $q->whereDate('delivery_date', '<=', $request->end_date);
        }

        return $q;
    }

    /**
     * Agregasi persentase selesai per cycle dari koleksi loading list.
     *
     * @param  \Illuminate\Support\Collection<int, LoadingList>  $loadingLists
     * @return array<int, array<string, mixed>>
     */
    private function aggregateCyclesFromLoadingLists($loadingLists): array
    {
        $grouped = $loadingLists->groupBy(function (LoadingList $ll) {
            $c = $ll->cycle;
            if ($c === null || $c === '') {
                return '_other';
            }

            return (string) (int) $c;
        });

        return $grouped->map(function ($items, $cycleKey) {
            $total = $items->count();
            $completed = $items->filter(function (LoadingList $ll) {
                $tk = (int) ($ll->total_kanban ?? 0);
                $ak = (int) ($ll->actual_kanban ?? 0);

                return $tk > 0 && $ak >= $tk;
            })->count();

            $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

            if ($cycleKey === '_other') {
                $label = 'Cycle (lainnya)';
                $sort = 9999;
            } else {
                $n = (int) $cycleKey;
                $label = 'Cycle '.$n;
                $sort = $n;
            }

            return [
                'cycle_key' => $cycleKey,
                'label' => $label,
                'sort' => $sort,
                'total_loading_lists' => $total,
                'completed_loading_lists' => $completed,
                'percentage' => $percentage,
            ];
        })->values()->sortBy('sort')->values()->all();
    }

    /**
     * Grafik per cycle untuk filter saat ini (tanpa perlu baris / detail).
     */
    public function getDeliveryCycleChartByFilters(Request $request)
    {
        $rows = $this->deliveryDashboardFilteredQuery($request)
            ->orderByDesc('delivery_date')
            ->limit(8000)
            ->get();

        $cycles = $this->aggregateCyclesFromLoadingLists($rows);

        return response()->json([
            'cycles' => $cycles,
            'meta' => [
                'total_loading_lists' => $rows->count(),
            ],
        ]);
    }

    /**
     * Bar chart grouped per Customer & per Cycle.
     * - X axis: Cycle
     * - Each dataset: one Customer
     * - Bar color: <50% red, 50-99% yellow, 100% green
     * - Percent = completed loading list count / total loading list count
     */
    public function getDeliveryCustomerCycleChartData(Request $request)
    {
        $rows = $this->deliveryDashboardFilteredQuery($request)
            ->orderByDesc('delivery_date')
            ->limit(8000)
            ->get();

        $normalizeCycleKey = function ($cycle) {
            if ($cycle === null || $cycle === '') {
                return '_other';
            }

            return (string) (int) $cycle;
        };

        $cycleKeys = $rows->map(function (LoadingList $ll) use ($normalizeCycleKey) {
            return $normalizeCycleKey($ll->cycle);
        })->unique()->values()->all();

        $cycleMeta = collect($cycleKeys)->map(function ($cycleKey) {
            if ($cycleKey === '_other') {
                return [
                    'cycle_key' => $cycleKey,
                    'label' => 'Cycle (lainnya)',
                    'sort' => 9999,
                ];
            }

            $n = (int) $cycleKey;

            return [
                'cycle_key' => $cycleKey,
                'label' => 'Cycle '.$n,
                'sort' => $n,
            ];
        })->sortBy('sort')->values()->all();

        $byCustomerCycle = $rows->groupBy(function (LoadingList $ll) use ($normalizeCycleKey) {
            $cid = (int) ($ll->customer_id ?? 0);
            $ck = $normalizeCycleKey($ll->cycle);

            return $cid.'|'.$ck;
        });

        $customersGrouped = $rows->groupBy(function (LoadingList $ll) {
            return (int) ($ll->customer_id ?? 0);
        });

        $customersPayload = $customersGrouped->map(function ($items, $cid) use ($cycleMeta, $byCustomerCycle) {
            $first = $items->first();
            $customerName = optional($first?->customer)->name ?? '-';

            $percentages = [];
            $completedCounts = [];
            $totalCounts = [];

            foreach ($cycleMeta as $cycle) {
                $key = (int) $cid.'|'.$cycle['cycle_key'];
                $bucket = $byCustomerCycle->get($key, collect());

                $total = $bucket->count();
                $completed = $bucket->filter(function (LoadingList $ll) {
                    $tk = (int) ($ll->total_kanban ?? 0);
                    $ak = (int) ($ll->actual_kanban ?? 0);

                    return $tk > 0 && $ak >= $tk;
                })->count();

                $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

                $percentages[] = $percentage;
                $completedCounts[] = $completed;
                $totalCounts[] = $total;
            }

            return [
                'customer_id' => (int) $cid,
                'customer_name' => $customerName,
                'percentages' => $percentages,
                'completed_loading_lists' => $completedCounts,
                'total_loading_lists' => $totalCounts,
            ];
        })->sortBy('customer_name')->values()->all();

        return response()->json([
            'cycles' => $cycleMeta,
            'customers' => $customersPayload,
            'meta' => [
                'total_loading_lists' => $rows->count(),
            ],
        ]);
    }

    /**
     * Delivery dashboard: tabel ringkasan per Customer + Tanggal (agregat kanban sama seperti baris monitoring).
     */
    public function getDeliveryDashboardData(Request $request)
    {
        try {
            $rows = $this->deliveryDashboardFilteredQuery($request)
                ->orderByDesc('delivery_date')
                ->limit(8000)
                ->get();

            $grouped = $rows->groupBy(function (LoadingList $ll) {
                return $ll->customer_id.'|'.Carbon::parse($ll->delivery_date)->format('Y-m-d');
            })->map(function ($loadingLists) {
                $first = $loadingLists->first();
                $cid = (int) $first->customer_id;
                $dateStr = Carbon::parse($first->delivery_date)->format('Y-m-d');
                $totalKanban = $loadingLists->sum('total_kanban');
                $actualKanban = $loadingLists->sum('actual_kanban');
                $rowId = 'cd-'.$cid.'-'.str_replace('-', '', $dateStr);

                return (object) [
                    'id' => $rowId,
                    'customer_id' => $cid,
                    'delivery_date_raw' => $dateStr,
                    'customer' => $first->customer,
                    'delivery_date' => $dateStr,
                    'total_kanban' => $totalKanban,
                    'actual_kanban' => $actualKanban,
                ];
            })->values();

            return DataTables::of($grouped)
                ->addColumn('customer', function ($group) {
                    return optional($group->customer)->name ?? '-';
                })
                ->addColumn('delivery_date', function ($group) {
                    return $group->delivery_date;
                })
                ->addColumn('progress', function ($group) {
                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;
                    $progressPercentage = ($totalKanban > 0) ? round(($actualKanban / $totalKanban) * 100) : 0;

                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusClass = 'lightgreen';
                    } elseif ($actualKanban > 0) {
                        $statusClass = 'orange';
                    } else {
                        $statusClass = 'red';
                    }

                    return '
                        <div class="text-small font-weight-bold text-muted mb-1 text-center">'
                            .$actualKanban.' / '.$totalKanban.
                        '</div>
                        <div class="progress" data-height="20" style="height: 18px;">
                            <div class="progress-bar" role="progressbar"
                                style="width:'.$progressPercentage.'%; background-color: '.$statusClass.' !important"
                                aria-valuenow="'.$progressPercentage.'" aria-valuemin="0" aria-valuemax="100">
                                <small class="text-white font-weight-bold">'.$progressPercentage.'%</small>
                            </div>
                        </div>';
                })
                ->addColumn('loading_and_status', function ($group) {
                    $buttonStyle = 'style="min-width: 120px; padding: 8px 12px; font-size: 13px; font-weight: 500; text-align: center; white-space: nowrap;"';

                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;

                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusButton = '<button type="button" class="btn btn-success" '.$buttonStyle.'>
                                            <i class="fas fa-check mr-1"></i>
                                            COMPLETE
                                        </button>';
                    } elseif ($actualKanban > 0) {
                        $statusButton = '<button type="button" class="btn btn-outline-warning" '.$buttonStyle.'>
                                            IN PROGRESS
                                        </button>';
                    } else {
                        $statusButton = '<button type="button" class="btn btn-outline-danger" '.$buttonStyle.'>
                                            INCOMPLETE
                                        </button>';
                    }

                    return '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; align-items: center;">'.$statusButton.'</div>';
                })
                ->setRowId(function ($group) {
                    return 'row-'.$group->id;
                })
                ->rawColumns(['loading_and_status', 'progress', 'customer'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Dashboard: progress per cycle (customer + delivery date), same data source as loading list monitoring.
     */
    public function dashboardDelivery()
    {
        return view('pages.dashboard_delivery', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'lastLoadingListSync' => Cache::get('dashboard_delivery_last_loading_list_sync'),
        ]);
    }

    public function syncDeliveryLoadingLists(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $dateFrom = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
        $dateTo = $dateFrom->copy()->addDays(2);

        $exitCode = Artisan::call('sync:loading-lists', [
            'date' => $validated['date'],
            '--days' => 3,
            '--web' => true,
        ]);
        $output = trim(Artisan::output());
        $success = $exitCode === 0;
        $result = $success ? json_decode($output, true) : null;
        $skippedItems = is_array($result) && is_array($result['skipped_items'] ?? null)
            ? $result['skipped_items']
            : [];
        $skipped = count($skippedItems);
        $loadingListCount = (int) ($result['loading_list_count'] ?? 0);
        $lastSync = null;

        if ($success) {
            $lastSync = [
                'date' => $validated['date'],
                'date_to' => $dateTo->toDateString(),
                'date_label' => $dateFrom->format('d/m/Y').'–'.$dateTo->format('d/m/Y'),
                'synced_at' => now()->format('d/m/Y H:i'),
                'loading_list_count' => $loadingListCount,
                'skipped' => $skipped,
            ];
            Cache::forever('dashboard_delivery_last_loading_list_sync', $lastSync);
        }

        return response()->json([
            'status' => ! $success
                ? 'error'
                : ($skipped > 0 ? 'warning' : ($loadingListCount === 0 ? 'empty' : 'success')),
            'message' => ! $success
                ? 'Sync gagal karena terjadi kendala teknis.'
                : ($skipped > 0
                    ? "Sync berhasil dengan catatan: {$skipped} data dilewati."
                    : ($loadingListCount === 0
                        ? 'Tidak ada loading list untuk rentang tanggal '.$dateFrom->toDateString().' s.d. '.$dateTo->toDateString().'.'
                        : 'Loading list tanggal '.$dateFrom->toDateString().' s.d. '.$dateTo->toDateString().' berhasil disinkronkan.')),
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'loading_list_count' => $loadingListCount,
            'skipped' => $skipped,
            'skipped_items' => $skippedItems,
            'last_sync' => $lastSync,
            'detail' => $success ? null : $output,
        ], $success ? 200 : 422);
    }

    public function getMasterCycles()
    {
        $rows = MasterCycle::query()
            ->with(['customer:id,name'])
            ->orderBy('time')
            ->orderBy('cycle_name')
            ->get()
            ->map(function (MasterCycle $m) {
                return $this->formatMasterCycleForApi($m);
            });

        return response()->json(['data' => $rows]);
    }

    public function getMasterCycleOptions(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $loadingLists = LoadingList::query()
            ->where('customer_id', $validated['customer_id'])
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get(['id', 'number', 'cycle', 'customer_id']);

        $cycles = $loadingLists
            ->pluck('cycle')
            ->filter(fn ($c) => $c !== null && $c !== '')
            ->map(fn ($c) => trim((string) $c))
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'loading_lists' => $loadingLists->map(function (LoadingList $ll) {
                return [
                    'id' => $ll->id,
                    'number' => $ll->number,
                    'cycle' => $ll->cycle,
                ];
            }),
            'cycles' => $cycles,
        ]);
    }

    public function storeMasterCycle(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'cycle_name' => 'required|string|max:120',
            'time' => 'required|date_format:H:i',
            'prep_end_time' => 'required|date_format:H:i',
            'prep_day_offset' => 'nullable|integer|min:-14|max:14',
            'truck_time' => 'required|date_format:H:i',
        ]);

        $validated['cycle_name'] = trim($validated['cycle_name']);
        $validated = $this->applyMasterCyclePrepOffsets($validated);
        $exists = MasterCycle::query()
            ->where('customer_id', $validated['customer_id'])
            ->whereRaw('LOWER(cycle_name) = ?', [Str::lower($validated['cycle_name'])])
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Kombinasi customer dan cycle sudah ada.',
            ], 422);
        }

        $row = MasterCycle::create($validated);

        return response()->json([
            'message' => 'Master cycle tersimpan.',
            'data' => $row->load(['customer:id,name']),
        ], 201);
    }

    public function updateMasterCycle(Request $request, MasterCycle $masterCycle)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'cycle_name' => 'required|string|max:120',
            'time' => 'required|date_format:H:i',
            'prep_end_time' => 'required|date_format:H:i',
            'prep_day_offset' => 'nullable|integer|min:-14|max:14',
            'truck_time' => 'required|date_format:H:i',
        ]);

        $validated['cycle_name'] = trim($validated['cycle_name']);
        $validated = $this->applyMasterCyclePrepOffsets($validated);
        $exists = MasterCycle::query()
            ->where('customer_id', $validated['customer_id'])
            ->whereRaw('LOWER(cycle_name) = ?', [Str::lower($validated['cycle_name'])])
            ->where('id', '!=', $masterCycle->id)
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Kombinasi customer dan cycle sudah ada.',
            ], 422);
        }

        $masterCycle->update($validated);

        return response()->json([
            'message' => 'Master cycle diperbarui.',
            'data' => $masterCycle->fresh(['customer:id,name']),
        ]);
    }

    public function destroyMasterCycle(MasterCycle $masterCycle)
    {
        $masterCycle->delete();

        return response()->json(['message' => 'Master cycle dihapus.']);
    }

    /**
     * Stacked bar chart: agregat loading list per customer + cycle.
     * Mapping cycle bersifat per-row berdasarkan:
     * 1) loading_lists.cycle ↔ master_cycles.cycle_name (customer yang sama),
     * 2) jika tidak cocok, tetap pakai label cycle dari loading list tanpa fallback waktu terdekat.
     * Filter tanggal menggunakan delivery_date agar sesuai tanggal kirim pada loading list.
     */
    public function getDeliveryStackedChartData(Request $request)
    {
        if ($request->input('date') === '') {
            $request->merge(['date' => null]);
        }
        if ($request->input('delivery_date') === '') {
            $request->merge(['delivery_date' => null]);
        }
        if ($request->input('date_from') === '') {
            $request->merge(['date_from' => null]);
        }
        if ($request->input('date_to') === '') {
            $request->merge(['date_to' => null]);
        }
        if ($request->input('customer_id') === '') {
            $request->merge(['customer_id' => null]);
        }
        if ($request->input('customer') === '') {
            $request->merge(['customer' => null]);
        }
        if ($request->input('cycle') === '') {
            $request->merge(['cycle' => null]);
        }

        $validated = $request->validate([
            'date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'customer' => 'nullable|string',
            'cycle' => 'nullable|string',
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $dateFilter = $validated['delivery_date'] ?? ($validated['date'] ?? null);

        // Backward compatible: jika range kosong, fallback ke filter single-date lama.
        if (empty($dateFrom) && empty($dateTo) && ! empty($dateFilter)) {
            $dateFrom = $dateFilter;
            $dateTo = $dateFilter;
        }

        $start = ! empty($dateFrom) ? Carbon::parse($dateFrom)->startOfDay() : null;
        $end = ! empty($dateTo) ? Carbon::parse($dateTo)->endOfDay() : null;

        // Jika user terbalik isi from/to, tukar agar tetap valid sebagai rentang.
        if ($start && $end && $start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $customerName = $request->filled('customer')
            ? trim((string) $request->get('customer'))
            : null;
        $cycleRaw = $request->filled('cycle')
            ? trim((string) $request->get('cycle'))
            : null;
        $cycleNumber = null;
        if ($cycleRaw !== null && preg_match('/\d+/', $cycleRaw, $m)) {
            $cycleNumber = $m[0];
        }

        $applyFilters = function ($query) use ($validated, $customerName, $cycleRaw, $cycleNumber, $start, $end) {
            if ($customerName !== null) {
                $query->whereHas('customer', function ($q) use ($customerName) {
                    $q->where('name', $customerName);
                });
            }

            if (! empty($validated['customer_id'] ?? null)) {
                $query->where('customer_id', (int) $validated['customer_id']);
            }

            if ($cycleRaw !== null) {
                $query->where(function ($q) use ($cycleRaw, $cycleNumber) {
                    $q->where('cycle', $cycleRaw);
                    if ($cycleNumber !== null) {
                        $q->orWhere('cycle', $cycleNumber)
                            ->orWhere('cycle', 'Cycle '.$cycleNumber)
                            ->orWhere('cycle', 'cycle '.$cycleNumber);
                    }
                });
            }

            if ($start && $end) {
                $query->whereBetween('delivery_date', [$start, $end]);
            } elseif ($start) {
                $query->where('delivery_date', '>=', $start);
            } elseif ($end) {
                $query->where('delivery_date', '<=', $end);
            }

            return $query;
        };

        $pisCustomerIds = Customer::query()
            ->whereIn('name', PisScan::DELIVERY_CUSTOMER_NAMES)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        // Lima customer SPD memakai PIS sebagai sumber eksklusif agar tidak terhitung ganda.
        $baseQuery = LoadingList::with(['customer'])
            ->withSum('detail as total_kanban', 'kanban_qty')
            ->withSum('detail as actual_kanban', 'actual_kanban_qty')
            ->whereNotNull('customer_id')
            ->when(! empty($pisCustomerIds), function ($query) use ($pisCustomerIds) {
                $query->whereNotIn('customer_id', $pisCustomerIds);
            })
            ->latest();
        $applyFilters($baseQuery);

        $pisQuery = PisScan::with(['customer'])
            ->withSum('details as total_kanban', 'target_qty')
            ->withSum('details as actual_kanban', 'scanned_qty')
            ->whereNotNull('customer_id')
            ->latest();

        if (empty($pisCustomerIds)) {
            $pisQuery->whereRaw('1 = 0');
        } else {
            $pisQuery->whereIn('customer_id', $pisCustomerIds);
        }
        $applyFilters($pisQuery);

        $hasSpecificFilter = $request->filled('customer')
            || ! empty($validated['customer_id'] ?? null)
            || $request->filled('cycle')
            || ! empty($dateFrom)
            || ! empty($dateTo)
            || ! empty($dateFilter);

        // Saat mode "Semua", jangan dibatasi agar hasil konsisten dengan data aktual.
        $rowLimit = $hasSpecificFilter ? 50000 : null;

        $loadingLists = (clone $baseQuery)
            ->when($rowLimit !== null, function ($q) use ($rowLimit) {
                $q->take($rowLimit);
            })
            ->get()
            ->each(function (LoadingList $loadingList) {
                $loadingList->setAttribute('delivery_source', 'loading_list');
                $loadingList->setAttribute('latest_pulling_date', null);
            });

        $pisLists = (clone $pisQuery)
            ->when($rowLimit !== null, function ($q) use ($rowLimit) {
                $q->take($rowLimit);
            })
            ->get();

        $latestPisScanById = collect();
        $pisScanIds = $pisLists->pluck('id')->filter()->unique()->values();
        if ($pisScanIds->isNotEmpty()) {
            $latestPisScanById = DB::table('pis_scan_details')
                ->whereIn('pis_scan_id', $pisScanIds->all())
                ->where('scanned_qty', '>', 0)
                ->select('pis_scan_id', DB::raw('MAX(updated_at) as latest_scan_date'))
                ->groupBy('pis_scan_id')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [(int) $row->pis_scan_id => (string) $row->latest_scan_date];
                });
        }

        $pisLists->each(function (PisScan $pisScan) use ($latestPisScanById) {
            $pisScan->setAttribute('delivery_source', 'pis');
            $pisScan->setAttribute('number', $pisScan->loading_list_number);
            $pisScan->setAttribute('truck_status', 'pending');
            $pisScan->setAttribute('truck_picked_at', null);
            $pisScan->setAttribute('latest_pulling_date', $latestPisScanById->get((int) $pisScan->id));
        });

        $rawLists = $loadingLists
            ->concat($pisLists)
            ->sortByDesc('created_at')
            ->values();

        if ($rowLimit !== null) {
            $rawLists = $rawLists->take($rowLimit)->values();
        }

        $scopedPullingByLoadingListId = collect();
        $loadingListIds = $rawLists
            ->where('delivery_source', 'loading_list')
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();
        if ($loadingListIds->isNotEmpty()) {
            // Precise: latest pulling dari tanggal checkout mutation via loading_list_detail_id.
            $linkedPulling = DB::table('mutations as m')
                ->join('loading_list_details as lld', 'lld.id', '=', 'm.loading_list_detail_id')
                ->whereIn('lld.loading_list_id', $loadingListIds->all())
                ->where('m.type', 'checkout')
                ->groupBy('lld.loading_list_id')
                ->select('lld.loading_list_id', DB::raw('MAX(COALESCE(m.date, m.created_at)) as latest_pulling_date'))
                ->get()
                ->mapWithKeys(function ($row) {
                    return [(int) $row->loading_list_id => (string) $row->latest_pulling_date];
                });

            // Fallback data lama (mutation belum punya linkage): proxy MAX(lld.updated_at).
            $fallbackPulling = DB::table('loading_list_details as lld')
                ->whereIn('lld.loading_list_id', $loadingListIds->all())
                ->where('lld.actual_kanban_qty', '>', 0)
                ->whereColumn('lld.updated_at', '>', 'lld.created_at')
                ->groupBy('lld.loading_list_id')
                ->select('lld.loading_list_id', DB::raw('MAX(lld.updated_at) as latest_pulling_date'))
                ->get()
                ->mapWithKeys(function ($row) {
                    return [(int) $row->loading_list_id => (string) $row->latest_pulling_date];
                });

            // Linkage presisi menang; LL tanpa linkage tetap pakai proxy lama.
            $scopedPullingByLoadingListId = $fallbackPulling->merge($linkedPulling);
        }

        $pullingByPds = collect();
        $pdsNumbers = $rawLists->pluck('pds_number')->filter()->unique()->values();
        if ($pdsNumbers->isNotEmpty()) {
            $pullingByPds = DB::table('pullings')
                ->select('customer_id', 'pds_number', 'loading_list', DB::raw('MAX(pulling_date) as latest_pulling_date'))
                ->whereIn('pds_number', $pdsNumbers->all())
                ->whereNotNull('pulling_date')
                ->groupBy('customer_id', 'pds_number', 'loading_list')
                ->get()
                ->groupBy(function ($row) {
                    return ((int) $row->customer_id).'|'.(string) $row->pds_number;
                });
        }

        $groupedData = $rawLists
            ->groupBy('pds_number')
            ->map(function ($loadingLists, $pdsNumber) use ($pullingByPds, $scopedPullingByLoadingListId) {
                /** @var LoadingList $firstLoadingList */
                $firstLoadingList = $loadingLists->first();
                $totalKanban = (int) $loadingLists->sum('total_kanban');
                $actualKanban = (int) $loadingLists->sum('actual_kanban');
                $cycle = $loadingLists->groupBy('cycle')->sortByDesc(function ($items) {
                    return $items->count();
                })->keys()->first();
                $scopedPullingItems = $loadingLists
                    ->map(function ($loadingList) use ($pdsNumber, $scopedPullingByLoadingListId) {
                        $latest = $loadingList->delivery_source === 'pis'
                            ? $loadingList->latest_pulling_date
                            : $scopedPullingByLoadingListId->get((int) $loadingList->id);
                        if (! $latest) {
                            return null;
                        }

                        return [
                            'pds_number' => (string) $pdsNumber,
                            'loading_list' => $loadingList->number ? (string) $loadingList->number : null,
                            'pulling_date' => Carbon::parse($latest)->toIso8601String(),
                        ];
                    })
                    ->filter()
                    ->values();
                $scopedLoadingLists = $scopedPullingItems
                    ->pluck('loading_list')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $pullingKey = ((int) $firstLoadingList->customer_id).'|'.(string) $pdsNumber;
                $fallbackPullingItems = $loadingLists->contains('delivery_source', 'pis')
                    ? collect()
                    : $pullingByPds->get($pullingKey, collect())
                        ->filter(function ($row) use ($scopedPullingItems, $scopedLoadingLists) {
                            if (! $row->loading_list) {
                                return $scopedPullingItems->isEmpty();
                            }

                            return ! in_array((string) $row->loading_list, $scopedLoadingLists, true);
                        })
                        ->map(function ($row) use ($pdsNumber) {
                            return [
                                'pds_number' => (string) $pdsNumber,
                                'loading_list' => $row->loading_list ? (string) $row->loading_list : null,
                                'pulling_date' => Carbon::parse($row->latest_pulling_date)->toIso8601String(),
                            ];
                        })
                        ->values();
                $pullingItems = $scopedPullingItems->concat($fallbackPullingItems)->values();
                $latestPullingDate = $pullingItems->pluck('pulling_date')->max();

                return (object) [
                    'id' => 'pds-'.$pdsNumber,
                    'pds_number' => $pdsNumber,
                    'customer' => $firstLoadingList->customer,
                    'customer_id' => (int) $firstLoadingList->customer_id,
                    'cycle' => $this->normalizeCycleNumber($cycle),
                    'delivery_date' => $loadingLists->min('delivery_date'),
                    'total_kanban' => $totalKanban,
                    'actual_kanban' => $actualKanban,
                    'latest_pulling_date' => $latestPullingDate,
                    'pulling_items' => $pullingItems->all(),
                    'is_complete' => $totalKanban > 0 && $actualKanban >= $totalKanban,
                ];
            })
            ->values();

        $masters = MasterCycle::query()
            ->with(['customer:id,name'])
            ->when(! empty($validated['customer_id'] ?? null), function ($q) use ($validated) {
                $q->where('customer_id', (int) $validated['customer_id']);
            })
            ->orderBy('time')
            ->orderBy('cycle_name')
            ->get();

        $masterMeta = $masters->map(function (MasterCycle $m) {
            return $this->formatMasterCycleMetaForChart($m);
        });

        $rows = $groupedData
            ->groupBy(function ($pds) {
                $date = $pds->delivery_date
                    ? Carbon::parse($pds->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');

                return $pds->customer_id.'|'.$date.'|'.$pds->cycle;
            })
            ->map(function ($pdsGroup) use ($masterMeta, $rawLists) {
                $first = $pdsGroup->first();
                $dateStr = $first->delivery_date
                    ? Carbon::parse($first->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');

                $bucketLls = $rawLists->filter(function ($ll) use ($first, $dateStr) {
                    $llDate = $ll->delivery_date ? Carbon::parse($ll->delivery_date)->format('Y-m-d') : null;
                    return (int) $ll->customer_id === (int) $first->customer_id
                        && $this->normalizeCycleNumber($ll->cycle) === (string) $first->cycle
                        && $llDate === $dateStr;
                });

                $totalLlsInBucket = $bucketLls->count();
                $pickedLlsInBucket = $bucketLls->where('truck_status', 'picked')->count();
                $bucketPickedAt = $bucketLls->max('truck_picked_at');
                $pullingItems = $pdsGroup->flatMap(function ($pds) {
                    return $pds->pulling_items ?? [];
                })->values();
                $latestPullingDate = $pullingItems->pluck('pulling_date')->max();
                $pdsNumbers = $pdsGroup->pluck('pds_number')->filter()->unique()->values();
                $loadingListNumbers = $bucketLls->pluck('number')->filter()->unique()->values();

                if ($pickedLlsInBucket === 0) {
                    $truckStatus = 'pending';
                } elseif ($pickedLlsInBucket >= $totalLlsInBucket) {
                    $truckStatus = 'picked';
                } else {
                    $truckStatus = 'partial';
                }

                $master = $this->findMasterCycleForDeliveryBucket(
                    (int) $first->customer_id,
                    (string) $first->cycle,
                    $masterMeta
                );
                $hasMaster = $this->deliveryChartRowHasConfiguredMasterCycle($master);

                $llCount = $bucketLls->count();
                $completeCount = $pdsGroup->where('is_complete', true)->count();
                $totalTarget = (int) $pdsGroup->sum('total_kanban');
                $totalDone = (int) $pdsGroup->sum('actual_kanban');
                // Progress bar = kanban (sama dengan tampilan loading list & weekly chart)
                $progressPct = $totalTarget > 0
                    ? round(($totalDone / $totalTarget) * 100, 1)
                    : ($llCount > 0 ? round(($completeCount / $llCount) * 100, 1) : 0.0);

                $cycleTime = $hasMaster ? $master['time'] : null;
                $prepEndTime = $hasMaster ? $master['prep_end_time'] : null;
                $truckTime = ($hasMaster && isset($master['truck_time']) && $master['truck_time'] !== null)
                    ? $master['truck_time']
                    : null;
                $prepDayOffset = 0;
                $prepEndDayOffset = 0;
                if ($hasMaster) {
                    $prepDayOffset = $this->normalizePrepDayOffset((int) ($master['prep_day_offset'] ?? 0));
                    $prepEndDayOffset = $this->computePrepEndDayOffset(
                        $prepDayOffset,
                        (string) ($master['time'] ?? ''),
                        (string) ($master['prep_end_time'] ?? '')
                    );
                }

                $rowPayload = [
                    'customer_id' => (int) $first->customer_id,
                    'customer_name' => optional($first->customer)->name ?? '-',
                    'delivery_date' => $dateStr,
                    'master_cycle_id' => $hasMaster ? ($master['id'] ?? null) : null,
                    'master_cycle_configured' => $hasMaster,
                    'cycle_name' => (string) $first->cycle,
                    'cycle_time' => $cycleTime,
                    'prep_end_time' => $prepEndTime,
                    'prep_day_offset' => $prepDayOffset,
                    'prep_end_day_offset' => $prepEndDayOffset,
                    'prep_day_offset_label' => $hasMaster ? ($master['prep_day_offset_label'] ?? $this->formatPrepDayOffsetLabel($prepDayOffset)) : null,
                    'prep_end_day_offset_label' => $hasMaster ? ($master['prep_end_day_offset_label'] ?? $this->formatPrepDayOffsetLabel($prepEndDayOffset)) : null,
                    'prep_crosses_midnight' => $hasMaster ? ($master['prep_crosses_midnight'] ?? false) : false,
                    'truck_time' => $truckTime,
                    'mapping_source' => $hasMaster ? 'master_cycle_name' : 'loading_list_only',
                    'on_time' => $completeCount,
                    'delay' => $llCount - $completeCount,
                    'no_order' => $pdsGroup->filter(function ($p) {
                        return (int) $p->total_kanban === 0;
                    })->count(),
                    'total_target' => $totalTarget,
                    'total_done' => $totalDone,
                    'll_count' => $llCount,
                    'progress_pct_raw' => $progressPct,
                    'progress_pct' => min(100.0, max(0.0, $progressPct)),
                    'truck_status' => $truckStatus,
                    'truck_picked_at' => $bucketPickedAt ? Carbon::parse($bucketPickedAt)->toIso8601String() : null,
                    'latest_pulling_date' => $latestPullingDate ? Carbon::parse($latestPullingDate)->toIso8601String() : null,
                    'pulling_items' => $pullingItems->all(),
                    'pds_numbers' => $pdsNumbers->all(),
                    'loading_lists' => $loadingListNumbers->all(),
                ];

                return $this->appendPrepAbsoluteDatetimesToChartRow($rowPayload);
            })
            ->sortBy([
                ['customer_name', 'asc'],
                ['absolute_start_datetime', 'asc'],
            ])
            ->values()
            ->all();

        $weeklyPoints = $groupedData
            ->groupBy(function ($pds) {
                $date = $pds->delivery_date
                    ? Carbon::parse($pds->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');

                return $pds->customer_id.'|'.$date.'|'.$pds->cycle;
            })
            ->map(function ($bucket) use ($masterMeta, $rawLists) {
                $first = $bucket->first();
                $dateStr = $first->delivery_date
                    ? Carbon::parse($first->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');

                $bucketLls = $rawLists->filter(function ($ll) use ($first, $dateStr) {
                    $llDate = $ll->delivery_date ? Carbon::parse($ll->delivery_date)->format('Y-m-d') : null;
                    return (int) $ll->customer_id === (int) $first->customer_id
                        && $this->normalizeCycleNumber($ll->cycle) === (string) $first->cycle
                        && $llDate === $dateStr;
                });

                $totalLlsInBucket = $bucketLls->count();
                $pickedLlsInBucket = $bucketLls->where('truck_status', 'picked')->count();
                $bucketPickedAt = $bucketLls->max('truck_picked_at');
                $pullingItems = $bucket->flatMap(function ($pds) {
                    return $pds->pulling_items ?? [];
                })->values();
                $latestPullingDate = $pullingItems->pluck('pulling_date')->max();
                $pdsNumbers = $bucket->pluck('pds_number')->filter()->unique()->values();
                $loadingListNumbers = $bucketLls->pluck('number')->filter()->unique()->values();

                if ($pickedLlsInBucket === 0) {
                    $truckStatus = 'pending';
                } elseif ($pickedLlsInBucket >= $totalLlsInBucket) {
                    $truckStatus = 'picked';
                } else {
                    $truckStatus = 'partial';
                }

                $master = $this->findMasterCycleForDeliveryBucket(
                    (int) $first->customer_id,
                    (string) $first->cycle,
                    $masterMeta
                );
                $hasMaster = $this->deliveryChartRowHasConfiguredMasterCycle($master);

                $target = (int) $bucket->sum('total_kanban');
                $done = (int) $bucket->sum('actual_kanban');
                $progress = $target > 0 ? round(($done / $target) * 100, 1) : 0.0;

                if ($hasMaster) {
                    $prepStart = $master['time'];
                    $prepEnd = $master['prep_end_time'];
                    $truckTime = $master['truck_time'] ?? null;
                    $prepDayOffset = $this->normalizePrepDayOffset((int) ($master['prep_day_offset'] ?? 0));
                    $prepEndDayOffset = $this->computePrepEndDayOffset(
                        $prepDayOffset,
                        (string) ($master['time'] ?? ''),
                        (string) ($master['prep_end_time'] ?? '')
                    );
                    $start = Carbon::parse(
                        Carbon::parse($dateStr)->addDays($prepDayOffset)->format('Y-m-d').' '.$prepStart
                    );
                    $end = $prepEnd
                        ? Carbon::parse(
                            Carbon::parse($dateStr)->addDays($prepEndDayOffset)->format('Y-m-d').' '.$prepEnd
                        )
                        : $start->copy()->addMinutes(45);
                    $startAt = $start->toIso8601String();
                    $endAt = $end->toIso8601String();
                } else {
                    $prepStart = null;
                    $prepEnd = null;
                    $truckTime = null;
                    $prepDayOffset = 0;
                    $prepEndDayOffset = 0;
                    $startAt = null;
                    $endAt = null;
                }

                return [
                    'customer_id' => (int) $first->customer_id,
                    'customer_name' => optional($first->customer)->name ?? '-',
                    'delivery_date' => $dateStr,
                    'cycle_name' => (string) $first->cycle,
                    'master_cycle_configured' => $hasMaster,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'absolute_start_datetime' => $startAt,
                    'absolute_end_datetime' => $endAt,
                    'prep_time' => $prepStart,
                    'prep_end_time' => $prepEnd,
                    'prep_day_offset' => $prepDayOffset,
                    'prep_end_day_offset' => $prepEndDayOffset,
                    'prep_day_offset_label' => $hasMaster ? ($master['prep_day_offset_label'] ?? $this->formatPrepDayOffsetLabel($prepDayOffset)) : null,
                    'prep_end_day_offset_label' => $hasMaster ? ($master['prep_end_day_offset_label'] ?? $this->formatPrepDayOffsetLabel($prepEndDayOffset)) : null,
                    'prep_crosses_midnight' => $hasMaster ? ($master['prep_crosses_midnight'] ?? false) : false,
                    'truck_time' => $truckTime,
                    'total_target' => $target,
                    'total_done' => $done,
                    'll_count' => $bucketLls->count(),
                    'progress_pct' => min(100.0, max(0.0, $progress)),
                    'is_complete' => $target > 0 && $done >= $target,
                    'truck_status' => $truckStatus,
                    'truck_picked_at' => $bucketPickedAt ? Carbon::parse($bucketPickedAt)->toIso8601String() : null,
                    'latest_pulling_date' => $latestPullingDate ? Carbon::parse($latestPullingDate)->toIso8601String() : null,
                    'pulling_items' => $pullingItems->all(),
                    'pds_numbers' => $pdsNumbers->all(),
                    'loading_lists' => $loadingListNumbers->all(),
                ];
            })
            ->sortBy([
                ['customer_name', 'asc'],
                ['absolute_start_datetime', 'asc'],
            ])
            ->values()
            ->all();

        $configuredBucketCount = collect($rows)->where('master_cycle_configured', true)->count();
        $totalBuckets = count($rows);

        $hint = null;
        if ($groupedData->isEmpty()) {
            $hint = 'Tidak ada loading list untuk filter ini. Samakan filter dengan halaman loading list.';
        } elseif ($totalBuckets > 0 && $configuredBucketCount === 0) {
            $hint = 'Ada data loading list, tetapi semua cycle belum dikonfigurasi di Master Cycle. Customer tetap ditampilkan; atur waktu Preparation di tab Master Cycle untuk menampilkan bar timeline.';
        }

        return response()->json([
            'rows' => $rows,
            'weekly_points' => $weeklyPoints,
            'master_cycles' => $masters->map(function (MasterCycle $m) {
                return $this->formatMasterCycleForApi($m);
            }),
            'meta' => [
                'total_loading_lists' => $groupedData->count(),
                'total_buckets' => count($rows),
                'master_cycle_count' => $masters->count(),
                'date_filter' => $dateFilter,
                'row_limit' => $rowLimit,
                'raw_rows_loaded' => $rawLists->count(),
                'pds_grouped_count' => $groupedData->count(),
                'customer_cycle_bucket_count' => count($rows),
                'unconfigured_cycle_bucket_count' => max(0, $totalBuckets - $configuredBucketCount),
                'configured_cycle_bucket_count' => $configuredBucketCount,
                'is_truncated' => $rowLimit !== null ? ($rawLists->count() >= $rowLimit) : false,
                'hint' => $hint,
            ],
        ]);
    }

    /**
     * Dipanggil scheduler (send:delivery-unfinished-notification) agar WA tetap terkirim tanpa dashboard dibuka.
     *
     * @return array{exit_code: int, message: string, detail?: string}
     */
    public function runScheduledDeliveryUnfinishedWaNotification(): array
    {
        $today = Carbon::now()->format('Y-m-d');
        $finishOffsetHours = (int) env('DELIVERY_FINISH_OFFSET_HOURS', 4);
        if ($finishOffsetHours < -24) {
            $finishOffsetHours = -24;
        }
        if ($finishOffsetHours > 24) {
            $finishOffsetHours = 24;
        }

        $chartRequest = Request::create('/delivery/stacked-chart', 'GET', [
            'date_from' => $today,
            'date_to' => $today,
        ]);
        $chartPayload = json_decode(
            $this->getDeliveryStackedChartData($chartRequest)->getContent(),
            true
        );
        $chartRows = is_array($chartPayload) ? ($chartPayload['rows'] ?? []) : [];

        $mergedRows = $this->mergeDeliveryChartRowsByCustomerTime($chartRows);
        $pendingRows = [];
        foreach ($mergedRows as $row) {
            $progress = (float) ($row['progress_pct'] ?? 0);
            if ($progress >= 100) {
                continue;
            }
            $cycleTime = trim((string) ($row['cycle_time'] ?? ''));
            if ($cycleTime === '') {
                continue;
            }
            if (! $this->isDeliveryRowPastFinishPrep($row, $finishOffsetHours)) {
                continue;
            }
            $pendingRows[] = [
                'customer_id' => (int) ($row['customer_id'] ?? 0),
                'customer_name' => (string) ($row['customer_name'] ?? '-'),
                'cycle_name' => (string) ($row['cycle_name'] ?? '-'),
                'cycle_time' => $cycleTime,
                'prep_end_time' => $row['prep_end_time'] ?? null,
                'progress_pct' => round($progress, 1),
                'total_done' => (int) ($row['total_done'] ?? 0),
                'total_target' => (int) ($row['total_target'] ?? 0),
                'll_count' => (int) ($row['ll_count'] ?? 0),
            ];
        }

        if ($pendingRows === []) {
            return [
                'exit_code' => 0,
                'message' => 'Tidak ada bar delay yang perlu dikirim ('.$today.').',
            ];
        }

        $result = $this->dispatchDeliveryUnfinishedWaNotification([
            'date_from' => $today,
            'date_to' => $today,
            'finish_offset_hours' => $finishOffsetHours,
            'pending_rows' => $pendingRows,
        ]);

        $detail = null;
        if (isset($result['data']['already_sent']) && $result['data']['already_sent']) {
            $detail = 'Dedupe cache aktif — notifikasi untuk snapshot yang sama sudah pernah dikirim.';
        }

        return [
            'exit_code' => ($result['status'] ?? 200) >= 400 ? 1 : 0,
            'message' => (string) ($result['data']['message'] ?? 'Selesai.'),
            'detail' => $detail,
        ];
    }

    public function sendDeliveryUnfinishedWaNotification(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|string|max:20',
            'date_to' => 'nullable|string|max:20',
            'finish_offset_hours' => 'nullable|integer|min:-24|max:24',
            'pending_rows' => 'required|array|min:1',
            'pending_rows.*.customer_id' => 'nullable|integer|min:1',
            'pending_rows.*.customer_name' => 'required|string|max:255',
            'pending_rows.*.cycle_name' => 'required|string|max:100',
            'pending_rows.*.cycle_time' => 'nullable|string|max:20',
            'pending_rows.*.prep_end_time' => 'nullable|string|max:20',
            'pending_rows.*.progress_pct' => 'required|numeric|min:0|max:100',
            'pending_rows.*.total_done' => 'nullable|numeric|min:0',
            'pending_rows.*.total_target' => 'nullable|numeric|min:0',
            'pending_rows.*.ll_count' => 'nullable|integer|min:0',
        ]);

        $result = $this->dispatchDeliveryUnfinishedWaNotification($validated);

        return response()->json($result['data'], $result['status']);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{status: int, data: array<string, mixed>}
     */
    private function dispatchDeliveryUnfinishedWaNotification(array $validated): array
    {
        $toTimelineMinutes = function (?string $clock): ?int {
            $raw = trim((string) ($clock ?? ''));
            if ($raw === '' || ! preg_match('/^\d{1,2}:\d{2}/', $raw)) {
                return null;
            }
            [$h, $m] = array_pad(explode(':', substr($raw, 0, 5)), 2, '0');
            $hour = (int) $h;
            $minute = (int) $m;
            if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                return null;
            }
            $total = ($hour * 60) + $minute;
            $timelineStart = 6 * 60; // 06:00
            if ($total < $timelineStart) {
                $total += 24 * 60;
            }

            return $total;
        };

        $finishOffsetHours = (int) ($validated['finish_offset_hours'] ?? 4);
        $finishMarker = Carbon::now()->addHours($finishOffsetHours)->format('H:i');
        $finishMarkerMinutes = $toTimelineMinutes($finishMarker);

        $pendingRows = collect($validated['pending_rows'])
            ->filter(function (array $row) use ($toTimelineMinutes, $finishMarkerMinutes) {
                $progress = (float) ($row['progress_pct'] ?? 0);
                if ($progress >= 100) {
                    return false;
                }

                if ($finishMarkerMinutes === null) {
                    return false;
                }

                $prepStart = isset($row['cycle_time']) ? trim((string) $row['cycle_time']) : '';
                $prepEnd = isset($row['prep_end_time']) ? trim((string) $row['prep_end_time']) : '';
                $barEndClock = $prepEnd !== '' ? $prepEnd : $prepStart;
                $barEndMinutes = $toTimelineMinutes($barEndClock);
                if ($barEndMinutes === null) {
                    return false;
                }

                // Kirim WA hanya jika ujung kanan bar PREP (masih delay) sudah lewat marker Finish Prep.
                return $finishMarkerMinutes >= $barEndMinutes;
            })
            ->take(30)
            ->values();

        if ($pendingRows->isEmpty()) {
            return [
                'status' => 200,
                'data' => [
                    'message' => 'Belum ada bar delay yang melewati garis Finish Preparation.',
                    'already_sent' => false,
                ],
            ];
        }

        $hashPayload = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'finish_offset_hours' => $finishOffsetHours,
            'rows' => $pendingRows->toArray(),
        ];
        $dedupeKey = 'delivery_wa_unfinished_'.md5(json_encode($hashPayload));
        if (Cache::has($dedupeKey)) {
            return [
                'status' => 200,
                'data' => [
                    'message' => 'Notifikasi untuk data yang sama sudah pernah dikirim.',
                    'already_sent' => true,
                ],
            ];
        }

        $token = env('FASTWA_API_KEY', '793D30579A77D4A0E12648872BFBB085');
        // Grup WA: PPIC DELIVERY BODY ALERT (id 120363425808222838)
        $phone = env('FASTWA_PHONE', '120363425808222838@g.us');
        $url = env('FASTWA_SEND_URL', 'https://app.fastwa.com/api/v1/4D9AF7CE224B91C9CE14FFDDB55D248D/send_text');

        $period = trim((string) ($validated['date_from'] ?? ''));
        if (! empty($validated['date_to']) && $validated['date_to'] !== $period) {
            $period .= ' s/d '.trim((string) $validated['date_to']);
        }
        if ($period === '') {
            $period = Carbon::now()->format('Y-m-d');
        }

        $lines = [];
        $lines[] = 'Peringatan Dashboard Delivery';
        $lines[] = 'Periode: '.$period;
        $lines[] = 'Status: Melewati waktu Finish Preparation, progress masih < 100%';
        $lines[] = '';
        foreach ($pendingRows as $idx => $row) {
            $lines[] = ($idx + 1).'. '.$row['customer_name'].' | Cycle '.$row['cycle_name'];
            if (! empty($row['cycle_time'])) {
                $lines[] = '   Time: '.$row['cycle_time'];
            }
            $lines[] = '   Progress: '.$row['progress_pct'].'% ('.$row['total_done'].' / '.$row['total_target'].')';
            $lines[] = '   Jumlah LL: '.$row['ll_count'];

            $partShortages = $this->resolveDeliveryPartShortages(
                $row,
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null
            );
            if (! empty($partShortages)) {
                $lines[] = '   Detail Part Kurang:';
                foreach ($partShortages as $part) {
                    $lines[] = '   - part_no: '.$part['part_no'].' | qty_kurang: '.$part['qty_kurang'];
                }
            }
        }
        $message = implode("\n", $lines);

        $postFields = http_build_query([
            'api_key' => $token,
            'phone' => $phone,
            'message' => $message,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
        ]);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (! empty($curlError)) {
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Gagal kirim notifikasi WhatsApp.',
                    'error' => $curlError,
                ],
            ];
        }

        if ($statusCode >= 400) {
            return [
                'status' => 502,
                'data' => [
                    'message' => 'FastWA mengembalikan error.',
                    'status_code' => $statusCode,
                    'response' => $response,
                ],
            ];
        }

        Cache::put($dedupeKey, true, now()->addMinutes(30));

        return [
            'status' => 200,
            'data' => [
                'message' => 'Notifikasi WhatsApp terkirim.',
                'status_code' => $statusCode,
                'response' => $response,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function mergeDeliveryChartRowsByCustomerTime(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $cycleKey = $this->normalizeCycleNumber($row['cycle_name'] ?? '');
            $key = (int) ($row['customer_id'] ?? 0).'|'.$cycleKey.'|'.trim((string) ($row['cycle_time'] ?? ''));
            if (! isset($map[$key])) {
                $map[$key] = $row;
                $map[$key]['total_done'] = 0;
                $map[$key]['total_target'] = 0;
                $map[$key]['ll_count'] = 0;
            }
            $map[$key]['total_done'] += (int) ($row['total_done'] ?? 0);
            $map[$key]['total_target'] += (int) ($row['total_target'] ?? 0);
            $map[$key]['ll_count'] += (int) ($row['ll_count'] ?? 0);
        }

        foreach ($map as &$merged) {
            $target = (int) ($merged['total_target'] ?? 0);
            $done = (int) ($merged['total_done'] ?? 0);
            $merged['progress_pct'] = $target > 0 ? round(($done / $target) * 100, 1) : 0.0;
        }
        unset($merged);

        return array_values($map);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isDeliveryRowPastFinishPrep(array $row, int $finishOffsetHours): bool
    {
        $finishClock = $this->getDeliveryRowFinishPrepClock($row, $finishOffsetHours);
        if ($finishClock === null) {
            return false;
        }

        $currentFrac = $this->deliveryClockToTimelineFrac(Carbon::now()->format('H:i'));
        $finishFrac = $this->deliveryClockToTimelineFrac($finishClock);

        return $currentFrac !== null && $finishFrac !== null && $currentFrac >= $finishFrac;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function getDeliveryRowFinishPrepClock(array $row, int $finishOffsetHours): ?string
    {
        $prepEnd = trim((string) ($row['prep_end_time'] ?? ''));
        if ($prepEnd !== '') {
            return substr($prepEnd, 0, 5);
        }

        $cycleTime = trim((string) ($row['cycle_time'] ?? ''));
        if ($cycleTime === '') {
            return null;
        }

        return Carbon::parse('2000-01-01 '.$cycleTime)
            ->addHours($finishOffsetHours)
            ->format('H:i');
    }

    private function deliveryClockToTimelineFrac(string $clock): ?float
    {
        $raw = trim($clock);
        if ($raw === '' || ! preg_match('/^\d{1,2}:\d{2}/', $raw)) {
            return null;
        }

        [$h, $m] = array_pad(explode(':', substr($raw, 0, 5)), 2, '0');
        $hour = (int) $h;
        $minute = (int) $m;
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }

        $timelineStart = 6;
        $value = $hour + ($minute / 60);
        if ($value < $timelineStart) {
            $value += 24;
        }

        return $value - $timelineStart;
    }

    /**
     * Ambil top part yang masih kurang untuk bucket customer+cycle pada periode filter.
     *
     * @param  array<string, mixed>  $row
     * @return array<int, array{part_no: string, qty_kurang: int}>
     */
    private function resolveDeliveryPartShortages(array $row, ?string $dateFrom, ?string $dateTo): array
    {
        $customerId = isset($row['customer_id']) ? (int) $row['customer_id'] : 0;
        if ($customerId <= 0) {
            return [];
        }

        $cycleRaw = trim((string) ($row['cycle_name'] ?? ''));
        if ($cycleRaw === '') {
            return [];
        }

        $cycleNumber = null;
        if (preg_match('/\d+/', $cycleRaw, $m)) {
            $cycleNumber = (string) ((int) $m[0]);
        }

        $query = DB::table('loading_lists as ll')
            ->join('loading_list_details as lld', 'lld.loading_list_id', '=', 'll.id')
            ->leftJoin('customer_parts as cp', 'cp.id', '=', 'lld.customer_part_id')
            ->where('ll.customer_id', $customerId)
            ->where(function ($q) use ($cycleRaw, $cycleNumber) {
                $q->where('ll.cycle', $cycleRaw);
                if ($cycleNumber !== null) {
                    $q->orWhere('ll.cycle', $cycleNumber)
                        ->orWhere('ll.cycle', 'Cycle '.$cycleNumber)
                        ->orWhere('ll.cycle', 'cycle '.$cycleNumber);
                }
            });

        if (! empty($dateFrom) || ! empty($dateTo)) {
            $start = ! empty($dateFrom) ? Carbon::parse($dateFrom)->startOfDay() : null;
            $end = ! empty($dateTo) ? Carbon::parse($dateTo)->endOfDay() : null;
            if ($start && $end && $start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            if ($start && $end) {
                $query->whereBetween('ll.delivery_date', [$start, $end]);
            } elseif ($start) {
                $query->where('ll.delivery_date', '>=', $start);
            } elseif ($end) {
                $query->where('ll.delivery_date', '<=', $end);
            }
        }

        $rows = $query
            ->selectRaw("
                COALESCE(cp.part_number, '-') as part_no,
                SUM(COALESCE(lld.kanban_qty, 0)) as total_target,
                SUM(COALESCE(lld.actual_kanban_qty, 0)) as total_done
            ")
            ->groupBy('cp.part_number')
            ->get();

        return collect($rows)
            ->map(function ($part) {
                $target = (int) ($part->total_target ?? 0);
                $done = (int) ($part->total_done ?? 0);
                $shortage = max(0, $target - $done);

                return [
                    'part_no' => (string) ($part->part_no ?? '-'),
                    'qty_kurang' => $shortage,
                ];
            })
            ->filter(function (array $part) {
                return $part['qty_kurang'] > 0;
            })
            ->sortByDesc('qty_kurang')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $masterMeta
     * @return array{
     *   bucket_key: string,
     *   customer_cycle_key: string,
     *   master_cycle_id: int|null,
     *   cycle_name: string,
     *   cycle_time: string,
     *   mapping_source: string
     * }
     */
    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $masterMeta
     */
    private function findMasterCycleForDeliveryBucket(int $customerId, string $cycleName, $masterMeta): ?array
    {
        $match = $masterMeta->first(function (array $m) use ($customerId, $cycleName) {
            return $m['customer_id'] === $customerId
                && $m['cycle_name'] === $cycleName;
        });

        return $match !== null ? $match : null;
    }

    private function deliveryChartRowHasConfiguredMasterCycle(?array $master): bool
    {
        if ($master === null) {
            return false;
        }

        $prepStart = trim((string) ($master['time'] ?? ''));
        $prepEnd = trim((string) ($master['prep_end_time'] ?? ''));

        return $prepStart !== '' && $prepEnd !== '';
    }

    private function resolveCycleBucketForLoadingList($ll, $masterMeta): array
    {
        $cid = (int) $ll->customer_id;
        $llCycleRaw = $ll->cycle;

        $candidates = $masterMeta->filter(function (array $m) use ($cid) {
            return $m['customer_id'] === $cid;
        })->values();

        $matchedByName = null;
        foreach ($candidates as $m) {
            if ($this->cycleLabelMatchesMaster($m['cycle_name'], $llCycleRaw)) {
                $matchedByName = $m;

                break;
            }
        }

        if ($matchedByName !== null) {
            $cycleKey = $cid.'_c_'.md5((string) $matchedByName['cycle_name']);

            return [
                'bucket_key' => $cycleKey,
                'customer_cycle_key' => $cycleKey,
                'master_cycle_id' => $matchedByName['id'],
                'cycle_name' => $matchedByName['cycle_name'],
                'cycle_time' => $matchedByName['time'],
                'mapping_source' => 'master_cycle_name',
            ];
        }

        $cycleLabel = ($llCycleRaw !== null && $llCycleRaw !== '') ? (string) $llCycleRaw : '(tanpa cycle)';
        $vk = $cid.'_c_'.md5($cycleLabel);

        return [
            'bucket_key' => $vk,
            'customer_cycle_key' => $vk,
            'master_cycle_id' => null,
            'cycle_name' => $cycleLabel,
            'cycle_time' => '06:00',
            'mapping_source' => 'loading_list_only',
        ];
    }

    private function normalizeCycleNumber($cycle): string
    {
        $raw = trim((string) ($cycle ?? ''));
        if ($raw === '') {
            return '(tanpa cycle)';
        }

        if (preg_match('/\d+/', $raw, $m)) {
            return (string) ((int) $m[0]);
        }

        return $raw;
    }

    private function cycleLabelMatchesMaster(string $masterCycleName, $loadingListCycle): bool
    {
        $ll = $loadingListCycle === null ? '' : trim((string) $loadingListCycle);
        $m = trim($masterCycleName);

        if ($ll === '' && $m === '') {
            return true;
        }
        if ($ll !== '' && strcasecmp($m, $ll) === 0) {
            return true;
        }

        $digitsLl = preg_replace('/\D/', '', $ll);
        $digitsM = preg_replace('/\D/', '', $m);
        if ($digitsLl !== '' && $digitsM !== '' && $digitsLl === $digitsM) {
            return true;
        }

        $dn = preg_replace('/\s+/u', ' ', Str::lower($ll));
        $mn = preg_replace('/\s+/u', ' ', Str::lower($m));

        return $dn !== '' && $dn === $mn;
    }

    private function timeOfDayToMinutes($value): int
    {
        $c = Carbon::parse($value);

        return ($c->hour * 60) + $c->minute;
    }

    private function normalizePrepDayOffset($value): int
    {
        $offset = (int) $value;
        if ($offset < -14) {
            return -14;
        }
        if ($offset > 14) {
            return 14;
        }

        return $offset;
    }

    private function prepTimeRangeCrossesMidnight(string $prepStart, string $prepEnd): bool
    {
        $start = trim($prepStart);
        $end = trim($prepEnd);
        if ($start === '' || $end === '') {
            return false;
        }

        return $this->timeOfDayToMinutes($end) <= $this->timeOfDayToMinutes($start);
    }

    private function computePrepEndDayOffset(int $prepDayOffset, string $prepStart, string $prepEnd): int
    {
        if ($this->prepTimeRangeCrossesMidnight($prepStart, $prepEnd)) {
            return $prepDayOffset + 1;
        }

        return $prepDayOffset;
    }

    private function formatPrepDayOffsetLabel(int $offset): string
    {
        if ($offset === 0) {
            return 'H+0';
        }
        if ($offset > 0) {
            return 'H+'.$offset;
        }

        return 'H'.$offset;
    }

    /**
     * @return array{
     *   absolute_start_datetime: string|null,
     *   absolute_end_datetime: string|null
     * }
     */
    private function computePrepAbsoluteDatetimes(
        string $deliveryDate,
        int $prepDayOffset,
        int $prepEndDayOffset,
        ?string $prepStart,
        ?string $prepEnd
    ): array {
        $prepStartClock = $prepStart !== null ? substr(trim($prepStart), 0, 5) : '';
        $prepEndClock = $prepEnd !== null ? substr(trim($prepEnd), 0, 5) : '';

        if ($prepStartClock === '') {
            return [
                'absolute_start_datetime' => null,
                'absolute_end_datetime' => null,
            ];
        }

        $start = Carbon::parse(
            Carbon::parse($deliveryDate)->addDays($prepDayOffset)->format('Y-m-d').' '.$prepStartClock
        );
        $end = $prepEndClock !== ''
            ? Carbon::parse(
                Carbon::parse($deliveryDate)->addDays($prepEndDayOffset)->format('Y-m-d').' '.$prepEndClock
            )
            : $start->copy()->addMinutes(45);

        return [
            'absolute_start_datetime' => $start->format('Y-m-d\TH:i:s'),
            'absolute_end_datetime' => $end->format('Y-m-d\TH:i:s'),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function appendPrepAbsoluteDatetimesToChartRow(array $row): array
    {
        if (empty($row['master_cycle_configured'])) {
            $row['absolute_start_datetime'] = null;
            $row['absolute_end_datetime'] = null;

            return $row;
        }

        $deliveryDate = (string) ($row['delivery_date'] ?? '');
        $prepStart = isset($row['cycle_time']) ? (string) $row['cycle_time'] : null;
        $prepEnd = isset($row['prep_end_time']) ? (string) $row['prep_end_time'] : null;
        $prepDayOffset = (int) ($row['prep_day_offset'] ?? 0);
        $prepEndDayOffset = (int) ($row['prep_end_day_offset'] ?? $prepDayOffset);

        $absolute = $this->computePrepAbsoluteDatetimes(
            $deliveryDate,
            $prepDayOffset,
            $prepEndDayOffset,
            $prepStart,
            $prepEnd
        );

        return array_merge($row, $absolute);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyMasterCyclePrepOffsets(array $validated): array
    {
        $prepDayOffset = $this->normalizePrepDayOffset($validated['prep_day_offset'] ?? 0);
        $validated['prep_day_offset'] = $prepDayOffset;
        $validated['prep_end_day_offset'] = $this->computePrepEndDayOffset(
            $prepDayOffset,
            (string) $validated['time'],
            (string) $validated['prep_end_time']
        );

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMasterCycleForApi(MasterCycle $m): array
    {
        $prepStart = Carbon::parse($m->time)->format('H:i');
        $prepEnd = $m->prep_end_time
            ? Carbon::parse($m->prep_end_time)->format('H:i')
            : null;
        $prepDayOffset = $this->normalizePrepDayOffset($m->prep_day_offset ?? 0);
        $prepEndDayOffset = $this->computePrepEndDayOffset($prepDayOffset, $prepStart, (string) $prepEnd);
        $crossesMidnight = $prepEnd !== null
            && $this->prepTimeRangeCrossesMidnight($prepStart, $prepEnd);

        $payload = [
            'id' => $m->id,
            'customer_id' => $m->customer_id,
            'customer_name' => optional($m->customer)->name,
            'cycle_name' => $m->cycle_name,
            'time' => $prepStart,
            'prep_end_time' => $prepEnd,
            'prep_day_offset' => $prepDayOffset,
            'prep_end_day_offset' => $prepEndDayOffset,
            'prep_day_offset_label' => $this->formatPrepDayOffsetLabel($prepDayOffset),
            'prep_end_day_offset_label' => $this->formatPrepDayOffsetLabel($prepEndDayOffset),
            'prep_crosses_midnight' => $crossesMidnight,
            'truck_time' => $m->truck_time
                ? Carbon::parse($m->truck_time)->format('H:i')
                : null,
            'absolute_start_datetime' => null,
            'absolute_end_datetime' => null,
        ];

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMasterCycleMetaForChart(MasterCycle $m): array
    {
        $formatted = $this->formatMasterCycleForApi($m);

        return [
            'id' => $formatted['id'],
            'customer_id' => (int) $formatted['customer_id'],
            'cycle_name' => $this->normalizeCycleNumber($m->cycle_name),
            'time' => $formatted['time'],
            'prep_end_time' => $formatted['prep_end_time'],
            'prep_day_offset' => $formatted['prep_day_offset'],
            'prep_end_day_offset' => $formatted['prep_end_day_offset'],
            'prep_day_offset_label' => $formatted['prep_day_offset_label'],
            'prep_end_day_offset_label' => $formatted['prep_end_day_offset_label'],
            'prep_crosses_midnight' => $formatted['prep_crosses_midnight'],
            'truck_time' => $formatted['truck_time'],
            'absolute_start_datetime' => $formatted['absolute_start_datetime'],
            'absolute_end_datetime' => $formatted['absolute_end_datetime'],
        ];
    }

    /**
     * JSON for bar chart: per-cycle completion = (jumlah LL selesai) / (total LL dalam cycle) × 100.
     * Satu LL dianggap selesai jika total_kanban > 0 dan actual_kanban >= total_kanban (sama logika status COMPLETE di loading list).
     */
    public function getDeliveryCycleProgress(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
        ]);

        $start = Carbon::parse($validated['delivery_date'])->startOfDay();
        $end = (clone $start)->endOfDay();

        $loadingLists = LoadingList::query()
            ->where('customer_id', $validated['customer_id'])
            ->whereBetween('delivery_date', [$start, $end])
            ->withSum('detail as total_kanban', 'kanban_qty')
            ->withSum('detail as actual_kanban', 'actual_kanban_qty')
            ->get();

        $cycles = $this->aggregateCyclesFromLoadingLists($loadingLists);

        return response()->json([
            'cycles' => $cycles,
            'meta' => [
                'customer_id' => (int) $validated['customer_id'],
                'delivery_date' => $start->toDateString(),
                'total_rows' => $loadingLists->count(),
            ],
        ]);
    }

    private function formatTruckStatusBadge(string $status, bool $allowPartial = false): string
    {
        if ($status === 'picked') {
            return '<span class="bella-badge bella-badge-green"><i class="fas fa-truck"></i> Sudah Diambil</span>';
        }

        if ($allowPartial && $status === 'partial') {
            return '<span class="bella-badge bella-badge-amber"><i class="fas fa-truck-loading"></i> Sebagian</span>';
        }

        return '<span class="bella-badge bella-badge-gray"><i class="fas fa-clock"></i> Belum Diambil</span>';
    }

    private function resolveCustomerFromLabelScan(string $barcode): ?Customer
    {
        $raw = trim($barcode);
        if ($raw === '') {
            return null;
        }

        $upper = strtoupper($raw);

        $byCode = Customer::query()->where('code', $raw)->orWhere('code', $upper)->first();
        if ($byCode) {
            return $byCode;
        }

        $byName = Customer::query()
            ->whereRaw('UPPER(name) = ?', [$upper])
            ->orWhere('name', 'like', '%'.$raw.'%')
            ->first();
        if ($byName) {
            return $byName;
        }

        $customers = Customer::query()
            ->whereNotNull('char_length')
            ->where('char_length', '>', 0)
            ->get();

        foreach ($customers as $customer) {
            $extracted = strtoupper(trim(str_replace('-', '', substr($raw, (int) $customer->char_first, (int) $customer->char_length))));
            if ($extracted === '') {
                continue;
            }

            if (strcasecmp((string) $customer->code, $extracted) === 0) {
                return $customer;
            }

            if (stripos((string) $customer->name, $extracted) !== false) {
                return $customer;
            }
        }

        return null;
    }

    private function getDistinctCyclesForCustomer(int $customerId, ?string $deliveryDate = null): array
    {
        $masters = MasterCycle::query()
            ->where('customer_id', $customerId)
            ->orderBy('time')
            ->orderBy('cycle_name')
            ->get();

        return $masters->map(function (MasterCycle $master) use ($customerId, $deliveryDate) {
            $loadingLists = $this->findLoadingListsByCustomerAndCycle($customerId, $master->cycle_name, $deliveryDate);
            $picked = $loadingLists->isNotEmpty()
                && $loadingLists->every(fn (LoadingList $ll) => ($ll->truck_status ?? 'pending') === 'picked');
            $formatted = $this->formatMasterCycleForApi($master);

            return [
                'id' => $master->id,
                'cycle' => $master->cycle_name,
                'cycle_name' => $master->cycle_name,
                'time' => $formatted['time'],
                'prep_end_time' => $formatted['prep_end_time'],
                'truck_time' => $formatted['truck_time'],
                'loading_list_count' => $loadingLists->count(),
                'loading_list_numbers' => $loadingLists->pluck('number')->values(),
                'has_loading_list' => $loadingLists->isNotEmpty(),
                'truck_status' => $picked ? 'picked' : 'pending',
            ];
        })->values()->all();
    }

    private function resolveCycleFromBarcode(int $customerId, string $barcode): ?string
    {
        $raw = trim($barcode);
        if ($raw === '') {
            return null;
        }

        $candidates = array_values(array_unique(array_filter([
            $raw,
            strtoupper($raw),
            strlen($raw) >= 11 ? substr($raw, 0, 11).' A' : null,
            strlen($raw) >= 11 ? substr($raw, 0, 11) : null,
        ])));

        $masterQuery = MasterCycle::query()->where('customer_id', $customerId);
        $master = (clone $masterQuery)
            ->where(function ($q) use ($candidates, $raw) {
                foreach ($candidates as $candidate) {
                    $q->orWhereRaw('LOWER(cycle_name) = ?', [Str::lower($candidate)]);
                }
                if (ctype_digit($raw)) {
                    $q->orWhere('id', (int) $raw);
                }
            })
            ->first();

        if ($master) {
            return trim((string) $master->cycle_name);
        }

        if (preg_match('/\d+/', $raw, $m)) {
            $num = $m[0];
            $masterByNumber = (clone $masterQuery)
                ->where(function ($q) use ($num) {
                    $q->whereRaw('LOWER(cycle_name) = ?', [Str::lower($num)])
                        ->orWhereRaw('LOWER(cycle_name) = ?', [Str::lower('Cycle '.$num)])
                        ->orWhereRaw('LOWER(cycle_name) = ?', [Str::lower('cycle '.$num)]);
                })
                ->first();

            if ($masterByNumber) {
                return trim((string) $masterByNumber->cycle_name);
            }
        }

        foreach ($candidates as $candidate) {
            $ll = LoadingList::query()
                ->where('customer_id', $customerId)
                ->where('number', $candidate)
                ->first();

            if ($ll && $ll->cycle) {
                return trim((string) $ll->cycle);
            }
        }

        foreach ($candidates as $candidate) {
            $ll = LoadingList::query()
                ->where('customer_id', $customerId)
                ->where('cycle', $candidate)
                ->first();

            if ($ll && $ll->cycle) {
                return trim((string) $ll->cycle);
            }
        }

        return null;
    }

    private function findLoadingListsByCustomerAndCycle(int $customerId, string $cycleInput, ?string $deliveryDate = null): \Illuminate\Support\Collection
    {
        $cycleRaw = trim($cycleInput);
        $cycleNumber = null;
        if (preg_match('/\d+/', $cycleRaw, $m)) {
            $cycleNumber = $m[0];
        }

        if ($deliveryDate) {
            $start = Carbon::parse($deliveryDate)->startOfDay();
            $end = Carbon::parse($deliveryDate)->endOfDay();
        } else {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        }

        return LoadingList::query()
            ->where('customer_id', $customerId)
            ->whereBetween('delivery_date', [$start, $end])
            ->where(function ($q) use ($cycleRaw, $cycleNumber) {
                if ($cycleNumber !== null) {
                    $numInt = (int) $cycleNumber;
                    $numPad = str_pad((string) $numInt, 2, '0', STR_PAD_LEFT);

                    $q->whereRaw('LOWER(cycle) = ?', [Str::lower($cycleRaw)])
                       ->orWhere('cycle', (string) $numInt)
                       ->orWhere('cycle', $numPad)
                       ->orWhereRaw('LOWER(cycle) = ?', ['cycle '.$numInt])
                       ->orWhereRaw('LOWER(cycle) = ?', ['cycle '.$numPad])
                       ->orWhereRaw('LOWER(cycle) = ?', ['cycle'.$numInt])
                       ->orWhereRaw('LOWER(cycle) = ?', ['c'.$numInt])
                       ->orWhereRaw('LOWER(cycle) = ?', ['c'.$numPad]);
                } else {
                    $q->whereRaw('LOWER(cycle) = ?', [Str::lower($cycleRaw)]);
                }
            })
            ->get();
    }

    private function resolveExpectedTruckDatetime(string $deliveryDate, array $master): ?Carbon
    {
        $prepStart = trim((string) ($master['time'] ?? ''));
        $prepEnd = trim((string) ($master['prep_end_time'] ?? ''));
        $truckTime = trim((string) ($master['truck_time'] ?? ''));

        if ($prepStart === '' || $truckTime === '') {
            return null;
        }

        $prepDayOffset = $this->normalizePrepDayOffset((int) ($master['prep_day_offset'] ?? 0));

        $prepStartDate = Carbon::parse($deliveryDate)->addDays($prepDayOffset);

        $truckOnNextDay = false;
        $truckMin = $this->timeOfDayToMinutes($truckTime);
        $startMin = $this->timeOfDayToMinutes($prepStart);
        $endMin = $prepEnd !== '' ? $this->timeOfDayToMinutes($prepEnd) : $startMin;

        $prepCrossesMidnight = $this->prepTimeRangeCrossesMidnight($prepStart, $prepEnd);

        if ($prepCrossesMidnight) {
            if ($truckMin < $startMin) {
                if ($truckMin <= $endMin) {
                    $truckOnNextDay = true;
                } elseif ($truckMin > $endMin) {
                    if ($truckMin >= 18 * 60 && $truckMin < $startMin) {
                        $truckOnNextDay = false;
                    } else {
                        $truckOnNextDay = true;
                    }
                }
            }
        } else {
            if ($prepStart !== '' && $this->timeOfDayToMinutes($prepStart) >= 18 * 60 && $truckMin < 12 * 60 && $truckMin < $startMin) {
                $truckOnNextDay = true;
            } elseif ($truckMin < $endMin && $truckMin < $startMin) {
                $truckOnNextDay = true;
            }
        }

        if ($truckOnNextDay) {
            return $prepStartDate->copy()->addDay()->setTimeFromTimeString($truckTime);
        }

        return $prepStartDate->copy()->setTimeFromTimeString($truckTime);
    }

    private function calculateTruckArrivalStatus(string $deliveryDate, ?string $truckPickedAt, ?array $master): string
    {
        if ($truckPickedAt === null || $master === null) {
            return '-';
        }

        $expectedDatetime = $this->resolveExpectedTruckDatetime($deliveryDate, $master);
        if ($expectedDatetime === null) {
            return '-';
        }

        $actualDatetime = Carbon::parse($truckPickedAt);

        $earlyThreshold = $expectedDatetime->copy()->subMinutes(15);

        if ($actualDatetime->gt($expectedDatetime)) {
            return 'Delay';
        } elseif ($actualDatetime->lt($earlyThreshold)) {
            return 'Early';
        } else {
            return 'Tepat Waktu';
        }
    }

    private function formatTruckArrivalStatusBadge(string $status): string
    {
        if ($status === 'Tepat Waktu') {
            return '<span class="bella-badge bella-badge-green"><i class="fas fa-check-circle"></i> Tepat Waktu</span>';
        } elseif ($status === 'Early') {
            return '<span class="bella-badge bella-badge-amber"><i class="fas fa-arrow-circle-down"></i> Early</span>';
        } elseif ($status === 'Delay') {
            return '<span class="bella-badge bella-badge-danger"><i class="fas fa-exclamation-circle"></i> Delay</span>';
        }

        return '<span class="text-muted">-</span>';
    }
}
