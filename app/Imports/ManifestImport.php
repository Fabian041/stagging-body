<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Manifest;
use App\Models\CustomerPart;
use App\Models\ManifestDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ManifestImport implements ToCollection, WithHeadingRow, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        try {
            DB::beginTransaction();

            // initialize empty array
            $manifests = [];

            foreach ($rows as $row) {

                $pdsNumber = $row['pds'];
                $customerCode = $row['customer'];
                $partNumberCustomer = $row['customer_parts'];
                $cycle = $row['cycle'];
                $deliveryDate = $row['first_shipping'];  
                $shippingDate = $row['shipping_result'];
                $totalQty = $row['order_qty'];
                $qtyPerKanban = $row['qtybox'];
                $kanbanQty = $totalQty/$qtyPerKanban;

                // get part number length
                $codeLength = strlen($partNumberCustomer);

                // check last two digit of partNumber 
                $lastDigit = substr($partNumberCustomer, -2);

                // check part number customer length
                if($codeLength == 12 || $codeLength == 14){
                    // TMMIN
                    if($lastDigit != '00'){
                        $convertedPartNumber = substr($partNumberCustomer, 0, 5) . '-' . substr($partNumberCustomer, 5, 5) . '-' . substr($partNumberCustomer, -2);
                    }else{
                        $convertedPartNumber = substr(substr_replace($partNumberCustomer, '-', 5, 0), 0, -2);
                    }
                }else if($codeLength == 10){
                    // TBINA
                    $convertedPartNumber = substr_replace($partNumberCustomer, '-', 5, 0);
                }
                
                // get cutomer id by dock number
                $customer = Customer::select('id')->where('code', $customerCode)->first();

                // get customer part id by part number customer
                $customerPart = CustomerPart::select('id')->where('part_number', $convertedPartNumber)->first();

                if(!isset($manifests[$pdsNumber])){
                    // insert into manifest master
                    $manifest = Manifest::create([
                        'pds_number' => $pdsNumber,
                        'customer_id' => $customer->id,
                        'delivery_date' => $deliveryDate,
                        'shipping_date' => $shippingDate,
                        'cycle' => $cycle
                    ]);
                    $manifests[$pdsNumber] = $manifest->id;
                }
                // insert the manifest detail
                ManifestDetail::create([
                    'manifest_id' => $manifests[$pdsNumber],
                    'customer_part_id' => $customerPart ? $customerPart->id : null,
                    'kanban_qty' => $kanbanQty,
                    'qty_per_kanban' => $qtyPerKanban,
                    'total_qty' => $totalQty,
                    'actual_qty' => 0
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
        return 2; // skip the first three rows
    }
}
