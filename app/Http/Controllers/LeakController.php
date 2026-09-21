<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LeakController extends Controller
{
    public function index(Request $request)
    {
        // Generic: tidak ada fallback hardcode. Kosong = user harus search/pilih mesin.
        $machine = $this->machine($request);

        return view('pages.leak.index', [
            'machine' => $machine,
        ]);
    }

    /**
     * Search daftar mesin secara generic untuk autocomplete.
     * Proxy ke gateway PLC dengan beberapa bentuk response yang umum,
     * lalu filter `q` di sisi Laravel agar tetap generic.
     *
     * GET /dashboard/leak/machines?q=TMEJ
     * Response: { status: true, query: "...", machines: [{code,name}, ...] }
     */
    public function machines(Request $request)
    {
        $q = strtoupper(trim((string) $request->query('q', '')));
        $base = $this->baseUrl();
        $timeout = $this->timeout();
        $cacheTtl = max(10, (int) config('leak.machines_cache_ttl', 60));

        $all = Cache::remember('leak:machines:list', $cacheTtl, function () use ($base, $timeout) {
            foreach ($this->machineListEndpoints($base) as $url) {
                try {
                    $res = Http::timeout($timeout)->acceptJson()->get($url);
                    if (! $res->successful()) {
                        continue;
                    }
                    $parsed = $res->json();
                    $list = $this->normalizeMachineList($parsed);
                    if (! empty($list)) {
                        return $list;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            return [];
        });

        if ($q !== '') {
            $all = array_values(array_filter($all, function ($m) use ($q) {
                return str_contains(strtoupper($m['code'] ?? ''), $q)
                    || str_contains(strtoupper($m['name'] ?? ''), $q);
            }));
        }

        return response()->json([
            'status' => true,
            'query' => $request->query('q', ''),
            'machines' => array_slice($all, 0, 20),
        ])->header('Cache-Control', 'no-store');
    }

    public function data(Request $request)
    {
        $machine = $this->machine($request);

        // Generic: tanpa mesin yang dipilih, jangan tembak PLC dengan default hardcode.
        if ($machine === '') {
            return response()->json([
                'status' => false,
                'machine' => '',
                'message' => 'Pilih mesin dulu (parameter machine kosong).',
            ], 422)->header('Cache-Control', 'no-store');
        }

        if (! $this->isValidMachineCode($machine)) {
            return response()->json([
                'status' => false,
                'machine' => $machine,
                'message' => 'Kode mesin tidak valid.',
            ], 422)->header('Cache-Control', 'no-store');
        }

        $base = $this->baseUrl();
        $timeout = $this->timeout();
        $target = $base.'/'.rawurlencode($machine);

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
        // Urutan: query ?machine= -> config leak.default_machine -> '' (generic, tanpa hardcode).
        $configured = trim((string) config('leak.default_machine', ''));
        $machine = trim((string) $request->query('machine', $configured));

        return strtoupper($machine);
    }

    private function isValidMachineCode(string $machine): bool
    {
        $pattern = (string) config('leak.code_pattern', '/^[A-Z0-9._-]{1,64}$/');

        return (bool) preg_match($pattern, $machine);
    }

    /**
     * Kandidat endpoint daftar mesin di gateway.
     * Dibuat generic karena tiap gateway bisa beda path.
     */
    private function machineListEndpoints(string $base): array
    {
        return [
            $base,
            $base.'/machines',
            $base.'/list',
        ];
    }

    /**
     * Normalisasi berbagai bentuk response gateway menjadi [{code, name}].
     * Menerima: array string, array object, atau {data|machines|items: [...]}.
     */
    private function normalizeMachineList(mixed $parsed): array
    {
        $rows = $parsed;
        if (is_array($parsed)) {
            foreach (['machines', 'data', 'items', 'list'] as $key) {
                if (isset($parsed[$key]) && is_array($parsed[$key])) {
                    $rows = $parsed[$key];
                    break;
                }
            }
        }

        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (is_string($row)) {
                $code = strtoupper(trim($row));
                if ($code !== '' && $this->isValidMachineCode($code)) {
                    $out[] = ['code' => $code, 'name' => $code];
                }
                continue;
            }

            if (! is_array($row)) {
                continue;
            }

            $code = (string) ($row['machine_code'] ?? $row['code'] ?? $row['machine'] ?? $row['machine_name'] ?? '');
            $code = strtoupper(trim($code));
            if ($code === '' || ! $this->isValidMachineCode($code)) {
                continue;
            }

            $name = (string) ($row['machine_name'] ?? $row['name'] ?? $code);
            $out[] = ['code' => $code, 'name' => $name !== '' ? $name : $code];
        }

        // Unik per code.
        $unique = [];
        foreach ($out as $m) {
            $unique[$m['code']] = $m;
        }

        return array_values($unique);
    }

    private function baseUrl(): string
    {
        // Via config agar aman terhadap config:cache (jangan env() langsung).
        $base = trim((string) config('leak.base_url', 'http://172.18.3.70:2100/api/plc'));
        if ($base === '') {
            $base = 'http://172.18.3.70:2100/api/plc';
        }

        return rtrim($base, '/');
    }

    private function timeout(): int
    {
        $t = (int) config('leak.timeout', 10);
        if ($t < 2) {
            $t = 2;
        }
        if ($t > 30) {
            $t = 30;
        }

        return $t;
    }
}
