<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\ProductionStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Calculation;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class StockImport implements ToCollection, WithHeadingRow, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            foreach ($rows as $row) {
                
                // get all row needed
                $backNumber = $row['back_no'];
                $qty = $row['total_qty'];

                // get id at internal part table based on back number
                $internalPart = InternalPart::select('id')->where('back_number', $backNumber)->first();

                if ($internalPart) {
                    // Use the getValue() method to get the calculated value of $qty
                    $calculatedQty = is_numeric($qty) ? $qty : $qty->getValue();

                    // insert into production stocks
                    ProductionStock::create([
                        'internal_part_id' => $internalPart->id,
                        'date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'current_stock' => $calculatedQty // Use the calculated value here
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            dd($th);
        }
    }

    public function startRow(): int
    {
        return 2; // skip the first three rows
    }
}
