<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanPairingLog extends Model
{
    use HasFactory;

    public const STATUS_CONSUMED = 0;

    public const STATUS_READY = 1;

    protected $table = 'kanban_pairing_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'painting_serial_numbers' => 'array',
        'paired_at' => 'datetime',
        'consumed_at' => 'datetime',
        'is_uncomplete' => 'boolean',
    ];
}
