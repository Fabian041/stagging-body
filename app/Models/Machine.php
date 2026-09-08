<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Machine extends Model
{
    use HasApiTokens, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function permissions()
    {
        return $this->hasMany(MachinePermission::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(MachineAccessLog::class);
    }

    public function events()
    {
        return $this->hasMany(MachineEvent::class);
    }
}
