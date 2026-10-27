<?php

return [
    'pdf_optimization' => [
        'enabled' => env('KANBAN_PDF_OPTIMIZE_ENABLED', true),
        'engine' => env('KANBAN_PDF_OPTIMIZE_ENGINE', 'auto'),
        'dpi' => (int) env('KANBAN_PDF_OPTIMIZE_DPI', 150),
        'quality' => (int) env('KANBAN_PDF_OPTIMIZE_QUALITY', 75),
        'timeout' => (int) env('KANBAN_PDF_OPTIMIZE_TIMEOUT', 120),
        'only_if_smaller' => env('KANBAN_PDF_OPTIMIZE_ONLY_IF_SMALLER', true),
    ],
];
