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
        $code = $request->code;
        $message = $request->message;

        try {
            DB::beginTransaction();

            ErrorLog::create([
                'area' => 'pulling',
                'code' => $code,
                'message' => $message,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
    
    public function production(Request $request)
    {
        $code = $request->code;
        $message = $request->message;

        try {
            DB::beginTransaction();

            ErrorLog::create([
                'area' => 'pulling',
                'code' => $code,
                'message' => $message,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
