<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MachineAuthorizationResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'authorized' => (bool) $this->resource['authorized'],
            'reason' => $this->resource['reason'],
            'transaction_id' => $this->resource['transaction_id'],
            'employee' => $this->resource['employee'],
            'machine' => $this->resource['machine'],
            'cache' => $this->resource['cache'],
        ];
    }
}
