<?php

return [
    'printers' => [
        'kanban' => env('QZ_PRINTER_KANBAN', 'Printer Office AIIA'),
        'manifest' => env('QZ_PRINTER_MANIFEST', 'Brother PT-9800PCN'),
        'skid' => env('QZ_PRINTER_SKID', 'HP laserJet PPIC Bpdy'),
        
    ],
    'paper' => [
        'kanban' => [
            'width' => 80,
            'height' => 150,
            'units' => 'mm',
            'margins' => 0,
            'scale_content' => true,
            'copies' => 1,
        ],

        // 'paper' => [
        // 'kanban' => [
        //     'width' => 150, // atau 180 sesuai ukuran fisik kertas label Anda
        //     'height' => 80,
        //     'units' => 'mm',
        //     'margins' => 0,
        //     'scale_content' => true,
        //     'copies' => 1,
        //     'orientation' => 'landscape', // Tambahkan orientasi
        // ],

        // 'kanban' => [
        //     'width' => 80,
        //     'height' => 150,
        //     'units' => 'mm',
        //     'margins' => [
        //         'top' => 2,
        //         'bottom' => 2,
        //         'left' => 0,
        //         'right' => 0,
        //     ],
        //     'scale_content' => true,
        //     'copies' => 1,
        //     'orientation' => 'landscape', // Merotasi kanban agar tidak mengecil di bagian atas
        // ],

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
            'width' => 218.4,
            'height' => 279.4,
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
