<?php

namespace Tests\Unit;

use App\Models\ProductionStock;
use PHPUnit\Framework\TestCase;

class ProductionStockAdjustmentTest extends TestCase
{
    public function test_adjustment_delta_uses_mutation_direction(): void
    {
        $this->assertSame(['type' => 'supply', 'qty' => 7], ProductionStock::adjustmentMutationFor(3, 10));
        $this->assertSame(['type' => 'checkout', 'qty' => 4], ProductionStock::adjustmentMutationFor(10, 6));
        $this->assertNull(ProductionStock::adjustmentMutationFor(5, 5));
    }
}
