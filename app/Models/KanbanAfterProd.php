<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanAfterProd extends Model
{
    use HasFactory;

    protected $table = 'kanban_after_prods';

    protected $guarded = ['id'];

    public function internalPart()
    {
        return $this->belongsTo(InternalPart::class, 'internal_part_id');
    }

    public function kanban()
    {
        return $this->belongsTo(Kanban::class, 'kanban_id');
    }
}
