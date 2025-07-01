<?php

namespace App\Models\Agstar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ia01 extends Model
{
    use HasFactory;

    protected $connection = 'mssql_ext';
    protected $table = 'IA01';
    public $timestamps = false;

    protected $primaryKey = 'DEC_COD_BINID';
}
