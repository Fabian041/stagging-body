<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPart extends Model
{
    use HasFactory;

    protected $table = 'customer_parts';

    protected $guarded = ['id'];
}
