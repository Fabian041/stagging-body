<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPart extends Model
{
    use HasFactory;

    protected $table = 'internal_parts';

    protected $guarded = ['id'];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function customerPart()
    {
        return $this->hasOne(CustomerPart::class);
    }

    public function customerParts()
    {
        return $this->hasMany(CustomerPart::class);
    }

    public function moldHasParts()
    {
        return $this->hasMany(MoldHasParts::class, 'internal_part_id');
    }

    public function kanbanAfterPulling()
    {
        return $this->hasMany(KanbanAfterPulling::class, 'internal_part_id');
    }

    public function kanbanAfterProd()
    {
        return $this->hasMany(KanbanAfterProd::class, 'internal_part_id');
    }

    public function isPullingKanbanStatusExempt(): bool
    {
        $backNumber = strtoupper(trim((string) $this->back_number));

        return $backNumber !== ''
            && in_array($backNumber, config('pulling.kanban_status_exempt_back_numbers', []), true);
    }
}
