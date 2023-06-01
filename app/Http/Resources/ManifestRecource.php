<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManifestRecource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'status' => $this->status,
            'code' => $this->code,
            'data' => [
                'pds_number' => $this->pds_number,
                'customer' => $this->customer,
                'cycle' => $this->cycle,
                'items' => [
                    'part_number_internal' => $this->part_number_internal,
                    'part_number_customer' => $this->part_number_customer,
                    'total_qty' => $this->total_qty,
                    'actual_kanban_qty' => $this->actual_kanban_qty,
                    'total_kanban_qty' => $this->total_kanban_qty
                ]
            ]
        ];
    }
}
