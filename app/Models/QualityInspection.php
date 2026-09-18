<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    use HasFactory;

    protected $table = 'quality_inspections';

    protected $guarded = ['id'];

    protected $casts = [
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(QualityInspectionDetail::class);
    }
}