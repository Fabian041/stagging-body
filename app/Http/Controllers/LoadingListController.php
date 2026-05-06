<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\Kanban;
use App\Models\Customer;
use App\Models\Mutation;
use App\Models\SkidDetail;
use App\Models\LoadingList;
use App\Models\MasterCycle;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Models\LoadingListDetail;
use App\Models\KanbanAfterPulling;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Yajra\DataTables\Facades\DataTables;

class LoadingListController extends Controller
{
    public $temp_serial_number;
    public function pushData($is_updated){
        // connection to pusher
        $options = array(
            'cluster' => 'ap1',
            'encrypted' => true
        );

        $pusher = new Pusher(
            '78dc86268a49904a688d',
            '19c222ee916e49372796',
            '1720799',
            $options
        );

        // sending data
        $result = $pusher->trigger('loading-list' , 'loadingListUpdated', $is_updated);

        return $result;
    }
    
    public function index()
    {
        return view('pages.loadingList',[
            'customers' => Customer::all(),
            'manifests' => LoadingList::select('pds_number')->distinct()->get()
        ]);
    }


    // Modified getLoadingList method that groups by pds_number
    public function getLoadingList(Request $request)
    {
        try {
            $baseQuery = LoadingList::with(['customer'])
                ->withSum('detail as total_kanban', 'kanban_qty')
                ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                ->whereNotNull('customer_id')
                ->latest();

            // Filter from query string: ?customer=MMKI
            if ($request->filled('customer')) {
                $customerName = trim((string) $request->get('customer'));
                $baseQuery->whereHas('customer', function ($q) use ($customerName) {
                    $q->where('name', $customerName);
                });
            }

            // Filter from query string: ?cycle=1 or ?cycle=Cycle 1
            if ($request->filled('cycle')) {
                $cycleRaw = trim((string) $request->get('cycle'));
                $cycleNumber = null;
                if (preg_match('/\d+/', $cycleRaw, $m)) {
                    $cycleNumber = $m[0];
                }

                $baseQuery->where(function ($q) use ($cycleRaw, $cycleNumber) {
                    $q->where('cycle', $cycleRaw);
                    if ($cycleNumber !== null) {
                        $q->orWhere('cycle', $cycleNumber)
                          ->orWhere('cycle', 'Cycle '.$cycleNumber)
                          ->orWhere('cycle', 'cycle '.$cycleNumber);
                    }
                });
            }

            // Filter from query string: ?delivery_date=2026-04-16 (optional)
            if ($request->filled('delivery_date')) {
                $start = Carbon::parse($request->get('delivery_date'))->startOfDay();
                $end = (clone $start)->endOfDay();
                $baseQuery->whereBetween('delivery_date', [$start, $end]);
            }

            // Group loading lists by pds_number and aggregate the data
            $groupedData = $baseQuery
                ->take(500)
                ->get()
                ->groupBy('pds_number')
                ->map(function ($loadingLists, $pdsNumber) {
                    $firstLoadingList = $loadingLists->first();
                    
                    // Aggregate totals
                    $totalKanban = $loadingLists->sum('total_kanban');
                    $actualKanban = $loadingLists->sum('actual_kanban');
                    
                    // Get all loading list numbers
                    $loadingListNumbers = $loadingLists->pluck('number')->toArray();
                    
                    // Earliest delivery date
                    $earliestDeliveryDate = $loadingLists->min('delivery_date');
                    
                    // Most common cycle
                    $cycle = $loadingLists->groupBy('cycle')->sortByDesc(function($items) {
                        return $items->count();
                    })->keys()->first();

                    return (object) [
                        'id' => 'pds-' . $pdsNumber,
                        'pds_number' => $pdsNumber,
                        'loading_list_numbers' => $loadingListNumbers,
                        'loading_list_count' => $loadingLists->count(),
                        'customer' => $firstLoadingList->customer,
                        'cycle' => $cycle,
                        'delivery_date' => $earliestDeliveryDate,
                        'total_kanban' => $totalKanban,
                        'actual_kanban' => $actualKanban,
                        'loading_lists' => $loadingLists
                    ];
                })
                ->values(); // Reset array keys

            return DataTables::of($groupedData)
                ->addColumn('customer', function ($group) {
                    return $group->customer->name ?? '-';
                })
                ->addColumn('loading_and_status', function ($group) {
                    // Common button styles for consistent sizing
                    $buttonStyle = 'style="min-width: 120px; padding: 8px 12px; font-size: 13px; font-weight: 500; text-align: center; white-space: nowrap;"';
                    
                    // Tombol Loading Lists
                    $loadingBtn = '<button class="btn btn-info text-white mr-2 show-loading-lists" ' . $buttonStyle . ' data-pds="' . $group->pds_number . '">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Detail
                                </button>';
                
                    // Tombol Status
                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;
                
                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusButton = '<button class="btn btn-success" ' . $buttonStyle . '>
                                            <i class="fas fa-check mr-1"></i>
                                            COMPLETE
                                        </button>';
                    } elseif ($actualKanban > 0) {
                        $statusButton = '<button class="btn btn-outline-warning" ' . $buttonStyle . '>
                                            IN PROGRESS
                                        </button>';
                    } else {
                        $statusButton = '<button class="btn btn-outline-danger" ' . $buttonStyle . '>
                                            INCOMPLETE
                                        </button>';
                    }
                
                    return '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; align-items: center;">' . $loadingBtn . $statusButton . '</div>';
                })
                ->addColumn('progress', function ($group) {
                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;
                    $progressPercentage = ($totalKanban > 0) ? round(($actualKanban / $totalKanban) * 100) : 0;

                    // Progress bar colors
                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusClass = 'lightgreen';
                    } elseif ($actualKanban > 0) {
                        $statusClass = 'orange';
                    } else {
                        $statusClass = 'red';
                    }

                    $progress = '
                        <div class="text-small font-weight-bold text-muted mb-1 text-center">'
                            . $actualKanban . ' / ' . $totalKanban .
                        '</div>
                        <div class="progress" data-height="20" style="height: 18px;">
                            <div class="progress-bar" role="progressbar"
                                style="width:' . $progressPercentage . '%; background-color: ' . $statusClass . ' !important"
                                aria-valuenow="' . $progressPercentage . '" aria-valuemin="0" aria-valuemax="100">
                                <small class="text-white font-weight-bold">' . $progressPercentage . '%</small>
                            </div>
                        </div>';

                    return $progress;
                })
                ->setRowId(function ($group) {
                    return 'row-' . $group->id; // $group->id is already 'pds-123'
                })
                ->rawColumns(['loading_and_status', 'progress', 'customer'])
                ->make(true);
                
        } catch (\Exception $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: ' . $e->getMessage()
            ]);
        }
    }

    // New method to get loading lists by PDS number for accordion
    public function getLoadingListsByPds(Request $request)
    {
        try {
            $pdsNumber = $request->get('pds_number');
            
            if (!$pdsNumber) {
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
                        'total_kanban' => $loadingList->total_kanban ?? 0,
                        'actual_kanban' => $loadingList->actual_kanban ?? 0,
                        'created_at' => $loadingList->created_at,
                        'updated_at' => $loadingList->updated_at
                    ];
                });

            return response()->json([
                'loading_lists' => $loadingLists,
                'pds_number' => $pdsNumber,
                'total_count' => $loadingLists->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to load loading lists: ' . $e->getMessage()
            ], 500);
        }
    }

    // Updated checkLoadingListUpdates for grouped data
    public function checkLoadingListUpdates(Request $request)
    {
        try {
            // Get the current state from client
            $currentState = $request->input('state', []);
            $currentPdsCount = $currentState['pdsCount'] ?? 0;
            $latestPdsNumbers = $currentState['latestPdsNumbers'] ?? [];
            
            // Get server state
            $serverPdsCount = LoadingList::distinct('pds_number')->count();
            $serverLatestPds = LoadingList::select('pds_number')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->pluck('pds_number')
                ->toArray();
                
            // Check if counts differ or if any new PDS numbers exist
            $hasNewData = ($serverPdsCount != $currentPdsCount) || 
                        count(array_diff($serverLatestPds, $latestPdsNumbers)) > 0;
                        
            return response()->json([
                'hasNewData' => $hasNewData,
                'serverPdsCount' => $serverPdsCount,
                'serverLatestPds' => $serverLatestPds,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => true], 500);
        }
    }

    // Updated getLoadingListUpdates for grouped data
    public function getLoadingListUpdates(Request $request)
    {
        try {
            $pdsNumbers = $request->input('ids', []);
            
            if (empty($pdsNumbers)) {
                return response()->json(['updatedRows' => []]);
            }

            // Get updated data for specific PDS numbers
            $updatedGroups = collect();
            
            foreach ($pdsNumbers as $pdsId) {
                // Extract PDS number from ID (remove 'pds-' prefix)
                $pdsNumber = str_replace('pds-', '', $pdsId);
                
                $loadingLists = LoadingList::where('pds_number', $pdsNumber)
                    ->with(['customer'])
                    ->withSum('detail as total_kanban', 'kanban_qty')
                    ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                    ->get();

                if ($loadingLists->isNotEmpty()) {
                    $totalKanban = $loadingLists->sum('total_kanban');
                    $actualKanban = $loadingLists->sum('actual_kanban');
                    $progressPercentage = ($totalKanban > 0) ? round(($actualKanban / $totalKanban) * 100) : 0;
                    $loadingListCount = $loadingLists->count();

                    // Generate updated progress HTML
                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusClass = 'lightgreen';
                    } elseif ($actualKanban > 0) {
                        $statusClass = 'orange';
                    } else {
                        $statusClass = 'red';
                    }

                    $progress = '
                        <div class="text-small font-weight-bold text-muted mb-1 text-center">'
                            . $actualKanban . ' / ' . $totalKanban .
                        '</div>
                        <div class="progress" data-height="20" style="height: 18px;">
                            <div class="progress-bar" role="progressbar"
                                style="width:' . $progressPercentage . '%; background-color: ' . $statusClass . ' !important"
                                aria-valuenow="' . $progressPercentage . '" aria-valuemin="0" aria-valuemax="100">
                                <small class="text-white font-weight-bold">' . $progressPercentage . '%</small>
                            </div>
                        </div>';

                    // Generate updated detail HTML (status button)
                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $detail = '<button class="btn btn-success">
                                    <i class="fas fa-check" style="padding-right: 1px"></i>
                                        COMPLETE
                                    </button>';
                    } elseif ($actualKanban > 0) {
                        $detail = '<button class="btn btn-outline-warning">
                                        INPROGRESS
                                    </button>';
                    } else {
                        $detail = '<button class="btn btn-outline-danger">
                                        INCOMPLETE
                                    </button>';
                    }

                    $updatedGroups->push([
                        'id' => 'row-pds-' . $pdsNumber,
                        'progress' => $progress,
                        'detail' => $detail,
                        'updated_at' => $loadingLists->max('updated_at')
                    ]);
                }
            }

            return response()->json([
                'updatedRows' => $updatedGroups
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Unable to get row updates'
            ], 500);
        }
    }

    public function detail(LoadingList $loadingList)
    {
        $loadingListDetail = DB::table('loading_lists')
                            ->join('loading_list_details', 'loading_lists.id', 'loading_list_details.loading_list_id')
                            ->join('customers', 'customers.id', 'loading_lists.customer_id')
                            ->select('loading_lists.number', 'loading_lists.pds_number', 'loading_lists.cycle', 'loading_lists.delivery_date', 'loading_lists.shipping_date','customers.name')
                            ->where('loading_list_details.loading_list_id', $loadingList->id)
                            ->first();

        return view('pages.loadingListDetail',[
            'customers' => Customer::all(),
            'loadingListDetail' => $loadingListDetail,
            'loadingListId' => $loadingList->id,
        ]);
    }

    public function getLoadingListDetail(LoadingList $loadingList)
    {
        $input = LoadingListDetail::with(['customerPart.internalPart'])
            ->where('loading_list_id', $loadingList->id)
            ->get();

        return DataTables::of($input)
            ->addColumn('part_name', function ($row) {
                return optional($row->customerPart->internalPart)->part_name ?? '-';
            })
            ->addColumn('cust_partno', function ($row) {
                return '<span class="customerPart">' . (optional($row->customerPart)->part_number ?? '-') . '</span>';
            })
            ->addColumn('int_partno', function ($row) {
                return optional($row->customerPart->internalPart)->part_number ?? '-';
            })
            ->addColumn('cust_backno', function ($row) {
                return '<span class="backNumber">' . (optional($row->customerPart)->back_number ?? '-') . '</span>';
            })
            ->addColumn('int_backno', function ($row) {
                return optional($row->customerPart->internalPart)->back_number ?? '-';
            })
            ->addColumn('kbn_qty', function ($row) {
                return $row->kanban_qty;
            })
            ->addColumn('actual_kbn_qty', function ($row) {
                return '<span class="actual">' . $row->actual_kanban_qty . '</span>
                    <input id="editActual" class="form-control editActual" type="number"
                        value="' . $row->actual_kanban_qty . '" data-width="100"
                        style="border-radius:6px; display:none">';
            })
            // ✅ Pulling Date = mutations type checkout (window per detail) + bawa mutation_id
            ->addColumn('pulling_date', function ($row) {
                $internalPartId = optional($row->customerPart->internalPart)->id;

                if (!$internalPartId) return '<span class="text-danger">N/A</span>';

                $start = $row->created_at;
                $end   = $row->updated_at;

                if (!$start || !$end || $start->equalTo($end)) return '<span class="text-danger">N/A</span>';

                $mutations = Mutation::query()
                    ->select('id','serial_number','qty','date','created_at')
                    ->where('internal_part_id', $internalPartId)
                    ->where('type', 'checkout')
                    ->whereBetween('created_at', [$start, $end])
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($mutations->isEmpty()) return '<span class="text-danger">N/A</span>';

                $lines = [];
                foreach ($mutations as $m) {
                    // ⚠️ span ini penting: data-mid dipakai JS untuk delete per mutation
                    $text = "[{$m->serial_number}] - [{$m->date}] (qty: {$m->qty})";
                    $lines[] = '<span class="mline" data-mid="'.$m->id.'">'.e($text).'</span>';
                }

                return implode('<br>', $lines);
            })

            // ✅ Production Date = supply terakhir sebelum pulling (serial sama) + bawa supply_id jika ada
            ->addColumn('prod_date', function ($row) {
                $internalPartId = optional($row->customerPart->internalPart)->id;

                if (!$internalPartId) return '<span class="text-danger">N/A</span>';

                $start = $row->created_at;
                $end   = $row->updated_at;

                if (!$start || !$end || $start->equalTo($end)) return '<span class="text-danger">N/A</span>';

                $checkouts = Mutation::query()
                    ->select('id','serial_number','date','qty','created_at')
                    ->where('internal_part_id', $internalPartId)
                    ->where('type', 'checkout')
                    ->whereBetween('created_at', [$start, $end])
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($checkouts->isEmpty()) return '<span class="text-danger">N/A</span>';

                $lines = [];

                foreach ($checkouts as $co) {
                    $serial = $co->serial_number;

                    $supply = Mutation::query()
                        ->select('id','serial_number','date','qty','created_at')
                        ->where('internal_part_id', $internalPartId)
                        ->where('type', 'supply')
                        ->where('serial_number', $serial)
                        ->where('created_at', '<', $co->created_at)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$supply) {
                        $text = "[{$serial}] - [N/A]";
                        $lines[] = '<span class="mline" data-mid="">'.e($text).'</span>';
                        continue;
                    }

                    $text = "[{$serial}] - [{$supply->date}] (qty: {$supply->qty})";
                    $lines[] = '<span class="mline" data-mid="'.$supply->id.'">'.e($text).'</span>';
                }

                return implode('<br>', $lines);
            })
            ->addColumn('edit', function ($row) {
                return '<button class="btn btn-icon btn-primary edit" id="edit"><i class="far fa-edit"></i></button>
                    <button class="btn btn-icon btn-success save mb-1" style="display: none"><i class="fas fa-check"></i></button>
                    <button class="btn btn-icon btn-danger cancel" style="display: none"><i class="fas fa-times"></i></button>';
            })
            ->rawColumns([
                'cust_partno',
                'cust_backno',
                'actual_kbn_qty',
                'edit',
                'pulling_date',
                'prod_date',
            ])
            ->toJson();
    }

    public function deletePullingMutation(LoadingListDetail $detail, Mutation $mutation)
    {
        $internalPartId = optional(optional($detail->customerPart)->internalPart)->id;
        if (!$internalPartId) {
            return response()->json(['status' => 'error', 'message' => 'Internal part tidak ditemukan.'], 404);
        }

        $start = $detail->created_at;
        $end   = $detail->updated_at;

        if (!$start || !$end || $start->equalTo($end)) {
            return response()->json(['status' => 'error', 'message' => 'Window waktu detail tidak valid.'], 422);
        }

        // Validasi: hanya boleh hapus checkout pada internal_part + window ini
        $isValid = $mutation->internal_part_id == $internalPartId
            && $mutation->type === 'checkout'
            && $mutation->created_at >= $start
            && $mutation->created_at <= $end;

        if (!$isValid) {
            return response()->json(['status' => 'error', 'message' => 'Mutation tidak valid untuk dihapus.'], 403);
        }

        DB::transaction(function () use ($detail, $mutation) {
            // delete 1 record mutation
            $mutation->delete();

            // ✅ setiap mutation = 1 scan, jadi -1
            $current = (int)($detail->actual_kanban_qty ?? 0);
            $newVal  = $current - 1;
            if ($newVal < 0) $newVal = 0;

            $detail->actual_kanban_qty = $newVal;
            $detail->save();
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Berhasil hapus pulling mutation id: {$mutation->id} dan actual qty dikurangi 1.",
            'data' => [
                'detail_id' => $detail->id,
                'actual_kanban_qty' => (int)$detail->fresh()->actual_kanban_qty,
            ]
        ]);
    }

    public function editLoadingListDetail($loadingList, $customerPart, $backNumber, $newActual)
    {
        if($backNumber == 'null'){
            $backNumber = null;
        }
        
        // get customer part id
        $customerPartId = CustomerPart::select('id')
                            ->where('part_number',$customerPart)
                            ->where('back_number',$backNumber)
                            ->first();

        // get kanban qty
        $maxKanbanQty = LoadingListDetail::select('id','kanban_qty')
                                        ->where('loading_list_id',$loadingList)
                                        ->where('customer_part_id',$customerPartId->id)
                                        ->first();
                                        
                                        
        if($newActual > $maxKanbanQty->kanban_qty){
            return [
                'status' => 'error',
                'message' => 'Tidak boleh lebih dari kuantitas kanban!',
            ];
        }

        try {
            DB::beginTransaction();

            // update loading list detail based on loading list id and customer part
            LoadingListDetail::where('loading_list_id',$loadingList)
                                ->where('customer_part_id', $customerPartId->id)
                                ->update([
                                    'actual_kanban_qty' => $newActual
                                ]);

            DB::commit();

            // push to websocket
            // $this->pushData(true);
            
            return response()->json([
                'status' => 'success',
                'data' => $newActual,
                'message' => 'Data berhasil diupdate!'
            ],200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ],500);
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

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer tidak ditemukan!',
            ], 404);
        }

        $check = LoadingList::where('number', $loadingList)->first();

        if (!$check) {
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
        // get part number length
        $codeLength = strlen($customerPart);

        // check last two digit of partNumber 
        $lastDigit = substr($customerPart, -2);

        // get loading list id
        $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        if(!$loadingListId){
            return [
                'status' => 'llNotExists',
            ];
        } 

        // check part number customer length
        if($codeLength == 12){
            // TMMIN
            if($lastDigit != '00'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
            }
        }else if($codeLength == 10){
            if($loadingListId->customer_id == 14 || $loadingListId->customer_id == 22){
                // SUZUKI
                // $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
            }else{
                if($loadingListId->customer_id == 6){
                    // MMKI
                    $convertedPartNumber = $customerPart;
                }else{
                    // TBINA
                    $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
                }
            }
        }else if($codeLength == 13){
            // SUZUKI
            if($lastDigit != '000'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -3);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -3);
            }
        }else{
            // MMKI fujitrans
            $convertedPartNumber = $customerPart;
        }

        // get customer part id
        $customerPartId = DB::table('customer_parts')
                        ->join('internal_parts', 'internal_parts.id', '=', 'customer_parts.internal_part_id')
                        ->select('customer_parts.id')
                        ->where('internal_parts.part_number', $internalPart)
                        ->where('customer_parts.part_number', $convertedPartNumber)
                        ->first();

        if(!$customerPartId){
            return [
                'status' => 'partNotExists',
                'data' => [
                    'int' => $internalPart,
                    'cust' => $convertedPartNumber
                ]
            ];
        } 

        // check and insert if the loading list exist and customer part does not exist
        $loadingListCheck = LoadingListDetail::firstOrNew([
            'loading_list_id' => $loadingListId->id,
            'customer_part_id' => $customerPartId->id,
        ]);

        if(!$loadingListCheck->exists){
            try {
                LoadingListDetail::create([
                    'loading_list_id' => $loadingListId->id,
                    'customer_part_id' => $customerPartId->id,
                    'kanban_qty' => $kbnQty,
                    'qty_per_kanban' => $qtyPerKanban,
                    'total_qty' => $totalQty,
                    'actual_kanban_qty' => $actualKanbanQty
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
            'message' => 'Detail loading list tersimpan!'
        ], 200);        
    }

    public function kanbanScanned(Request $request)
    {
        $loadingList = $request->loadingList;
        $customerPart = $request->customerPart;
        $internalPart = $request->internalPart;

        // get part number length
        $codeLength = strlen($customerPart);

        // check last two digit of partNumber 
        $lastDigit = substr($customerPart, -2);

        // get loadingList id
        $loadingListId = LoadingList::select('id', 'customer_id')->where('number', $loadingList)->first();
        if(!$loadingListId){
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!'
            ];
        }

        // check part number customer length
        if($codeLength == 12){
            // TMMIN
            if($lastDigit != '00'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
            }
        }else if($codeLength == 10){
            if($loadingListId->customer_id == 14 || $loadingListId->customer_id == 22){
                // SUZUKI
                // $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
            }else{
                if($loadingListId->customer_id == 6){
                    // MMKI
                    $convertedPartNumber = $customerPart;
                }else{
                    // TBINA
                    $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
                }
            }
        }else if($codeLength == 13){
            // SUZUKI
            if($lastDigit != '000'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -3);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -3);
            }
        }else{
            $convertedPartNumber = $customerPart;
        }

        // get customer part id
        $customerPartId = DB::table('customer_parts')
                        ->join('internal_parts', 'internal_parts.id', '=', 'customer_parts.internal_part_id')
                        ->select('customer_parts.id')
                        ->where('internal_parts.part_number', $internalPart)
                        ->where('customer_parts.part_number', $convertedPartNumber)
                        ->first();
                        
        if(!$customerPartId){
            return [
                'status' => 'notExists',
                'message' => 'Part number customer tidak terdaftar!',
                'data' => [
                    'int' => $internalPart,
                    'cust' => $convertedPartNumber
                ]
            ];
        }

        // get current actual kanban qty
        $currentQty = LoadingListDetail::select('kanban_qty','actual_kanban_qty','loading_list_id')
                        ->where('loading_list_id', $loadingListId->id)
                        ->where('customer_part_id', $customerPartId->id)
                        ->first();
                        
        $actualQty = (int) $currentQty->actual_kanban_qty;
        $targetQty = (int) $currentQty->kanban_qty;

        try {
            DB::beginTransaction();

            // check if actual is below target qty
            if($actualQty < $targetQty) {
                // update actual kanban quantity or scanned kanban quantity
                LoadingListDetail::where('loading_list_id', $loadingListId->id)
                                ->where('customer_part_id', $customerPartId->id)
                                ->update([
                                    'actual_kanban_qty' => $actualQty + 1
                                ]);
            }else{
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
            'data' => $actualQty
        ],200);
    }

    public function fetchLoadingList($pds)
    {
        // Fetch LoadingList models with their related models preloaded
        $loadingLists = LoadingList::with([
            'detail.customerPart.internalPart.kanbanAfterPulling' => function ($query) {
                $query->latest();
            },
            'detail.customerPart.internalPart.kanbanAfterPulling.kanban'
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
                    if (!isset($groupedResults[$customerPartId])) {
                        $groupedResults[$customerPartId] = [
                            'customer_part_id' => $customerPartId,
                            'serial_number' => []
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
            'total_series' => $totalSeries // Return the total series count in the response
        ]);
    }

    public function edclDetail($loadingListId, $customerPartId)
    {
        // get loading list detail id
        $loadingListDetail = LoadingListDetail::select('id')
                                ->where('loading_list_id', $loadingListId)
                                ->where('customer_part_id', $customerPartId)
                                ->first();
                                
        if(!$loadingListDetail){
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found!' // Return the total series count in the response
            ]);
        }

        // get skid detail
        $skidData = SkidDetail::where('loading_list_detail_id', $loadingListDetail->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $skidData
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
     * @param \Illuminate\Support\Collection<int, LoadingList> $loadingLists
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
                $label = 'Cycle ' . $n;
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
                'label' => 'Cycle ' . $n,
                'sort' => $n,
            ];
        })->sortBy('sort')->values()->all();

        $byCustomerCycle = $rows->groupBy(function (LoadingList $ll) use ($normalizeCycleKey) {
            $cid = (int) ($ll->customer_id ?? 0);
            $ck = $normalizeCycleKey($ll->cycle);
            return $cid . '|' . $ck;
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
                $key = (int) $cid . '|' . $cycle['cycle_key'];
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
                return $ll->customer_id . '|' . Carbon::parse($ll->delivery_date)->format('Y-m-d');
            })->map(function ($loadingLists) {
                $first = $loadingLists->first();
                $cid = (int) $first->customer_id;
                $dateStr = Carbon::parse($first->delivery_date)->format('Y-m-d');
                $totalKanban = $loadingLists->sum('total_kanban');
                $actualKanban = $loadingLists->sum('actual_kanban');
                $rowId = 'cd-' . $cid . '-' . str_replace('-', '', $dateStr);

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
                            . $actualKanban . ' / ' . $totalKanban .
                        '</div>
                        <div class="progress" data-height="20" style="height: 18px;">
                            <div class="progress-bar" role="progressbar"
                                style="width:' . $progressPercentage . '%; background-color: ' . $statusClass . ' !important"
                                aria-valuenow="' . $progressPercentage . '" aria-valuemin="0" aria-valuemax="100">
                                <small class="text-white font-weight-bold">' . $progressPercentage . '%</small>
                            </div>
                        </div>';
                })
                ->addColumn('loading_and_status', function ($group) {
                    $buttonStyle = 'style="min-width: 120px; padding: 8px 12px; font-size: 13px; font-weight: 500; text-align: center; white-space: nowrap;"';

                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;

                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusButton = '<button type="button" class="btn btn-success" ' . $buttonStyle . '>
                                            <i class="fas fa-check mr-1"></i>
                                            COMPLETE
                                        </button>';
                    } elseif ($actualKanban > 0) {
                        $statusButton = '<button type="button" class="btn btn-outline-warning" ' . $buttonStyle . '>
                                            IN PROGRESS
                                        </button>';
                    } else {
                        $statusButton = '<button type="button" class="btn btn-outline-danger" ' . $buttonStyle . '>
                                            INCOMPLETE
                                        </button>';
                    }

                    return '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; align-items: center;">' . $statusButton . '</div>';
                })
                ->setRowId(function ($group) {
                    return 'row-' . $group->id;
                })
                ->rawColumns(['loading_and_status', 'progress', 'customer'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: ' . $e->getMessage(),
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
        ]);
    }

    public function getMasterCycles()
    {
        $rows = MasterCycle::query()
            ->with(['customer:id,name'])
            ->orderBy('time')
            ->orderBy('cycle_name')
            ->get()
            ->map(function (MasterCycle $m) {
                return [
                    'id' => $m->id,
                    'customer_id' => $m->customer_id,
                    'customer_name' => optional($m->customer)->name,
                    'cycle_name' => $m->cycle_name,
                    'time' => Carbon::parse($m->time)->format('H:i'),
                    'prep_end_time' => $m->prep_end_time
                        ? Carbon::parse($m->prep_end_time)->format('H:i')
                        : null,
                    'truck_time' => $m->truck_time
                        ? Carbon::parse($m->truck_time)->format('H:i')
                        : null,
                ];
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
            'truck_time' => 'required|date_format:H:i',
        ]);

        $validated['cycle_name'] = trim($validated['cycle_name']);
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
            'truck_time' => 'required|date_format:H:i',
        ]);

        $validated['cycle_name'] = trim($validated['cycle_name']);
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

        // Gunakan sumber data dan filter yang sama dengan getLoadingList.
        $baseQuery = LoadingList::with(['customer'])
            ->withSum('detail as total_kanban', 'kanban_qty')
            ->withSum('detail as actual_kanban', 'actual_kanban_qty')
            ->whereNotNull('customer_id')
            ->latest();

        if ($request->filled('customer')) {
            $customerName = trim((string) $request->get('customer'));
            $baseQuery->whereHas('customer', function ($q) use ($customerName) {
                $q->where('name', $customerName);
            });
        }

        if (! empty($validated['customer_id'] ?? null)) {
            $baseQuery->where('customer_id', (int) $validated['customer_id']);
        }

        if ($request->filled('cycle')) {
            $cycleRaw = trim((string) $request->get('cycle'));
            $cycleNumber = null;
            if (preg_match('/\d+/', $cycleRaw, $m)) {
                $cycleNumber = $m[0];
            }

            $baseQuery->where(function ($q) use ($cycleRaw, $cycleNumber) {
                $q->where('cycle', $cycleRaw);
                if ($cycleNumber !== null) {
                    $q->orWhere('cycle', $cycleNumber)
                        ->orWhere('cycle', 'Cycle '.$cycleNumber)
                        ->orWhere('cycle', 'cycle '.$cycleNumber);
                }
            });
        }

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $dateFilter = $validated['delivery_date'] ?? ($validated['date'] ?? null);

        // Backward compatible: jika range kosong, fallback ke filter single-date lama.
        if (empty($dateFrom) && empty($dateTo) && ! empty($dateFilter)) {
            $dateFrom = $dateFilter;
            $dateTo = $dateFilter;
        }

        if (! empty($dateFrom) || ! empty($dateTo)) {
            $start = ! empty($dateFrom) ? Carbon::parse($dateFrom)->startOfDay() : null;
            $end = ! empty($dateTo) ? Carbon::parse($dateTo)->endOfDay() : null;

            // Jika user terbalik isi from/to, tukar agar tetap valid sebagai rentang.
            if ($start && $end && $start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            if ($start && $end) {
                $baseQuery->whereBetween('delivery_date', [$start, $end]);
            } elseif ($start) {
                $baseQuery->where('delivery_date', '>=', $start);
            } elseif ($end) {
                $baseQuery->where('delivery_date', '<=', $end);
            }
        }

        $hasSpecificFilter = $request->filled('customer')
            || ! empty($validated['customer_id'] ?? null)
            || $request->filled('cycle')
            || ! empty($dateFrom)
            || ! empty($dateTo)
            || ! empty($dateFilter);

        // Saat mode "Semua", jangan dibatasi agar hasil konsisten dengan data aktual.
        $rowLimit = $hasSpecificFilter ? 50000 : null;

        $rawLists = (clone $baseQuery)
            ->when($rowLimit !== null, function ($q) use ($rowLimit) {
                $q->take($rowLimit);
            })
            ->get();

        $groupedData = $rawLists
            ->groupBy('pds_number')
            ->map(function ($loadingLists, $pdsNumber) {
                /** @var LoadingList $firstLoadingList */
                $firstLoadingList = $loadingLists->first();
                $totalKanban = (int) $loadingLists->sum('total_kanban');
                $actualKanban = (int) $loadingLists->sum('actual_kanban');
                $cycle = $loadingLists->groupBy('cycle')->sortByDesc(function ($items) {
                    return $items->count();
                })->keys()->first();

                return (object) [
                    'id' => 'pds-'.$pdsNumber,
                    'pds_number' => $pdsNumber,
                    'customer' => $firstLoadingList->customer,
                    'customer_id' => (int) $firstLoadingList->customer_id,
                    'cycle' => $this->normalizeCycleNumber($cycle),
                    'delivery_date' => $loadingLists->min('delivery_date'),
                    'total_kanban' => $totalKanban,
                    'actual_kanban' => $actualKanban,
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
            return [
                'id' => $m->id,
                'customer_id' => (int) $m->customer_id,
                'cycle_name' => $this->normalizeCycleNumber($m->cycle_name),
                'time' => Carbon::parse($m->time)->format('H:i'),
                'prep_end_time' => $m->prep_end_time
                    ? Carbon::parse($m->prep_end_time)->format('H:i')
                    : null,
                'truck_time' => $m->truck_time
                    ? Carbon::parse($m->truck_time)->format('H:i')
                    : null,
            ];
        });

        $rows = $groupedData
            ->groupBy(function ($pds) {
                $date = $pds->delivery_date
                    ? Carbon::parse($pds->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');
                return $pds->customer_id.'|'.$date.'|'.$pds->cycle;
            })
            ->map(function ($pdsGroup) use ($masterMeta) {
                $first = $pdsGroup->first();
                $dateStr = $first->delivery_date
                    ? Carbon::parse($first->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');
                $master = $masterMeta->first(function (array $m) use ($first) {
                    return $m['customer_id'] === (int) $first->customer_id
                        && $m['cycle_name'] === (string) $first->cycle;
                });

                $llCount = $pdsGroup->count();
                $completeCount = $pdsGroup->where('is_complete', true)->count();
                $totalTarget = (int) $pdsGroup->sum('total_kanban');
                $totalDone = (int) $pdsGroup->sum('actual_kanban');
                $progressPct = $llCount > 0
                    ? round(($completeCount / $llCount) * 100, 1)
                    : 0.0;

                $cycleTime = $master ? ($master['time'] ?? '06:00') : '06:00';
                $prepEndTime = ($master && isset($master['prep_end_time']) && $master['prep_end_time'] !== null)
                    ? $master['prep_end_time']
                    : $cycleTime;
                $truckTime = ($master && isset($master['truck_time']) && $master['truck_time'] !== null)
                    ? $master['truck_time']
                    : null;

                return [
                    'customer_id' => (int) $first->customer_id,
                    'customer_name' => optional($first->customer)->name ?? '-',
                    'delivery_date' => $dateStr,
                    'master_cycle_id' => $master ? ($master['id'] ?? null) : null,
                    'cycle_name' => (string) $first->cycle,
                    'cycle_time' => $cycleTime,
                    'prep_end_time' => $prepEndTime,
                    'truck_time' => $truckTime,
                    'mapping_source' => $master ? 'master_cycle_name' : 'loading_list_only',
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
                ];
            })
            ->sortBy([
                ['customer_name', 'asc'],
                ['delivery_date', 'asc'],
                ['cycle_time', 'asc'],
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
            ->map(function ($bucket) use ($masterMeta) {
                $first = $bucket->first();
                $dateStr = $first->delivery_date
                    ? Carbon::parse($first->delivery_date)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d');

                $master = $masterMeta->first(function (array $m) use ($first) {
                    return $m['customer_id'] === (int) $first->customer_id
                        && $m['cycle_name'] === (string) $first->cycle;
                });

                $prepStart = $master['time'] ?? '06:00';
                $prepEnd = $master['prep_end_time'] ?? null;
                $truckTime = $master['truck_time'] ?? null;

                $start = Carbon::parse($dateStr.' '.$prepStart);
                $end = $prepEnd ? Carbon::parse($dateStr.' '.$prepEnd) : $start->copy()->addMinutes(45);
                if ($end->lessThanOrEqualTo($start)) {
                    $end = $end->addDay();
                }

                $target = (int) $bucket->sum('total_kanban');
                $done = (int) $bucket->sum('actual_kanban');
                $progress = $target > 0 ? round(($done / $target) * 100, 1) : 0.0;

                return [
                    'customer_id' => (int) $first->customer_id,
                    'customer_name' => optional($first->customer)->name ?? '-',
                    'delivery_date' => $dateStr,
                    'cycle_name' => (string) $first->cycle,
                    'start_at' => $start->toIso8601String(),
                    'end_at' => $end->toIso8601String(),
                    'prep_time' => $prepStart,
                    'prep_end_time' => $prepEnd,
                    'truck_time' => $truckTime,
                    'total_target' => $target,
                    'total_done' => $done,
                    'progress_pct' => min(100.0, max(0.0, $progress)),
                    'is_complete' => $target > 0 && $done >= $target,
                ];
            })
            ->sortBy([
                ['customer_name', 'asc'],
                ['start_at', 'asc'],
            ])
            ->values()
            ->all();

        $hint = null;
        if ($groupedData->isEmpty()) {
            $hint = 'Tidak ada loading list untuk filter ini. Samakan filter dengan halaman loading list.';
        }

        return response()->json([
            'rows' => $rows,
            'weekly_points' => $weeklyPoints,
            'master_cycles' => $masters->map(function (MasterCycle $m) {
                return [
                    'id' => $m->id,
                    'customer_id' => $m->customer_id,
                    'customer_name' => optional($m->customer)->name,
                    'cycle_name' => $m->cycle_name,
                    'time' => Carbon::parse($m->time)->format('H:i'),
                    'prep_end_time' => $m->prep_end_time
                        ? Carbon::parse($m->prep_end_time)->format('H:i')
                        : null,
                    'truck_time' => $m->truck_time
                        ? Carbon::parse($m->truck_time)->format('H:i')
                        : null,
                ];
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
                'is_truncated' => $rowLimit !== null ? ($rawLists->count() >= $rowLimit) : false,
                'hint' => $hint,
            ],
        ]);
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

        $toTimelineMinutes = function (?string $clock): ?int {
            $raw = trim((string) ($clock ?? ''));
            if ($raw === '' || !preg_match('/^\d{1,2}:\d{2}/', $raw)) {
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
            return response()->json([
                'message' => 'Belum ada bar delay yang melewati garis Finish Preparation.',
                'already_sent' => false,
            ]);
        }

        $hashPayload = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'finish_offset_hours' => $finishOffsetHours,
            'rows' => $pendingRows->toArray(),
        ];
        $dedupeKey = 'delivery_wa_unfinished_'.md5(json_encode($hashPayload));
        if (Cache::has($dedupeKey)) {
            return response()->json([
                'message' => 'Notifikasi untuk data yang sama sudah pernah dikirim.',
                'already_sent' => true,
            ]);
        }

        $token = env('FASTWA_API_KEY', '793D30579A77D4A0E12648872BFBB085');
        $phone = env('FASTWA_PHONE', '085971684756');
        $url = env('FASTWA_SEND_URL', 'https://app.fastwa.com/api/v1/4D9AF7CE224B91C9CE14FFDDB55D248D/send_text');

        $period = trim((string) ($validated['date_from'] ?? ''));
        if (!empty($validated['date_to']) && $validated['date_to'] !== $period) {
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
            if (!empty($row['cycle_time'])) {
                $lines[] = '   Time: '.$row['cycle_time'];
            }
            $lines[] = '   Progress: '.$row['progress_pct'].'% ('.$row['total_done'].' / '.$row['total_target'].')';
            $lines[] = '   Jumlah LL: '.$row['ll_count'];

            $partShortages = $this->resolveDeliveryPartShortages(
                $row,
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null
            );
            if (!empty($partShortages)) {
                $lines[] = '   Detail Part Kurang:';
                foreach ($partShortages as $part) {
                    $lines[] = '   - part_no: '.$part['part_no'].' | part_name: '.$part['part_name'].' | qty_kurang: '.$part['qty_kurang'];
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

        if (!empty($curlError)) {
            return response()->json([
                'message' => 'Gagal kirim notifikasi WhatsApp.',
                'error' => $curlError,
            ], 500);
        }

        if ($statusCode >= 400) {
            return response()->json([
                'message' => 'FastWA mengembalikan error.',
                'status_code' => $statusCode,
                'response' => $response,
            ], 502);
        }

        Cache::put($dedupeKey, true, now()->addMinutes(30));

        return response()->json([
            'message' => 'Notifikasi WhatsApp terkirim.',
            'status_code' => $statusCode,
            'response' => $response,
        ]);
    }

    /**
     * Ambil top part yang masih kurang untuk bucket customer+cycle pada periode filter.
     *
     * @param  array<string, mixed>  $row
     * @return array<int, array{part_no: string, part_name: string, qty_kurang: int}>
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
            ->leftJoin('internal_parts as ip', 'ip.id', '=', 'cp.internal_part_id')
            ->where('ll.customer_id', $customerId)
            ->where(function ($q) use ($cycleRaw, $cycleNumber) {
                $q->where('ll.cycle', $cycleRaw);
                if ($cycleNumber !== null) {
                    $q->orWhere('ll.cycle', $cycleNumber)
                        ->orWhere('ll.cycle', 'Cycle '.$cycleNumber)
                        ->orWhere('ll.cycle', 'cycle '.$cycleNumber);
                }
            });

        if (!empty($dateFrom) || !empty($dateTo)) {
            $start = !empty($dateFrom) ? Carbon::parse($dateFrom)->startOfDay() : null;
            $end = !empty($dateTo) ? Carbon::parse($dateTo)->endOfDay() : null;
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
                COALESCE(ip.part_name, '-') as part_name,
                SUM(COALESCE(lld.kanban_qty, 0)) as total_target,
                SUM(COALESCE(lld.actual_kanban_qty, 0)) as total_done
            ")
            ->groupBy('cp.part_number', 'ip.part_name')
            ->get();

        return collect($rows)
            ->map(function ($part) {
                $target = (int) ($part->total_target ?? 0);
                $done = (int) ($part->total_done ?? 0);
                $shortage = max(0, $target - $done);
                return [
                    'part_no' => (string) ($part->part_no ?? '-'),
                    'part_name' => (string) ($part->part_name ?? '-'),
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

}
