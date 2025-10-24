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
    /**
     * Update incremental DP/SC.
     * - Cari by plan_date (default: hari ini) + back_no (termasuk ekivalensi).
     * - dn_number opsional; jika dikirim maka ikut difilter, kalau tidak dikirim diabaikan.
     * - Tanpa fallback tanggal (strict di plan_date).
     */
    public function updateQty(Request $request)
    {
        // 1) Validasi
        $validator = Validator::make($request->all(), [
            'dn_number'          => 'sometimes|nullable|string',
            'back_no'            => 'required|string',
            'direct_pulling_qty' => 'sometimes|integer|min:0',
            'stock_chute_qty'    => 'sometimes|integer|min:0',
            'plan_date'          => 'sometimes|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2) Normalisasi input
        $dn        = $request->filled('dn_number') ? trim((string) $request->dn_number) : null;
        $backNoRaw = strtoupper(trim((string) $request->back_no));
        $planDate  = $request->input('plan_date', Carbon::now()->toDateString()); // default hari ini

        $addDp = (int) $request->input('direct_pulling_qty', 0);
        $addSc = (int) $request->input('stock_chute_qty', 0);
        if ($addDp === 0 && $addSc === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No quantity field provided (direct_pulling_qty / stock_chute_qty).',
            ], 422);
        }

        // Grup ekivalensi back_no (opsional, sesuaikan)
        $equivGroups = [
            ['CI17', 'CI18'],
            // tambah mapping lain jika perlu
        ];

        $backNosToCheck = [$backNoRaw];
        foreach ($equivGroups as $group) {
            if (in_array($backNoRaw, $group, true)) {
                $backNosToCheck = $group;
                break;
            }
        }

        return DB::transaction(function () use ($dn, $backNosToCheck, $planDate, $addDp, $addSc) {

            // Ambil baris strict di plan_date, back_no (ekivalensi), dan optional dn_number
            $plansQ = ProductionPlan::query()
                ->whereDate('plan_date', $planDate)
                ->whereIn('back_no', $backNosToCheck);

            if (!is_null($dn) && $dn !== '') {
                $plansQ->where('dn_number', $dn);
            }

            // Urutkan agar "paling atas" duluan. Kalau tidak ada delivery_time, id jadi tie-breaker.
            $plans = $plansQ
                ->orderBy('delivery_time') // jika kolom ada; null akan diurutkan dulu/terakhir tergantung DB, tidak apa.
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($plans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching production plans found for the specified plan_date',
                    'data'    => [
                        'dn_number'       => $dn,
                        'back_no_checked' => $backNosToCheck,
                        'plan_date'       => $planDate,
                    ],
                ], 404);
            }

            $updatedCount = 0;
            $now          = now();

            // Helper: set timestamps ketika DP dari 0 -> >0 untuk baris yang pertama kena
            $setStartOnce = function (ProductionPlan $plan) use ($now) {
                // Set actual_working_start
                $plan->actual_working_start = $now->format('H:i');

                // Hitung working_end berdasarkan prod_time * order_qty (fallback aman)
                $prodMin = 0; $prodSec = 0;
                if ($plan->prod_time && strpos($plan->prod_time, ':') !== false) {
                    [$prodMin, $prodSec] = array_map('intval', explode(':', $plan->prod_time));
                }
                $totalProdSeconds = (($prodMin * 60) + $prodSec) * (int)$plan->order_qty;
                $workingEnd = (clone $now)->addSeconds($totalProdSeconds);
                $plan->working_end = $workingEnd->format('H:i');

                // Hitung balance_time vs delivery_time (jika valid)
                if ($plan->delivery_time) {
                    try {
                        $deliveryTime = Carbon::createFromFormat('H:i', $plan->delivery_time);

                        // Jika delivery < start → anggap hari berikutnya
                        if ($deliveryTime->lessThan($workingEnd)) {
                            $deliveryTime->addDay();
                        }

                        $balanceSeconds = $deliveryTime->diffInSeconds($workingEnd);
                        $balanceHour    = intdiv($balanceSeconds, 3600);
                        $balanceMinute  = intdiv($balanceSeconds % 3600, 60);
                        $plan->balance_time = sprintf('%02d:%02d', $balanceHour, $balanceMinute);
                    } catch (\Throwable $e) {
                        // abaikan jika format tidak valid
                    }
                }
            };

            // === 1) Distribusi DIRECT PULLING ===
            $remainDp          = $addDp;
            $alreadySetStartDp = false;

            if ($remainDp > 0) {
                // Total kapasitas DP
                $totalCapDp = 0;
                foreach ($plans as $p) {
                    $totalCapDp += max(0, (int)$p->order_qty - (int)$p->direct_pulling_qty);
                }

                if ($remainDp > $totalCapDp) {
                    return response()->json([
                        'success'            => false,
                        'message'            => 'Direct pulling quantity exceeds total remaining capacity across plans',
                        'requested_addition' => $remainDp,
                        'total_capacity'     => $totalCapDp,
                        'max_allowed'        => $totalCapDp, // agar caller tahu batasnya
                    ], 422);
                }

                foreach ($plans as $plan) {
                    if ($remainDp <= 0) break;

                    $current = (int)$plan->direct_pulling_qty;
                    $cap     = max(0, (int)$plan->order_qty - $current);
                    if ($cap <= 0) continue;

                    $take = min($remainDp, $cap);

                    $wasZeroBefore = ($current === 0);
                    $plan->direct_pulling_qty = $current + $take;

                    // Set timestamps sekali saja ketika DP dari 0 -> >0 pada baris pertama yang kena
                    if ($wasZeroBefore && !$alreadySetStartDp && $take > 0) {
                        $setStartOnce($plan);
                        $alreadySetStartDp = true;
                    }

                    $plan->save();
                    $updatedCount++;
                    $remainDp -= $take;
                }
            }

            // === 2) Distribusi STOCK CHUTE ===
            $remainSc = $addSc;
            if ($remainSc > 0) {
                // Total kapasitas SC
                $totalCapSc = 0;
                foreach ($plans as $p) {
                    $totalCapSc += max(0, (int)$p->order_qty - (int)$p->stock_chute_qty);
                }

                if ($remainSc > $totalCapSc) {
                    return response()->json([
                        'success'            => false,
                        'message'            => 'Stock chute quantity exceeds total remaining capacity across plans',
                        'requested_addition' => $remainSc,
                        'total_capacity'     => $totalCapSc,
                        'max_allowed'        => $totalCapSc,
                    ], 422);
                }

                foreach ($plans as $plan) {
                    if ($remainSc <= 0) break;

                    $current = (int)$plan->stock_chute_qty;
                    $cap     = max(0, (int)$plan->order_qty - $current);
                    if ($cap <= 0) continue;

                    $take = min($remainSc, $cap);
                    $plan->stock_chute_qty = $current + $take;

                    // SC tidak memicu timestamps
                    $plan->save();
                    $updatedCount++;
                    $remainSc -= $take;
                }
            }

            // Ambil ulang data terbaru (hanya yang match filter agar respons ringkas)
            $fresh = ProductionPlan::query()
                ->whereDate('plan_date', $planDate)
                ->whereIn('back_no', $backNosToCheck)
                ->when(!is_null($dn) && $dn !== '', fn($q) => $q->where('dn_number', $dn))
                ->orderBy('delivery_time')->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Distributed update applied',
                'updated_records' => $updatedCount,
                'data'    => $fresh,
            ]);
        });
    }

}
