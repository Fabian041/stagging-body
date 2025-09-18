<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanPairing extends Model
{
    use HasFactory;

    protected $table = 'body_kanban_pairings';

    protected $fillable = [
        'painting_part',
        'assembly_part',
        'qty_painting',
        'qty_assy',
    ];
}
