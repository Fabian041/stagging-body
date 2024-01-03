<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\Customer;
use App\Models\LoadingList;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Http\Request;
use App\Models\LoadingListDetail;
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
        $input = LoadingList::with('detail')->latest()->take(500)->get();

        return DataTables::of($input)
                ->addColumn('customer', function ($loadingList) {
                    return $loadingList->customer->name;
                })
                ->addColumn('detail', function($loadingList){

                    $totalKanban = 0;
                    $actualKanban = 0;
                    foreach ($loadingList->detail as $detail) {
                        $totalKanban += $detail->kanban_qty;
                        $actualKanban += $detail->actual_kanban_qty;
                    }

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
                    // Calculate progress percentage
                    $totalKanban = 0;
                    $actualKanban = 0;
                    foreach ($loadingList->detail as $detail) {
                        $totalKanban += $detail->kanban_qty;
                        $actualKanban += $detail->actual_kanban_qty;
                    }
                    $progressPercentage = ($totalKanban > 0) ? round(($actualKanban / $totalKanban) * 100) : 0;

                    // Determine the status
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
    
                    // Create a progress bar dynamically
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
                    return $loadingList->customerPart->back_number;
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
                ->addColumn('edit', function($row) use ($input){

                    $btn = '<button class="btn btn-icon btn-primary edit" id="edit"><i class="far fa-edit"></i></button>
                    <button class="btn btn-icon btn-success save mb-1" style="display: none"><i
                            class="fas fa-check"></i></button>
                    <button class="btn btn-icon btn-danger cancel" style="display: none"><i
                            class="fas fa-times"></i></button>';

                    return $btn;

                })
                ->rawColumns(['cust_partno','actual_kbn_qty','edit'])
                ->toJson();
    }

    public function editLoadingListDetail($loadingList, $customerPart, $newActual)
    {
        // get customer part id
        $customerPartId = CustomerPart::select('id')->where('part_number',$customerPart)->first();

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
                'message' => $th,
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
        $currentQty = LoadingListDetail::select('actual_kanban_qty','loading_list_id')
                        ->where('loading_list_id', $loadingListId->id)
                        ->where('customer_part_id', $customerPartId->id)
                        ->first();
                        
        $currentQty = (int) $currentQty->actual_kanban_qty;

        try {
            DB::beginTransaction();

            // update actual kanban quantity or scanned kanban quantity
            LoadingListDetail::where('loading_list_id', $loadingListId->id)
                            ->where('customer_part_id', $customerPartId->id)
                            ->update([
                                'actual_kanban_qty' => $currentQty + 1
                            ]);

            // push to websocket
            // $this->pushData(true);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ],500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $currentQty
        ],200);
    }

    public function fetchLoadingList($pds)
    {
        $total_kanban = 0;
        $total_actual = 0;
        
        $data = LoadingList::join('loading_list_details', 'loading_list_details.loading_list_id', '=', 'loading_lists.id')
                ->select('loading_list_details.kanban_qty', 'loading_list_details.actual_kanban_qty')
                ->where('loading_lists.pds_number', $pds)
                ->get();

        foreach ($data as $datum){
            $total_kanban += $datum->kanban_qty;
            $total_actual += $datum->actual_kanban_qty;
        }

        return response()->json([
            'status' => 'success',
            'kanban_qty' => $total_kanban,
            'actual_kanban_qty' => $total_actual
        ]);
    }
}
