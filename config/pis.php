<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PIS Barcode Scanning Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for PIS (Part Information System) barcode scanning.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Append Character Workaround
    |--------------------------------------------------------------------------
    |
    | WORKAROUND OPTION: If enabled, appends a character to scanned barcodes
    | before querying the database. This is a temporary workaround for cases
    | where the database stores part numbers with trailing characters that
    | the scanner doesn't capture.
    |
    | ⚠️ WARNING: This is a workaround, not a permanent solution.
    | 
    | RECOMMENDED APPROACH:
    | 1. First, try the root cause fix (improved query matching)
    | 2. If that doesn't work, investigate database data format
    | 3. Clean up database data to remove trailing characters
    | 4. Only use this workaround as a last resort
    |
    | RISKS:
    | - May cause false matches if multiple parts differ only by trailing char
    | - Masks underlying data quality issues
    | - May break if database format changes
    | - Makes debugging more difficult
    |
    */

    'append_character_workaround' => env('PIS_APPEND_CHARACTER_WORKAROUND', false),

    /*
    |--------------------------------------------------------------------------
    | Character to Append (if workaround enabled)
    |--------------------------------------------------------------------------
    |
    | The character to append to scanned barcodes when the workaround is enabled.
    | Common values:
    | - ' ' (space) - if database has trailing spaces
    | - '\0' (null) - if database has null padding
    | - Other specific characters based on your database format
    |
    */

    'append_character' => env('PIS_APPEND_CHARACTER', ' '),

    /*
    |--------------------------------------------------------------------------
    | PIS Interlock — NPK JP/Leader yang boleh approval
    |--------------------------------------------------------------------------
    | Hanya NPK dalam array ini yang dapat membuka interlock (verifikasi).
    */
    'jp_leader_npks' => ['000453', '002484'],

];
