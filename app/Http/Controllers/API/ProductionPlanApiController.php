<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
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

        // Find matching records
        $plans = ProductionPlan::where('dn_number', $request->dn_number)
            ->where('back_no', $request->back_no)
            ->get();

        if ($plans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching production plans found',
                'data' => [
                    'dn_number' => $request->dn_number,
                    'b_number' => $request->back_no
                ]
            ], 404);
        }

        // Prepare incremental update data
        $updateData = [];
        $shouldUpdateTimestamps = false;

        if ($request->has('direct_pulling_qty')) {
            $updateData['direct_pulling_qty'] = DB::raw('direct_pulling_qty + ' . $request->direct_pulling_qty);
            
            // Check if this is the first update (current qty is 0)
            foreach ($plans as $plan) {
                if ($plan->direct_pulling_qty == 0 && $request->direct_pulling_qty > 0) {
                    $shouldUpdateTimestamps = true;
                    break;
                }
            }
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

        // If this is the first update, set working_start to current time
        if ($shouldUpdateTimestamps) {
            $currentTime = now();
            $updateData['working_start'] = $currentTime->format('H:i');
        
            foreach ($plans as $plan) {
                // Hitung total waktu produksi (detik)
                [$prodMin, $prodSec] = explode(':', $plan->prod_time);
                $totalProdSeconds = (($prodMin * 60) + $prodSec) * $plan->order_qty;
        
                // Hitung working_end
                $workingEnd = (clone $currentTime)->addSeconds($totalProdSeconds);
                $updateData['working_end'] = $workingEnd->format('H:i');
        
                // Hitung balance_time
                if ($plan->delivery_time) {
                    $deliveryTime = Carbon::createFromFormat('H:i', $plan->delivery_time);
        
                    // Jika delivery lebih kecil dari working_end (artinya hari berikutnya), tambahkan 1 hari
                    if ($deliveryTime->lessThan($workingEnd)) {
                        $deliveryTime->addDay();
                    }
        
                    // Hitung selisih
                    $balanceSeconds = $deliveryTime->diffInSeconds($workingEnd);
                    $balanceHour = floor($balanceSeconds / 3600);
                    $balanceMinute = floor(($balanceSeconds % 3600) / 60);
                    $updateData['balance_time'] = sprintf('%02d:%02d', $balanceHour, $balanceMinute);
                }
        
                // Hanya hitung sekali
                break;
            }
        }        

        // Update all matching records with increment and timestamps if needed
        $updated = ProductionPlan::whereIn('id', $plans->pluck('id'))
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Updated ' . $updated . ' record(s)',
            'data' => $plans->fresh()
        ]);
    }
}
