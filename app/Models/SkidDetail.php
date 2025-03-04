<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SkidDetail extends Model
{
    use HasFactory;

    protected $table = 'skid_details';

    protected $guarded = ['id'];

    public function loadingListDetail()
    {
        return $this->belongsTo(LoadingListDetail::class);
    }
}
