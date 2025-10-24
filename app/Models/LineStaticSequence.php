<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LineStaticSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'line','back_no','seq_no','dock_hint','default_order_qty','default_prod_time','is_active'
    ];
}
