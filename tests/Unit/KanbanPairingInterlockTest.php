<?php

namespace Tests\Unit;

use App\Models\KanbanPairing;
use PHPUnit\Framework\TestCase;

class KanbanPairingInterlockTest extends TestCase
{
    public function test_660a_uses_production_interlock(): void
    {
        $pair = new KanbanPairing();
        $pair->project = ' 660a ';

        $this->assertTrue($pair->usesProductionInterlock());
    }

    public function test_other_project_does_not_use_production_interlock_yet(): void
    {
        $pair = new KanbanPairing();
        $pair->project = '700A';

        $this->assertFalse($pair->usesProductionInterlock());
    }
}
