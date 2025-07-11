<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternalPart extends Model
{
    use HasFactory;

    protected $table = 'internal_parts';

    protected $guarded = ['id'];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }
    public function customerPart()
    {
        return $this->hasOne(CustomerPart::class);
    }

    public function kanbanAfterPulling()
    {
        return $this->hasMany(KanbanAfterPulling::class, 'internal_part_id');
    }

    public function kanbanAfterProd()
    {
        return $this->hasMany(KanbanAfterProd::class, 'internal_part_id');
    }
}
