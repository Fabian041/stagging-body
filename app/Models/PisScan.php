<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PisScan extends Model
{
    use HasFactory;

    protected $table = 'pis_scans';

    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(PisScanDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
