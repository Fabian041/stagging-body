<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kanban extends Model
{
    use HasFactory;

    protected $table = 'kanbans';

    protected $guarded = ['id'];
}
