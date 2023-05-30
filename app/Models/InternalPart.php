<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPart extends Model
{
    use HasFactory;

    protected $table = 'internal_parts';

    protected $guarded = ['id'];
}
