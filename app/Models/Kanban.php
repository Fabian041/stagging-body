<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kanban extends Model
{
    use HasFactory;

    protected $table = 'kanbans';

    protected $guarded = ['id'];

    public function kanbanAfterPulling()
    {
        return $this->hasMany(KanbanAfterPulling::class, 'kanban_id');
    }
    
    public function kanbanAfterProd()
    {
        return $this->hasMany(KanbanAfterProd::class, 'kanban_id');
    }
}
