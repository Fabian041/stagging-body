<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoadingList extends Model
{
    use HasFactory;

    protected $table = 'loading_lists';

    protected $guarded = ['id'];

    public function detail()
    {
        return $this->hasMany(LoadingListDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
