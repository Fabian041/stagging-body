<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeakController extends Controller
{
    public function index(Request $request)
    {
        $machine = $this->machine($request);

        return view('pages.leak.index', [
            'machine' => $machine,
        ]);
    }

    public function data(Request $request)
    {
        $machine = $this->machine($request);
        $base = $this->baseUrl();
        $timeout = $this->timeout();
        $target = $base.'/'.$machine;

        $fields = collect(explode(',', (string) $request->query('fields', '')))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values()
            ->all();

        try {
            $res = Http::timeout($timeout)->acceptJson()->get($target);

            if (! $res->successful()) {
                return response()->json([
                    'status' => false,
                    'machine' => $machine,
                    'message' => 'Gagal ambil data PLC (HTTP '.$res->status().')',
                ], 502)->header('Cache-Control', 'no-store');
            }

            $parsed = $res->json();
            if (! is_array($parsed)) {
                return response()->json([
                    'status' => false,
                    'machine' => $machine,
                    'message' => 'Gateway PLC tidak mengembalikan JSON valid.',
                ], 502)->header('Cache-Control', 'no-store');
            }

            $data = $parsed['data'] ?? [];
            if (! is_array($data)) {
                $data = [];
            }

            if (! empty($fields)) {
                $data = collect($data)->only($fields)->all();
            }

            return response()->json([
                'status' => true,
                'machine' => $parsed['machine_name'] ?? $machine,
                'meta' => [
                    'ip_address' => $parsed['ip_address'] ?? null,
                    'port' => $parsed['port'] ?? null,
                ],
                'data' => $data,
            ])->header('Cache-Control', 'no-store');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'cURL error 28') || str_contains($msg, 'timed out')) {
                $msg = 'Timeout ke gateway PLC ('.$timeout.' detik). Pastikan server bisa menjangkau '.$target.' dan service gateway jalan. Detail: '.$msg;
            }

            return response()->json([
                'status' => false,
                'machine' => $machine,
                'message' => 'Gagal menghubungi gateway PLC: '.$msg,
            ], 502)->header('Cache-Control', 'no-store');
        }
    }

    private function machine(Request $request): string
    {
        $default = trim((string) env('LEAK_DEFAULT_MACHINE', 'TMEJ003'));
        if ($default === '') {
            $default = 'TMEJ003';
        }

        $machine = trim((string) $request->query('machine', $default));

        return $machine !== '' ? $machine : $default;
    }

    private function baseUrl(): string
    {
        $base = trim((string) env('LEAK_API_URL', 'http://172.18.3.70:2100/api/plc'));
        if ($base === '') {
            $base = 'http://172.18.3.70:2100/api/plc';
        }

        return rtrim($base, '/');
    }

    private function timeout(): int
    {
        $t = (int) env('LEAK_TIMEOUT', 10);
        if ($t < 2) {
            $t = 2;
        }
        if ($t > 30) {
            $t = 30;
        }

        return $t;
    }
}
