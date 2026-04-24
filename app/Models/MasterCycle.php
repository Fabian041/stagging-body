<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterCycle extends Model
{
    protected $table = 'master_cycles';

    protected $fillable = [
        'customer_id',
        'cycle_name',
        'time',
        'prep_end_time',
        'truck_time',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
