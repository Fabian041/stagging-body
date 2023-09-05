<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoadingList;
use App\Models\CustomerPart;
use Illuminate\Http\Request;
use App\Models\LoadingListDetail;
use Illuminate\Support\Facades\DB;

class LoadingListController extends Controller
{
    public function index()
    {
        return view('pages.loadingList',[
            'loadingLists' => LoadingList::all(),
            'customers' => Customer::all()
        ]);
    }

    public function detail(LoadingList $loadingList)
    {
        $loadingListDetail = DB::table('loading_lists')
                            ->join('loading_list_details', 'loading_lists.id', 'loading_list_details.loading_list_id')
                            ->join('customers', 'customers.id', 'loading_lists.customer_id')
                            ->select('loading_lists.number', 'loading_lists.pds_number', 'loading_lists.cycle', 'loading_lists.delivery_date', 'loading_lists.shipping_date','customers.name')
                            ->where('loading_list_details.loading_list_id', $loadingList->id)
                            ->first();

        $detail = DB::table('loading_lists')
                        ->join('loading_list_details', 'loading_lists.id', 'loading_list_details.loading_list_id')
                        ->join('customers', 'customers.id', 'loading_lists.customer_id')
                        ->join('customer_parts', 'customer_parts.id', 'loading_list_details.customer_part_id')
                        ->join('internal_parts', 'internal_parts.id', 'customer_parts.internal_part_id')
                        ->select('loading_list_details.kanban_qty', 'loading_list_details.actual_kanban_qty', 'customers.name', 'customer_parts.part_number as pn_customer', 'customer_parts.back_number as bn_customer', 'internal_parts.part_number as pn_internal', 'internal_parts.back_number as bn_internal', 'internal_parts.part_name')
                        ->where('loading_list_details.loading_list_id', $loadingList->id)
                        ->get();

        return view('pages.loadingListDetail',[
            'customers' => Customer::all(),
            'loadingListDetail' => $loadingListDetail,
            'details' => $detail
        ]);
    }
    
    public function store($loadingList, $pds, $cycle, $customerCode, $shippingDate, $deliveryDate)
    {
        // get customer by customer code
        $customer = Customer::select('id')->where('code', $customerCode)->first();

        $check = LoadingList::where('number', $loadingList)->first();
        if(!$check){
            try {
                LoadingList::create([
                    'number' => $loadingList,
                    'pds_number' => $pds,
                    'cycle' => $cycle,
                    'customer_id' => $customer->id,
                    'delivery_date' => $deliveryDate,
                    'shipping_date' => $shippingDate,
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
            'message' => 'loading list tersimpan!'
        ], 200);
    }

    public function storeDetail($loadingList, $customerPart, $kbnQty, $qtyPerKanban, $totalQty, $actualKanbanQty)
    {
        // get part number length
        $codeLength = strlen($customerPart);

        // check last two digit of partNumber 
        $lastDigit = substr($customerPart, -2);

        // check part number customer length
        if($codeLength == 12){
            // TMMIN
            if($lastDigit != '00'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
            }
        }else if($codeLength == 10){
            // TBINA
            $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
        }

        // get loading list id
        $loadingListId = LoadingList::select('id')->where('number', $loadingList)->first();
        if(!$loadingListId){
            return [
                'status' => 'llNotExists',
            ];
        } 

        // get customer part id
        $customerPartId = CustomerPart::select('id')->where('part_number', $convertedPartNumber)->first();
        if(!$customerPartId){
            return [
                'status' => 'partNotExists',
            ];
        } 

        // check and insert if the loading list exist and customer part does not exist
        $loadingListCheck = LoadingListDetail::where('loading_list_id', $loadingListId->id)
                ->where('customer_part_id', $customerPartId->id)
                ->get();

        if(!$loadingListCheck == false){
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

        // get part number length
        $codeLength = strlen($customerPart);

        // check last two digit of partNumber 
        $lastDigit = substr($customerPart, -2);

        // check part number customer length
        if($codeLength == 12){
            // TMMIN
            if($lastDigit != '00'){
                $convertedPartNumber = substr($customerPart, 0, 5) . '-' . substr($customerPart, 5, 5) . '-' . substr($customerPart, -2);
            }else{
                $convertedPartNumber = substr(substr_replace($customerPart, '-', 5, 0), 0, -2);
            }
        }else if($codeLength == 10){
            // TBINA
            $convertedPartNumber = substr_replace($customerPart, '-', 5, 0);
        }

        // get loadingList id
        $loadingListId = LoadingList::select('id')->where('number', $loadingList)->first();
        if(!$loadingListId){
            return [
                'status' => 'notExists',
                'message' => 'Loading list tidak terdaftar!'
            ];
        }
        // get customer part id
        $customerPartId = CustomerPart::select('id')->where('part_number', $convertedPartNumber)->first();
        if(!$customerPartId){
            return [
                'status' => 'notExists',
                'message' => 'Part number customer tidak terdaftar!'
            ];
        }

        // get current actual kanban qty
        $currentQty = LoadingListDetail::select('actual_kanban_qty')
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
            'data' => $customerPartId->id
        ],200);
    }
}
