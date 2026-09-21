<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanManualPreset extends Model
{
    protected $table = 'kanban_manual_presets';

    protected $fillable = [
        'name',
        'lines',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'lines' => 'array',
    ];
}
