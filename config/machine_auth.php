<?php

return [
    'token_ability' => env('MACHINE_AUTH_TOKEN_ABILITY', 'machine-auth'),

    'allowed_cache_ttl_hours' => (int) env('MACHINE_AUTH_ALLOWED_CACHE_TTL_HOURS', 24),
    'denied_cache_ttl_minutes' => (int) env('MACHINE_AUTH_DENIED_CACHE_TTL_MINUTES', 60),
    'sync_cache_ttl_hours' => (int) env('MACHINE_AUTH_SYNC_CACHE_TTL_HOURS', 24),

    'rate_limit_per_minute' => (int) env('MACHINE_AUTH_RATE_LIMIT_PER_MINUTE', 60),

    'development_npk' => env('MACHINE_AUTH_DEV_NPK'),
    'development_card_uid' => env('MACHINE_AUTH_DEV_CARD_UID', '1667815478'),

    'event_types' => [
        'CARD_SCAN',
        'ACCESS_GRANTED',
        'ACCESS_DENIED',
        'PLC_AUTHORIZED',
        'PLC_FAILED',
        'MACHINE_START',
        'MACHINE_STOP',
        'LOGOUT',
    ],
];
