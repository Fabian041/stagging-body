<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PisScanLog extends Model
{
    use HasFactory;

    protected $table = 'pis_scan_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    public function detail()
    {
        return $this->belongsTo(PisScanDetail::class, 'pis_scan_detail_id');
    }
}

