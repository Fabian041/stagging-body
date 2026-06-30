<?php

namespace Tests\Feature;

use App\Models\QualityInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QualityScanFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_start_from_any_scan_lookup_save_inspection_and_open_detail_checksheet(): void
    {
        $user = User::factory()->make([
            'id' => 999999,
            'name' => 'Quality Tester',
            'role' => 'QC',
        ]);

        $this->actingAs($user);

        $scanResponse = $this->postJson(route('quality.lookupKanban'), [
            'kanban_code' => 'SCAN TEST GENERAL QTY 10',
        ]);

        $scanResponse
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'line' => 'QC GATE R1',
                ],
            ])
            ->assertJsonPath('data.check_items.0.item', 'Mass check')
            ->assertJsonPath('data.check_items.0.std_upper', '158 gr')
            ->assertJsonPath('data.check_items.0.std_lower', '140 gr');

        $scanData = $scanResponse->json('data');

        $storeResponse = $this->postJson(route('quality.storeInspection'), [
            'kanban_code' => $scanData['kanban_code'],
            'check_type' => '100% CHECK',
            'shift' => '1',
            'line' => $scanData['line'],
            'pic' => 'Quality Tester',
            'part_name' => $scanData['part_name'],
            'part_number' => $scanData['part_number'],
            'back_number' => $scanData['back_number'],
            'qty_per_box' => (string) $scanData['qty_per_box'],
            'details' => [
                [
                    'item' => $scanData['check_items'][0]['item'],
                    'standard' => $scanData['check_items'][0]['standard'],
                    'result' => 'OK',
                    'remark' => 'Actual: 150 gr',
                ],
            ],
        ]);

        $storeResponse
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'judgement' => 'OK',
                    'approval_status' => 'AUTO_RELEASE',
                ],
            ]);

        $inspectionId = $storeResponse->json('data.inspection_id');

        $this->assertDatabaseHas('quality_inspections', [
            'id' => $inspectionId,
            'kanban_code' => 'SCAN TEST GENERAL QTY 10',
            'judgement' => 'OK',
            'approval_status' => 'AUTO_RELEASE',
        ]);

        $this->assertDatabaseHas('quality_inspection_details', [
            'quality_inspection_id' => $inspectionId,
            'check_item' => 'Mass check',
            'result' => 'OK',
            'remark' => 'Actual: 150 gr',
        ]);

        $inspection = QualityInspection::findOrFail($inspectionId);

        $detailResponse = $this->get(route('quality.show', $inspection));
        $historyResponse = $this->get(route('quality.history'));

        $detailResponse
            ->assertOk()
            ->assertSee('Detail Checksheet')
            ->assertSee('Mass check')
            ->assertSee('Actual: 150 gr');

        $historyResponse
            ->assertOk()
            ->assertSee('SCAN TEST GENERAL QTY 10')
            ->assertSee('OK');
    }

    public function test_store_inspection_saves_ng_result_to_history_and_detail(): void
    {
        $user = User::factory()->make([
            'id' => 999998,
            'name' => 'Quality Tester',
            'role' => 'QC',
        ]);

        $this->actingAs($user);

        $storeResponse = $this->postJson(route('quality.storeInspection'), [
            'kanban_code' => 'SCAN TEST NG QTY 10',
            'check_type' => '100% CHECK',
            'line' => 'QC GATE R1',
            'pic' => 'Quality Tester',
            'part_name' => 'PART SAMPLE QC ASSY',
            'part_number' => 'SCAN-TEST-NG',
            'back_number' => '-',
            'qty_per_box' => '10',
            'details' => [
                [
                    'item' => 'Mass check',
                    'standard' => 'Std Upper: 158 gr | Std Lower: 140 gr',
                    'result' => 'NG',
                    'remark' => 'Actual: 160 gr',
                ],
            ],
        ]);

        $storeResponse
            ->assertOk()
            ->assertJsonPath('data.judgement', 'NG')
            ->assertJsonPath('data.approval_status', 'NEED_APPROVAL');

        $inspectionId = $storeResponse->json('data.inspection_id');

        $this->assertDatabaseHas('quality_inspections', [
            'id' => $inspectionId,
            'kanban_code' => 'SCAN TEST NG QTY 10',
            'judgement' => 'NG',
            'approval_status' => 'NEED_APPROVAL',
        ]);

        $this->assertDatabaseHas('quality_inspection_details', [
            'quality_inspection_id' => $inspectionId,
            'check_item' => 'Mass check',
            'result' => 'NG',
            'remark' => 'Actual: 160 gr',
        ]);
    }
}