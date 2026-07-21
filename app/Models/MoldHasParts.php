<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoldHasParts extends Model
{
    protected $table = 'mold_has_parts';

    protected $guarded = [];

    public function mold()
    {
        return $this->belongsTo(Mold::class, 'mold_id', 'id');
    }

    public function internalPart()
    {
        return $this->belongsTo(InternalPart::class, 'internal_part_id', 'id');
    }
}
