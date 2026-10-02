<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BodyKanbanPairing560BSeeder extends Seeder
{
    private const PROJECT = '560B';
    private const QTY = 6;

    public function run(): void
    {
        $now = now();
        $rows = $this->rows();

        DB::transaction(function () use ($rows, $now) {
            DB::table('body_kanban_pairings')->where('project', self::PROJECT)->delete();

            DB::table('body_kanban_pairings')->insert(array_map(fn (array $row) => [
                'project' => self::PROJECT,
                'product' => $row['painting_name'],
                'part_name' => $row['assembly_name'],
                'part_number_customer' => $row['assembly_part'],
                'assembly_part' => $row['assembly_part'],
                'assembly_back_number' => $row['assembly_back'],
                'painting_part' => $row['painting_part'],
                'painting_back_number' => $row['painting_back'],
                'qty_assy' => self::QTY,
                'qty_painting' => self::QTY,
                'created_at' => $now,
                'updated_at' => $now,
            ], $rows));
        });

        $this->command?->info('Seeded '.count($rows).' direct kanban pairing rows for project '.self::PROJECT.'.');
    }

    private function rows(): array
    {
        return [
            [
                'assembly_part' => '484170-12580-B40',
                'assembly_name' => 'HANDLE ASSY, ELECTRICAL KEY, ANTENNA (PAINT WH) SILVER METALLIC',
                'assembly_back' => 'SP2C',
                'painting_part' => '423115-11060-B40',
                'painting_name' => 'HANDLE DOOR OUTSIDE. NO 1 ( SMART PAINT WH) SILVER METALLIC',
                'painting_back' => 'SN31',
            ],
            [
                'assembly_part' => '484170-12590-B40',
                'assembly_name' => 'HANDLE ASSY, ELECTRICAL KEY, ANTENNA (PAINT WOH) SILVER METALLIC',
                'assembly_back' => 'SP2D',
                'painting_part' => '423115-11070-B40',
                'painting_name' => 'HANDLE DOOR OUTSIDE. NO 1 ( SMART PAINT WOH) SILVER METALLIC',
                'painting_back' => 'SN32',
            ],
            [
                'assembly_part' => '423110-14450-B40',
                'assembly_name' => 'HANDLE ASSY, FRONT DOOR OUTSIDE (PAINT WH) SILVER METALLIC',
                'assembly_back' => 'KP7E',
                'painting_part' => '423115-11190-B40',
                'painting_name' => 'HANDLE, DOOR OUTSIDE, NO.1 (NORMAL PAINT WH) SILVER METALLIC',
                'painting_back' => 'KN71',
            ],
            [
                'assembly_part' => '423110-14360-B40',
                'assembly_name' => 'HANDLE ASSY, FRONT DOOR OUTSIDE (PAINT WOH) SILVER METALLIC',
                'assembly_back' => 'KP7F',
                'painting_part' => '423115-11100-B40',
                'painting_name' => 'HANDLE, DOOR OUTSIDE, NO.1 (NORMAL PAINT WOH) SILVER METALLIC',
                'painting_back' => 'KN72',
            ],
            [
                'assembly_part' => '423110-14370-B40',
                'assembly_name' => 'HANDLE ASSY, FRONT DOOR OUTSIDE (PLAT WOH) SILVER METALLIC',
                'assembly_back' => 'KP7G',
                'painting_part' => '423115-11120-B40',
                'painting_name' => 'HANDLE, DOOR OUTSIDE, NO.1 (NORMAL PLAT WOH) SILVER METALLIC',
                'painting_back' => 'KN73',
            ],
        ];
    }
}
