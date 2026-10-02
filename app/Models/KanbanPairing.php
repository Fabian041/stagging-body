<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanPairing extends Model
{
    use HasFactory;

    // Ganti ke ['*'] untuk mengaktifkan interlock ke semua project.
    public const INTERLOCK_PROJECTS = ['660A', '560B', '4L45W'];

    protected $table = 'body_kanban_pairings';

    protected $fillable = [
        'project',
        'product',
        'part_name',
        'part_number_customer',
        'painting_part',
        'painting_back_number',
        'assembly_part',
        'assembly_back_number',
        'qty_painting',
        'qty_assy',
    ];

    public static function forAssemblyPart(string $part): ?self
    {
        $pairs = self::whereRaw('UPPER(TRIM(assembly_part)) = ?', [strtoupper(trim($part))])
            ->get();

        return $pairs->first(fn(self $pair) => $pair->usesProductionInterlock())
            ?: $pairs->first();
    }

    public function usesProductionInterlock(): bool
    {
        $projects = array_map(fn($project) => strtoupper(trim((string) $project)), self::INTERLOCK_PROJECTS);

        return in_array('*', $projects, true)
            || in_array(strtoupper(trim((string) $this->project)), $projects, true);
    }

    public function allowsRepeatedSerials(): bool
    {
        return strtoupper(trim((string) $this->project)) === '4L45W';
    }
}
