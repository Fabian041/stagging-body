<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionStock extends Model
{
    use HasFactory;

    protected  $table = 'production_stocks';

    protected $guarded = ['id'];

    public static function adjustmentMutationFor(int $currentStock, int $targetStock): ?array
    {
        $diff = $targetStock - $currentStock;

        if ($diff === 0) {
            return null;
        }

        return [
            'type' => $diff > 0 ? 'supply' : 'checkout',
            'qty' => abs($diff),
        ];
    }

    public static function adjustCurrentStock(int $internalPartId, int $targetStock, ?string $npk = null): void
    {
        DB::transaction(function () use ($internalPartId, $targetStock, $npk) {
            static::where('internal_part_id', $internalPartId)->lockForUpdate()->first();

            $currentStock = (int) DB::table('mutations')
                ->where('internal_part_id', $internalPartId)
                ->selectRaw("
                    COALESCE(SUM(
                        CASE
                            WHEN type = 'supply' THEN COALESCE(qty, 0)
                            WHEN type = 'checkout' THEN -COALESCE(qty, 0)
                            ELSE 0
                        END
                    ), 0) as current_stock
                ")
                ->value('current_stock');

            $adjustment = static::adjustmentMutationFor($currentStock, $targetStock);
            $now = Carbon::now();

            if ($adjustment) {
                Mutation::create([
                    'internal_part_id' => $internalPartId,
                    'serial_number' => 'ADJUSTMENT',
                    'qty' => $adjustment['qty'],
                    'type' => $adjustment['type'],
                    'is_adjustment' => true,
                    'npk' => $npk ?: 'SYS',
                    'date' => $now->toDateTimeString(),
                ]);
            }

            static::updateOrCreate(
                ['internal_part_id' => $internalPartId],
                [
                    'date' => $now->toDateTimeString(),
                    'current_stock' => $targetStock,
                ]
            );
        });
    }
}
