<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_check_id',
        'standard',
        'min_value',
        'max_value',
    ];

    public function itemCheck()
    {
        return $this->belongsTo(MasterItemCheck::class, 'item_check_id');
    }
}