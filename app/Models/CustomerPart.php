<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerPart extends Model
{
    use HasFactory;

    protected $table = 'customer_parts';

    protected $guarded = ['id'];

    public function loadingListDetail()
    {
        return $this->hasMany(loadingListDetail::class);
    }

    public function internalPart()
    {
        return $this->belongsTo(InternalPart::class);
    }
}
