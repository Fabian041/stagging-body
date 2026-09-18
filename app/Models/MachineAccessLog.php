<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineAccessLog extends Model
{
    use HasFactory;

    public const RESULT_GRANTED = 'GRANTED';
    public const RESULT_DENIED = 'DENIED';

    protected $guarded = ['id'];

    protected $casts = [
        'occurred_at' => 'datetime',
        'cache_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function events()
    {
        return $this->hasMany(MachineEvent::class);
    }
}
