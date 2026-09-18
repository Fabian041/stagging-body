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

    public function test_560b_still_uses_production_interlock(): void
    {
        $pair = new KanbanPairing();
        $pair->project = '560B';

        $this->assertTrue($pair->usesProductionInterlock());
    }

    public function test_4l45w_keeps_interlock_but_allows_repeated_serials(): void
    {
        $pair = new KanbanPairing();
        $pair->project = ' 4l45w ';

        $this->assertTrue($pair->usesProductionInterlock());
        $this->assertTrue($pair->allowsRepeatedSerials());
    }

    public function test_other_projects_still_require_distinct_serials(): void
    {
        foreach (['660A', '560B', '700A'] as $project) {
            $pair = new KanbanPairing();
            $pair->project = $project;

            $this->assertFalse($pair->allowsRepeatedSerials());
        }
    }
}
