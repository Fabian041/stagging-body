<?php

return [
    'printers' => [
        'kanban' => env('QZ_PRINTER_KANBAN', 'Printer Office AIIA'),
        'manifest' => env('QZ_PRINTER_MANIFEST', 'Brother PT-9800PCN'),
    ],
    'paper' => [
        'kanban' => [
            'width' => 150,
            'height' => 80,
            'units' => 'mm',
            'margins' => 0,
            'scale_content' => true,
            'copies' => 1,
        ],
        'manifest' => [
            'width' => 210,
            'height' => 297,
            'units' => 'mm',
            'margins' => 0,
            'scale_content' => true,
            'copies' => 1,
        ],
    ],
];
