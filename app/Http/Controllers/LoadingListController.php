<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\Kanban;
use App\Models\Customer;
use App\Models\Mutation;
use App\Models\SkidDetail;
use App\Models\LoadingList;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Models\LoadingListDetail;
use App\Models\KanbanAfterPulling;
use Illuminate\Support\Facades\DB;
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
    public function getLoadingList()
    {
        try {
            // Group loading lists by pds_number and aggregate the data
            $groupedData = LoadingList::with(['customer'])
                ->withSum('detail as total_kanban', 'kanban_qty')
                ->withSum('detail as actual_kanban', 'actual_kanban_qty')
                ->latest()
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
                    // Tombol Loading Lists
                    $loadingBtn = '<button class="btn btn-info text-white mr-2 show-loading-lists" data-pds="' . $group->pds_number . '">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Detail
                                </button>';

                    // Tombol Status
                    $totalKanban = $group->total_kanban ?? 0;
                    $actualKanban = $group->actual_kanban ?? 0;

                    if ($actualKanban >= $totalKanban && $totalKanban > 0) {
                        $statusButton = '<button class="btn btn-success">
                                            <i class="fas fa-check" style="padding-right: 1px"></i>
                                            COMPLETE
                                        </button>';
                    } elseif ($actualKanban > 0) {
                        $statusButton = '<button class="btn btn-outline-warning">
                                            INPROGRESS
                                        </button>';
                    } else {
                        $statusButton = '<button class="btn btn-outline-danger">
                                            INCOMPLETE
                                        </button>';
                    }

                    return $loadingBtn . $statusButton;
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
        // Eager load untuk menghindari N+1
        $input = LoadingListDetail::with([
            'customerPart.internalPart'
        ])->where('loading_list_id', $loadingList->id)->get();

        return DataTables::of($input)
            ->addColumn('part_name', function ($loadingList) {
                return optional($loadingList->customerPart->internalPart)->part_name ?? '-';
            })
            ->addColumn('cust_partno', function ($loadingList) {
                return '<span class="customerPart">' . optional($loadingList->customerPart)->part_number . '</span>';
            })
            ->addColumn('int_partno', function ($loadingList) {
                return optional($loadingList->customerPart->internalPart)->part_number ?? '-';
            })
            ->addColumn('cust_backno', function ($loadingList) {
                return '<span class="backNumber">' . optional($loadingList->customerPart)->back_number . '</span>';
            })
            ->addColumn('int_backno', function ($loadingList) {
                return optional($loadingList->customerPart->internalPart)->back_number ?? '-';
            })
            ->addColumn('kbn_qty', function ($loadingList) {
                return $loadingList->kanban_qty;
            })
            ->addColumn('actual_kbn_qty', function ($loadingList) {
                return '<span class="actual">' . $loadingList->actual_kanban_qty . '</span>
                    <input id="editActual" class="form-control editActual" type="number"
                    value="' . $loadingList->actual_kanban_qty . '" data-width="100"
                    style="border-radius:6px; display:none">';
            })
            ->addColumn('pulling_date', function ($loadingList) {
                return $loadingList->updated_at != $loadingList->created_at
                    ? $loadingList->updated_at->format('Y-m-d H:i')
                    : '<span class="text-danger">N/A</span>';
            })
            ->addColumn('serial_number', function ($loadingList) {
                $internalPartId = optional($loadingList->customerPart->internalPart)->id;
                $updateTime = $loadingList->updated_at->format('Y-m-d H:i');

                if (!$internalPartId) {
                    return '<span class="text-danger">N/A</span>';
                }

                $serials = Mutation::select('serial_number')
                    ->where('internal_part_id', $internalPartId)
                    ->where('type', 'checkout')
                    ->where('date', 'like', $updateTime . '%')
                    ->pluck('serial_number')
                    ->toArray();

                $loadingList->temp_serial_numbers = $serials;

                return !empty($serials)
                    ? implode(', ', $serials)
                    : '<span class="text-danger">N/A</span>';
            })
            ->addColumn('prod_date', function ($loadingList) {
                $internalPartId = optional($loadingList->customerPart->internalPart)->id;
                $serialNumbers = $loadingList->temp_serial_numbers ?? [];

                if (!$internalPartId || empty($serialNumbers)) {
                    return '<span class="text-danger">N/A</span>';
                }

                $data = Mutation::select('serial_number', 'date')
                    ->where('internal_part_id', $internalPartId)
                    ->where('type', 'supply')
                    ->whereIn('serial_number', $serialNumbers)
                    ->where('date', '<=', $loadingList->updated_at)
                    ->orderBy('date', 'asc')
                    ->get();

                $serialDates = $data->pluck('date', 'serial_number')->toArray();

                $output = array_map(function ($serial) use ($serialDates) {
                    $date = $serialDates[$serial] ?? 'N/A';
                    return "[$serial] - [$date]";
                }, $serialNumbers);

                return implode('<br>', $output);
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
                'serial_number',
                'prod_date'
            ])
            ->toJson();
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
            if($loadingListId->customer_id == 14){
                // SUZUKI
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
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
            if($loadingListId->customer_id == 14){
                // SUZUKI
                $convertedPartNumber = substr_replace($customerPart, '-', 5, 0) . '-' . '000';
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
                'message' => 'Part number customer tidak terdaftar!'
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

}
