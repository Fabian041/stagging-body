<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mold extends Model
{
    protected $table = 'molds';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        $filename = basename((string) $this->photo);

        if ($filename === '' || ! is_file(public_path('assets/img/molds/' . $filename))) {
            return null;
        }

        return asset('assets/img/molds/' . rawurlencode($filename));
    }

    public function moldHasParts()
    {
        return $this->hasMany(MoldHasParts::class, 'mold_id', 'id');
    }

    public function parts()
    {
        return $this->hasMany(MoldHasParts::class, 'mold_id', 'id');
    }
}
