<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScannedPart extends Model
{
    use HasFactory;

    protected $table = 'part_scans';
    protected $guarded = ['id'];
}
