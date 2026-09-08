<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    use HasFactory;

    protected $table = 'lines';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function internalPart()
    {
        return $this->hasMany(InternalPart::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
