<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoadingListDetail extends Model
{
    use HasFactory;

    protected $table = 'loading_list_details';

    protected $guarded = ['id'];

    public function loadingList()
    {
        return $this->belongsTo(LoadingList::class);
    }

    public function customerPart()
    {
        return $this->belongsTo(CustomerPart::class);
    }
}
