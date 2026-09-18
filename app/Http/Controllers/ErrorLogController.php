<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ErrorLog;
use App\Exports\ErrorLogsExport;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ErrorLogController extends Controller
{
    public function index()
    {
        // get error logs

        return view('pages.error.index');
    }

    public function getErrorLogs(Request $request)
    {
        $area = $request->get('area');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ErrorLog::query()
            ->when($area, function ($q) use ($area) {
                $q->where('area', $area);
            });

        if ($startDate || $endDate) {
            try {
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            } catch (\Throwable $e) {
                $start = null;
            }
            try {
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
            } catch (\Throwable $e) {
                $end = null;
            }

            $query->when($start || $end, function ($q) use ($start, $end) {
                if ($start && $end) {
                    $q->whereBetween('date', [$start, $end]);
                } elseif ($start) {
                    $q->where('date', '>=', $start);
                } elseif ($end) {
                    $q->where('date', '<=', $end);
                }
            });
        }

        return DataTables::of($query)
            ->make(true);
    }

    public function export(Request $request)
    {
        $area = $request->query('area');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $filenameParts = ['error-logs'];
        if ($area) {
            $filenameParts[] = $area;
        }
        if ($startDate) {
            $filenameParts[] = $startDate;
        }
        if ($endDate) {
            $filenameParts[] = $endDate;
        }
        $filenameParts[] = Carbon::now()->format('Ymd_His');
        $fileName = implode('_', $filenameParts) . '.xlsx';

        return Excel::download(new ErrorLogsExport($area, $startDate, $endDate), $fileName);
    }

    public function store(Request $request)
    {
        // Area: khusus dari modul PIS selalu "Packing"
        // Menu/modul lain tetap menggunakan dept user (role)
        $area = (strtolower((string) $request->get('source')) === 'pis')
            ? 'Packing'
            : (auth()->user()->role ?? null);

        $message = $request->message ?? null;
        $expected = $request->expected ?? null;
        $scanned = $request->scanned ?? null;
        $deviceId = substr(trim((string) $request->get('device_id', '')), 0, 100);
        $deviceSession = $deviceId !== '' && auth()->id()
            ? DB::table('device_sessions')
                ->where('device_id', $deviceId)
                ->where('user_id', auth()->id())
                ->first()
            : null;

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
                'area' => $area,
                'message' => $message,
                'expected' => $expected,
                'scanned' => $scanned,
            ]);

            $errorLog = ErrorLog::create([
                'area' => $area,
                'message' => $message,
                'expected' => $expected,
                'scanned' => $scanned,
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                'user_id' => auth()->id(),
                'device_session_id' => $deviceSession->id ?? null,
            ]);

            if ($deviceSession) {
                DB::table('device_sessions')->where('id', $deviceSession->id)->update([
                    'last_error_message' => $message,
                    'last_error_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('device_session_events')->insert([
                    'device_session_id' => $deviceSession->id,
                    'user_id' => auth()->id(),
                    'type' => 'operational_error',
                    'message' => substr((string) ($message ?: 'Error operasional'), 0, 255),
                    'meta' => json_encode([
                        'error_log_id' => $errorLog->id,
                        'expected' => $expected,
                        'scanned' => $scanned,
                    ]),
                    'created_at' => now(),
                ]);
            }

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
