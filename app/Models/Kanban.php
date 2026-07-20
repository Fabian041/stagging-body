<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kanban extends Model
{
    use HasFactory;

    public const STATUS_NOT_PRODUCED = 0;

    public const STATUS_PRODUCED = 1;

    public const STATUS_PULLED = 2;

    protected $table = 'kanbans';

    protected $guarded = ['id'];

    public function canBePulled(): bool
    {
        return in_array((int) $this->status, [self::STATUS_NOT_PRODUCED, self::STATUS_PRODUCED], true);
    }

    public function kanbanAfterPulling()
    {
        return $this->hasMany(KanbanAfterPulling::class, 'kanban_id');
    }

    public function kanbanAfterProd()
    {
        return $this->hasMany(KanbanAfterProd::class, 'kanban_id');
    }

    public function internalPart()
    {
        return $this->belongsTo(InternalPart::class, 'internal_part_id');
    }
}
