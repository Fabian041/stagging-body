<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterItemCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'item_check',
        'jumlah_check',
    ];

    public function part()
    {
        return $this->belongsTo(MasterPart::class, 'part_id');
    }

    public function standards()
    {
        return $this->hasMany(MasterStandard::class, 'item_check_id');
    }
}