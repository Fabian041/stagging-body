<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErrorLogController extends Controller
{
    public function index()
    {
        // get error logs

        return view('pages.error.index');
    }

    public function getErrorLogs(Request $request)
    {

        $errorLogs = ErrorLog::get();

        return DataTables::of($errorLogs)
            ->make(true);
    }

    public function store(Request $request)
    {
        // get user dept
        $dept = auth()->user()->role ?? null;
        $message = $request->message ?? null;
        $expected = $request->expected ?? null;
        $scanned = $request->scanned ?? null;

        // Protect against DB length limits without changing schema
        // (e.g. kanban string very long for `expected`)
        $maxLen = 255;
        if (is_string($message) && strlen($message) > $maxLen) {
            $message = substr($message, 0, $maxLen);
        }
        if (is_string($expected) && strlen($expected) > $maxLen) {
            // Keep the tail so part number / important info near the end is preserved
            $expected = substr($expected, -$maxLen);
        }
        if (is_string($scanned) && strlen($scanned) > $maxLen) {
            $scanned = substr($scanned, 0, $maxLen);
        }

        try {
            DB::beginTransaction();

            Log::info('ErrorLogController@store - start save error log', [
                'user_id' => auth()->id(),
                'user_role' => $dept,
                'message' => $message,
                'expected' => $expected,
                'scanned' => $scanned,
            ]);

            ErrorLog::create([
                'area' => $dept,
                'message' => $message,
                'expected' => $expected,
                'scanned' => $scanned,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
            Log::info('ErrorLogController@store - save success');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('ErrorLogController@store - save failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);
        }
    }
}
