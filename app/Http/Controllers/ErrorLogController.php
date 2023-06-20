<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErrorLogController extends Controller
{
    public function pulling(Request $request)
    {
        // get user dept
        $dept = auth()->user()->role;

        try {
            DB::beginTransaction();

            ErrorLog::create([
                'area' => $dept,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
