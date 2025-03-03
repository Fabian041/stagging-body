<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $guarded = ['id'];

    public function custLoadingList()
    {
        return $this->hasMany(LoadingList::class);
    }

    public function customerPart()
    {
        return $this->hasMany(CustomerPart::class);
    }
}
