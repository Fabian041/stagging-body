<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PisPart extends Model
{
    use HasFactory;

    protected $table = 'part_pis';

    protected $fillable = [
        'part_number',
        'part_number_ag',
        'part_number_kanban',
        'part_number_customer',
        'customer_code',
        'customer_code_ag',
        'part_kind',
        'part_dock',
        'back_number',
        'qty_kanban',
    ];

    protected $casts = [
        'qty_kanban' => 'float',
    ];
}
