<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pulling extends Model
{
    use HasFactory;

    protected $table = 'pullings';

    protected $guarded = ['id'];
}
