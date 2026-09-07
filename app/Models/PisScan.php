<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PisScan extends Model
{
    use HasFactory;

    public const DELIVERY_CUSTOMER_NAMES = [
        'ADM SPD',
        'MMKI SPD',
        'TAM SPD (DAIHATSU)',
        'TMMIN SPD',
    ];

    protected $table = 'pis_scans';

    protected $guarded = ['id'];

    protected $casts = [
        'operator_scanned_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PisScanDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
