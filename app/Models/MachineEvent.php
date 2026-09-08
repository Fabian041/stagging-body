<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineEvent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function accessLog()
    {
        return $this->belongsTo(MachineAccessLog::class, 'machine_access_log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
