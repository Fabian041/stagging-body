<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mold extends Model
{
    protected $table = 'molds';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function moldHasParts()
    {
        return $this->hasMany(MoldHasParts::class, 'mold_id', 'id');
    }

    public function parts()
    {
        return $this->hasMany(MoldHasParts::class, 'mold_id', 'id');
    }
}
