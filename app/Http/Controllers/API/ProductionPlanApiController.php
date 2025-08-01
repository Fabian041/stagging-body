<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductionPlan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class ProductionPlanApiController extends Controller
{
    public function updateQty(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'dn_number' => 'required|string',
            'back_no' => 'required|string',
            'direct_pulling_qty' => 'sometimes|integer|min:0',
            'stock_chute_qty' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find matching records from today
        $today = now()->format('Y-m-d');
        // $plans = ProductionPlan::where('plan_date', $today)
        //     ->where('dn_number', $request->dn_number)
        //     ->where('back_no', $request->back_no)
        //     ->get();
            
        $plans = ProductionPlan::where('dn_number', $request->dn_number)
            ->where('back_no', $request->back_no)
            ->get();

        if ($plans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching production plans found for today',
                'data' => [
                    'dn_number' => $request->dn_number
                ]
            ], 404);
        }

        // Prepare incremental update data
        $updateData = [];
        if ($request->has('direct_pulling_qty')) {
            $updateData['direct_pulling_qty'] = DB::raw('direct_pulling_qty + ' . $request->direct_pulling_qty);
        }
        if ($request->has('stock_chute_qty')) {
            $updateData['stock_chute_qty'] = DB::raw('stock_chute_qty + ' . $request->stock_chute_qty);
        }

        // Validate quantities don't exceed order_qty after increment
        foreach ($plans as $plan) {
            if (isset($updateData['direct_pulling_qty']) && 
                ($plan->direct_pulling_qty + $request->direct_pulling_qty) > $plan->order_qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direct pulling quantity cannot exceed order quantity',
                    'current_qty' => $plan->direct_pulling_qty,
                    'attempted_addition' => $request->direct_pulling_qty,
                    'max_allowed' => $plan->order_qty - $plan->direct_pulling_qty
                ], 422);
            }

            if (isset($updateData['stock_chute_qty']) && 
                ($plan->stock_chute_qty + $request->stock_chute_qty) > $plan->order_qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock chute quantity cannot exceed order quantity',
                    'current_qty' => $plan->stock_chute_qty,
                    'attempted_addition' => $request->stock_chute_qty,
                    'max_allowed' => $plan->order_qty - $plan->stock_chute_qty
                ], 422);
            }
        }

        // Update all matching records with increment
        $updated = ProductionPlan::whereIn('id', $plans->pluck('id'))
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Updated ' . $updated . ' record(s)',
            'data' => $plans->fresh()
        ]);
    }
}
