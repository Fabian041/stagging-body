<?php

namespace Tests\Unit;

use App\Models\InternalPart;
use App\Models\Kanban;
use Tests\TestCase;

class PullingKanbanStatusTest extends TestCase
{
    public function test_kanban_status_contract(): void
    {
        $this->assertSame(0, Kanban::STATUS_NOT_PRODUCED);
        $this->assertSame(1, Kanban::STATUS_PRODUCED);
        $this->assertSame(2, Kanban::STATUS_PULLED);

        $this->assertTrue((new Kanban(['status' => 0]))->canBePulled());
        $this->assertTrue((new Kanban(['status' => 1]))->canBePulled());
        $this->assertFalse((new Kanban(['status' => 2]))->canBePulled());
        $this->assertFalse((new Kanban(['status' => 3]))->canBePulled());
    }

    public function test_excel_back_numbers_bypass_pulling_kanban_status_gate(): void
    {
        $backNumbers = config('pulling.kanban_status_exempt_back_numbers');

        $this->assertCount(57, $backNumbers);
        $this->assertTrue((new InternalPart(['back_number' => 'NCX1']))->isPullingKanbanStatusExempt());
        $this->assertTrue((new InternalPart(['back_number' => ' mm0p ']))->isPullingKanbanStatusExempt());
        $this->assertFalse((new InternalPart(['back_number' => 'OTHER']))->isPullingKanbanStatusExempt());
    }
}
