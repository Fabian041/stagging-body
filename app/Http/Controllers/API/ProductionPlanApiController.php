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
            $currentTime = now()->format('H:i');
            $updateData['working_start'] = $currentTime;

            // Calculate working_end (prod_time * order_qty + working_start)
            foreach ($plans as $plan) {
                // Parse prod_time (format: "00:40" meaning 40 seconds)
                $prodTimeParts = explode(':', $plan->prod_time);
                $prodTimeMinutes = (int)$prodTimeParts[0];
                $prodTimeSeconds = (int)$prodTimeParts[1];
                $totalProdSeconds = ($prodTimeMinutes * 60 + $prodTimeSeconds) * $plan->order_qty;

                // Parse working_start time
                $startTimeParts = explode(':', $currentTime);
                $startHour = (int)$startTimeParts[0];
                $startMinute = (int)$startTimeParts[1];
                
                // Add production time to start time
                $endTimestamp = $startHour * 3600 + $startMinute * 60 + $totalProdSeconds;
                
                // Handle next day if needed
                $endHour = floor($endTimestamp / 3600) % 24;
                $endMinute = floor(($endTimestamp % 3600) / 60);
                
                $workingEnd = sprintf('%02d:%02d', $endHour, $endMinute);
                $updateData['working_end'] = $workingEnd;

                // Calculate balance_time (delivery_time - working_end)
                if ($plan->delivery_time) {
                    $deliveryParts = explode(':', $plan->delivery_time);
                    $deliveryHour = (int)$deliveryParts[0];
                    $deliveryMinute = (int)$deliveryParts[1];
                    $deliveryTimestamp = $deliveryHour * 3600 + $deliveryMinute * 60;
                    
                    // If working end is next day (smaller hour number), add 24 hours
                    if ($endHour < $startHour) {
                        $deliveryTimestamp += 24 * 3600;
                    }
                    
                    $balanceSeconds = $deliveryTimestamp - $endTimestamp;
                    $balanceHour = floor($balanceSeconds / 3600);
                    $balanceMinute = floor(($balanceSeconds % 3600) / 60);
                    
                    $balanceTime = sprintf('%02d:%02d', $balanceHour, $balanceMinute);
                    $updateData['balance_time'] = $balanceTime;
                }
                
                // We only need to calculate once since all plans share the same timestamps
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
