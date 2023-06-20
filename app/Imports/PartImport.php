<?php

namespace App\Imports;

use App\Models\CustomerPart;
use App\Models\InternalPart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartImport implements ToCollection, WithHeadingRow, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            // initialize empty array
            $internalParts = [];

            foreach($rows as $row) {
                
                // initialize the collection
                $partName = $row['part_name'];
                $partNumberInternal = $row['part_no_aiia'];
                $partNumberCustomer = $row['part_no_customer'];
                $backNumberInternal = $row['aiia_back_no'];
                $backNumberCustomer = $row['cust_back_no'];
                $qtyPerKanban = $row['qty_per_kbn'];
                $lineId = $row['line_id'];
                $customerId = $row['customer_id'];

                if(!isset($internalParts[$partNumberInternal])){

                    // insert into internal parts table
                    $internalPart =  InternalPart::create([
                        'line_id' => $lineId,
                        'part_number' => $partNumberInternal,
                        'back_number' => $backNumberInternal,
                        'part_name' => $partName,
                    ]);

                    // insert internalPart table id into spesific part number in array assoc
                    $internalParts[$partNumberInternal] = $internalPart->id;
                }

                // insert the customer part
                $customerPart = \App\Models\CustomerPart::create([
                    'internal_part_id' => $internalParts[$partNumberInternal],
                    'customer_id' => $customerId,
                    'part_number' => $partNumberCustomer,
                    'back_number' => $backNumberCustomer,
                    'qty_per_kanban' => $qtyPerKanban
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            dd($th);
        }
    }

    public function startRow(): int
    {
        return 3; // skip the first three rows
    }
}
