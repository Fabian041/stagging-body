<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeakController extends Controller
{
    public function index(Request $request)
    {
        $machine = trim((string) $request->query('machine', 'TMEJ003'));

        return view('pages.leak.index', [
            'machine' => $machine !== '' ? $machine : 'TMEJ003',
        ]);
    }

    public function data(Request $request)
    {
        $machine = trim((string) $request->query('machine', 'TMEJ003'));
        if ($machine === '') {
            $machine = 'TMEJ003';
        }

        $target = rtrim((string) env('LEAK_API_URL', 'http://172.18.3.70:2100/api/plc'), '/').'/'.$machine;

        try {
            $res = Http::timeout(10)->acceptJson()->get($target);

            if (! $res->successful()) {
                logger()->warning('Leak API non-200', ['machine' => $machine, 'http' => $res->status()]);

                return response()->json([
                    'status' => false,
                    'machine' => $machine,
                ], 502)->header('Cache-Control', 'no-store');
            }

            return response()->json([
                'status' => true,
                'machine' => $machine,
                'data' => $res->json(),
            ])->header('Cache-Control', 'no-store');
        } catch (\Throwable $e) {
            logger()->warning('Leak API gagal', ['machine' => $machine, 'err' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'machine' => $machine,
            ], 502)->header('Cache-Control', 'no-store');
        }
    }
}
