<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestDetail extends Model
{
    use HasFactory;

    protected $table = 'manifest_details';

    protected $guarded = ['id'];
}
