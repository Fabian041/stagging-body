<?php

return [
    'printers' => [
        'kanban' => env('QZ_PRINTER_KANBAN', 'Printer Office AIIA'),
        'manifest' => env('QZ_PRINTER_MANIFEST', 'Brother PT-9800PCN'),
        'skid' => env('QZ_PRINTER_SKID', 'HP laserJet PPIC Bpdy'),
        
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
        // 'kanban' => [
        //     'width' => 180,
        //     'height' => 80,
        //     'units' => 'mm',
        //     'margins' => 0,
        //     'scale_content' => true,
        //     'copies' => 1,
        // ],
        // 'kanban-tmmin' => [
        //     'width' => 150,
        //     'height' => 80,
        //     'units' => 'mm',
        //     'margins' => 0,
        //     'scale_content' => true,
        //     'copies' => 1,
        // ],
        // 'kanban-otic' => [
        //     'width' => 80,
        //     'height' => 80,
        //     'units' => 'mm',
        //     'margins' => 0,
        //     'scale_content' => true,
        //     'copies' => 1,
        // ],
        'manifest' => [
            // 'width' => 218.4,
            // 'height' => 279.4,
            'width' => 228.4,
            'height' => 289.4,
            'units' => 'mm',
            'margins' => 0,
            'scale_content' => true,
            'copies' => 1,
        ],
        'skid' => [
            'width' => 210,
            'height' => 297,
            'units' => 'mm',
            'margins' => 0,
            'scale_content' => true,
            'copies' => 1,
        ],
    ],
];
