<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PisScanDetail extends Model
{
    use HasFactory;

    protected $table = 'pis_scan_details';

    protected $guarded = ['id'];

    public function pisScan()
    {
        return $this->belongsTo(PisScan::class);
    }

    public function logs()
    {
        return $this->hasMany(PisScanLog::class, 'pis_scan_detail_id');
    }
}
