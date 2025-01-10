<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Injection extends Model
{
    use HasFactory;

    protected $table = 'injections';

    protected $guarded = ['id'];
}
