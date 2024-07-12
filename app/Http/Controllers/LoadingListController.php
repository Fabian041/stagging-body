<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\Kanban;
use App\Models\Customer;
use App\Models\Mutation;
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

    public function getLoadingList()
    {
        $input = LoadingList::with(['detail', 'customer'])->latest()->take(500)->get();

        return DataTables::of($input)
            ->addColumn('customer', function ($loadingList) {
                return $loadingList->customer->name;
            })
            ->addColumn('detail', function($loadingList) {
                $totalKanban = $loadingList->detail->sum('kanban_qty');
                $actualKanban = $loadingList->detail->sum('actual_kanban_qty');

                $detailButton = '<a href="/loading-list/'. $loadingList->id.'" class="btn btn-info text-white mr-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    DETAIL
                                </a>';

                if ($actualKanban >= $totalKanban) {
                    $buttons = $detailButton . '<button class="btn btn-success">
                                                    <i class="fas fa-check" style="padding-right: 1px"></i>
                                                    COMPLETE
                                                </button>';
                } elseif ($actualKanban < $totalKanban && $actualKanban > 0) {
                    $buttons = $detailButton . '<button class="btn btn-outline-warning">
                                                    INPROGRESS
                                                </button>';
                } elseif ($actualKanban == 0) {
                    $buttons = $detailButton . '<button class="btn btn-outline-danger">
                                                    INCOMPLETE
                                                </button>';
                }

                return $buttons;
            })
            ->addColumn('progress', function ($loadingList) {
                $totalKanban = $loadingList->detail->sum('kanban_qty');
                $actualKanban = $loadingList->detail->sum('actual_kanban_qty');
                $progressPercentage = ($totalKanban > 0) ? round(($actualKanban / $totalKanban) * 100) : 0;

                $statusClass = '';
                $statusText = '';

                if ($actualKanban >= $totalKanban) {
                    $statusClass = 'lightgreen';
                    $statusText = 'COMPLETE';
                } elseif ($actualKanban == 0) {
                    $statusClass = 'red';
                    $statusText = 'INCOMPLETE';
                } else {
                    $statusClass = 'orange';
                    $statusText = 'INPROGRESS';
                }

                $progress = '
                <div class="text-small float-right font-weight-bold text-muted ml-3">'. $actualKanban .' / '.$totalKanban .'</div>
                                <div class="font-weight-bold mb-1" style="color: white">-</div>
                <div class="progress" data-height="20" style="height: 15px;">
                    <div class="progress-bar" role="progressbar" data-width="'.$progressPercentage .'"
                        aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                        style="width:'. $progressPercentage .'%; background-color: '. $statusClass .' !important">
                    </div>
                </div>';

                return $progress;
            })
            ->rawColumns(['detail', 'progress', 'customer'])
            ->make(true);
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
        $input = LoadingListDetail::where('loading_list_id', $loadingList->id)->get();

        return DataTables::of($input)
                ->addColumn('part_name', function ($loadingList) {
                    return $loadingList->customerPart->internalPart->part_name;
                })
                ->addColumn('cust_partno', function ($loadingList) {
                    $custPart = '<span class="customerPart">'. $loadingList->customerPart->part_number .'</span>';

                    return $custPart;
                })
                ->addColumn('int_partno', function ($loadingList) {
                    return $loadingList->customerPart->internalPart->part_number;
                })
                ->addColumn('cust_backno', function ($loadingList) {
                    $custBackPart = '<span class="backNumber">'. $loadingList->customerPart->back_number .'</span>';

                    return $custBackPart;
                })
                ->addColumn('int_backno', function ($loadingList) {
                    return $loadingList->customerPart->internalPart->back_number;
                })
                ->addColumn('kbn_qty', function ($loadingList) {
                    return $loadingList->kanban_qty;
                })
                ->addColumn('actual_kbn_qty', function ($loadingList) {

                    $actual = '<span class="actual">'. $loadingList->actual_kanban_qty .' </span>
                        <input id="editActual" class="form-control editActual" type="number"
                        value="'.$loadingList->actual_kanban_qty.'" data-width="100"
                        style="border-radius:6px; display:none">';

                    return $actual;
                })
                ->addColumn('pulling_date', function ($loadingList) {

                    return $loadingList->updated_at != $loadingList->created_at 
                    ? $loadingList->updated_at->format('Y-m-d H:i:s') 
                    :  '<span class="text-danger"> N/A </span>';
                })
                ->addColumn('serial_number', function ($loadingList) {
                    $datum = Mutation::select('serial_number')
                        ->where('internal_part_id', $loadingList->customerPart->internalPart->id)
                        ->where('type', 'checkout')
                        ->where('date', 'LIKE', $loadingList->updated_at->format('Y-m-d H:i') . '%')
                        ->get();
                
                    $serialNumbers = $datum->pluck('serial_number')->toArray();
                    
                    return !empty($serialNumbers) ? implode(', ', $serialNumbers) : '<span class="text-danger"> N/A </span>';
                })
                ->addColumn('edit', function($row) use ($input){

                    $btn = '<button class="btn btn-icon btn-primary edit" id="edit"><i class="far fa-edit"></i></button>
                    <button class="btn btn-icon btn-success save mb-1" style="display: none"><i
                            class="fas fa-check"></i></button>
                    <button class="btn btn-icon btn-danger cancel" style="display: none"><i
                            class="fas fa-times"></i></button>';

                    return $btn;

                })
                ->rawColumns(['cust_partno','cust_backno','actual_kbn_qty','edit', 'pulling_date', 'serial_number'])
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
        // get customer by customer code
        $customer = Customer::select('id')->where('code', $customerCode)->first();

        $check = LoadingList::where('number', $loadingList)->first();
        if(!$check){
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
                DB::rollback();
                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'loading list tersimpan!'
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

}
