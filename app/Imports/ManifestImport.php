<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\Manifest;
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

                $pdsNumber = $row['pds_manifest'];
                $customerDock = $row['dock_code'];
                $partNumberCustomer = $row['part_no'];
                $cycle = $row['cycle'];
                $deliveryDate = $row['delivery_date_etd_aiia'];  
                $shippingDate = $row['delivery_date_eta_tmmin'];
                $kanbanQty = $row['order_kbn'];
                $totalQty = $row['order_pcs'];
                $qtyPerKanban = $row['qtykbn'];
                
                // get cutomer id by dock number
                $customer = Customer::select('id')->where('dock', $customerDock)->first();

                // get customer part id by part number customer
                $customerPart = CustomerPart::select('id')->where('part_number', $partNumberCustomer)->first();

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
