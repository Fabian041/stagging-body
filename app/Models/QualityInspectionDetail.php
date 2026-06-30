<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspectionDetail extends Model
{
    use HasFactory;

    protected $table = 'quality_inspection_details';

    protected $guarded = ['id'];

    public function inspection()
    {
        return $this->belongsTo(QualityInspection::class, 'quality_inspection_id');
    }
}