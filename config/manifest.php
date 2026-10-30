<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Manifest PDF Image Overlay Configuration
    |--------------------------------------------------------------------------
    |
    | When users upload a Manifest PDF, this configuration controls inserting
    | an image (e.g. supplier stamp / signature) at a fixed, predefined
    | position inside the bottom supplier signature box of the manifest document.
    |
    | Coordinates are expressed in millimeters (mm) using the PDF top-left origin.
    | Standard A4 portrait dimensions: 210 mm (width) x 297 mm (height).
    |
    */

    'overlay' => [
        'enabled' => env('MANIFEST_OVERLAY_ENABLED', true),


        'image_path' => env('MANIFEST_OVERLAY_IMAGE_PATH', public_path('assets/img/stamp.jpeg')),

        'x' => (float) env('MANIFEST_OVERLAY_X', 55.0),
        'y' => (float) env('MANIFEST_OVERLAY_Y', 228.0),
        'width' => (float) env('MANIFEST_OVERLAY_WIDTH', 40),
        'height' => (float) env('MANIFEST_OVERLAY_HEIGHT', 40),

        // Maintain aspect ratio inside the bounding box and center the image.
        'preserve_aspect_ratio' => (bool) env('MANIFEST_OVERLAY_PRESERVE_ASPECT', true),

        // Automatically remove white background from JPEG/PNG (creates transparent stamp like PNG)
        'remove_white_bg' => (bool) env('MANIFEST_OVERLAY_REMOVE_WHITE_BG', true),

        // Rotation angle in degrees (e.g. -5.0 or -6.0 for a natural physical stamp tilt, 0.0 for straight)
        'rotation' => (float) env('MANIFEST_OVERLAY_ROTATION', -6.0),

        // Which pages to apply the overlay:
        // 'all'   = every page in the manifest
        // 'first' = only the first page
        // 'last'  = only the last page
        // '1,2'   = specific comma-separated page numbers
        'pages' => env('MANIFEST_OVERLAY_PAGES', 'all'),
    ],
];
