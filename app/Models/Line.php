<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Line extends Model
{
    use HasFactory;

    protected $table = 'lines';

    protected $guarded = ['id'];

    public function internalPart()
    {
        return $this->hasMany(InternalPart::class);
    }
}
