<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Leak PLC Gateway
    |--------------------------------------------------------------------------
    |
    | Semua nilai dibaca via config() agar aman terhadap `config:cache`.
    | Jangan hardcode kode mesin di sini maupun di controller/blade.
    | LEAK_DEFAULT_MACHINE boleh kosong -> halaman leak bersifat generic
    | dan user wajib pilih/cari mesin lewat search.
    |
    */

    'base_url' => rtrim(env('LEAK_API_URL', 'http://172.18.3.70:2100/api/plc'), '/'),

    'timeout' => (int) env('LEAK_TIMEOUT', 10),

    // Nullable. Kosong = tidak ada mesin default (generic search).
    'default_machine' => env('LEAK_DEFAULT_MACHINE'),

    // Validasi generic untuk kode mesin, bukan whitelist mesin tertentu.
    'code_pattern' => '/^[A-Z0-9._-]{1,64}$/',

    // Cache daftar mesin untuk search autocomplete (detik).
    'machines_cache_ttl' => (int) env('LEAK_MACHINES_CACHE_TTL', 60),
];
