<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanAfterPulling extends Model
{
    use HasFactory;

    protected $table = 'kanban_after_pulls';

    protected $guarded = ['id'];
}
