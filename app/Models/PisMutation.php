<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PisMutation extends Model
{
    protected $table = 'pis_mutations';

    protected $fillable = [
        'mutation_date',
        'part_number',
        'store_location',
        'quantity',
        'serial_no',
        'loading_list',
        'delivery',
        'customer',
        'npk',
        'flag_confirm',
        'quantity_edited',
        'npk_edited',
        'part_number_customer',
        'back_number',
        'dock',
        'scanned_at',
        'created_by'
    ];

    protected $casts = [
        'mutation_date' => 'datetime',
        'scanned_at' => 'datetime',
        'quantity' => 'integer',
        'quantity_edited' => 'integer',
        'flag_confirm' => 'boolean',
    ];

    /**
     * Relationship to PisPart
     */
    public function pisPart(): BelongsTo
    {
        return $this->belongsTo(PisPart::class, 'part_number', 'part_number');
    }

    /**
     * Get counter (total scans) for a part number on a specific date
     */
    public static function getCounter(string $partNumber, string $delivery = null, string $dock = null, string $date = null): int
    {
        $date = $date ?? Carbon::today()->toDateString();
        
        $query = self::where('part_number', $partNumber)
            ->whereDate('mutation_date', $date);

        if ($delivery) {
            $query->where('delivery', $delivery);
        }

        if ($dock) {
            $query->where('dock', $dock);
        }

        return $query->count();
    }

    /**
     * Get last scans for display
     */
    public static function getLastScans(string $partNumber = null, int $limit = 5): array
    {
        $query = self::orderBy('mutation_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($partNumber) {
            $query->where('part_number', $partNumber);
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($mutation) {
                return [
                    'part_number' => $mutation->part_number_customer ?? $mutation->part_number,
                    'serial_no' => $mutation->serial_no,
                    'quantity' => $mutation->quantity,
                    'delivery' => $mutation->delivery,
                    'dock' => $mutation->dock,
                    'scanned_at' => $mutation->mutation_date ? $mutation->mutation_date->format('H:i:s') : null,
                ];
            })
            ->toArray();
    }

    /**
     * Create mutation from scan data
     */
    public static function createFromScan(array $scanData): self
    {
        return self::create([
            'mutation_date' => $scanData['mutation_date'] ?? Carbon::now(),
            'part_number' => $scanData['part_number'],
            'part_number_customer' => $scanData['part_number_customer'] ?? null,
            'store_location' => $scanData['store_location'] ?? null,
            'quantity' => $scanData['quantity'] ?? 1,
            'serial_no' => $scanData['serial_no'] ?? null,
            'loading_list' => $scanData['loading_list'] ?? null,
            'delivery' => $scanData['delivery'] ?? null,
            'customer' => $scanData['customer'] ?? null,
            'dock' => $scanData['dock'] ?? null,
            'npk' => $scanData['npk'] ?? null,
            'back_number' => $scanData['back_number'] ?? null,
            'flag_confirm' => $scanData['flag_confirm'] ?? false,
            'scanned_at' => Carbon::now(),
            'created_by' => $scanData['created_by'] ?? null,
        ]);
    }
}

