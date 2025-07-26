<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

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

        try {
            DB::beginTransaction();

            ErrorLog::create([
                'area' => $dept,
                'message' => $message,
                'expected' => $expected,
                'scanned' => $scanned,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
