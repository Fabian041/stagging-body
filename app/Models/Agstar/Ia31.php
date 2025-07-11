<?php

namespace App\Models\Agstar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ia31 extends Model
{
    use HasFactory;

    protected $connection = 'mssql_ext';
    protected $table = 'IA31';
    public $timestamps = false;
    protected $primaryKey = 'DEC_COD_BINID';

    public function flight()
    {
        return $this->hasOne(Ia01::class, 'DEC_COD_BINID', 'DEC_COD_BINID');
    }
}
