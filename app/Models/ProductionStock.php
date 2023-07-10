<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionStock extends Model
{
    use HasFactory;

    protected  $table = 'production_stocks';

    protected $guarded = ['id'];
}
