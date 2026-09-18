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
            foreach ($rows as $row) {
                DB::table('body_kanban_pairings')
                    ->where('project', self::PROJECT)
                    ->where('assembly_part', $row['fg_part'])
                    ->where('painting_part', $row['painting_part'])
                    ->delete();

                $this->upsertPair([
                    'product' => $row['sa_name'],
                    'part_name' => $row['fg_name'],
                    'part_number_customer' => $row['fg_part'],
                    'assembly_part' => $row['fg_part'],
                    'assembly_back_number' => $row['fg_back'],
                    'painting_part' => $row['sa_part'],
                    'painting_back_number' => $row['sa_back'],
                ], $now);

                $this->upsertPair([
                    'product' => $row['painting_name'],
                    'part_name' => $row['sa_name'],
                    'part_number_customer' => $row['sa_part'],
                    'assembly_part' => $row['sa_part'],
                    'assembly_back_number' => $row['sa_back'],
                    'painting_part' => $row['painting_part'],
                    'painting_back_number' => $row['painting_back'],
                ], $now);
            }
        });

        $this->command?->info('Seeded '.(count($rows) * 2).' kanban pairing rows for project '.self::PROJECT.'.');
    }

    private function upsertPair(array $row, $now): void
    {
        DB::table('body_kanban_pairings')->updateOrInsert(
            [
                'project' => self::PROJECT,
                'assembly_part' => $row['assembly_part'],
                'painting_part' => $row['painting_part'],
            ],
            [
                'product' => $row['product'],
                'part_name' => $row['part_name'],
                'part_number_customer' => $row['part_number_customer'],
                'assembly_back_number' => $row['assembly_back_number'],
                'painting_back_number' => $row['painting_back_number'],
                'qty_assy' => self::QTY,
                'qty_painting' => self::QTY,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function rows(): array
    {
        return array_merge(
            $this->buildRows([
                'fg_base' => '484170-12560',
                'fg_name' => 'HANDLE ASSY, ELECTRICAL KEY, ANTENNA (PLAT WH)',
                'sa_base' => '423101-10420',
                'sa_name' => 'HANDLE, DOOR OUTSIDE, NO.1 SUB ASSY (SMART PLAT WH)',
                'painting_base' => '423115-11080',
                'painting_name' => 'HANDLE DOOR OUTSIDE. NO 1 (SMART PLAT WH)',
                'items' => [
                    ['A01', 'SUPER WHITE 2', 'SP1M', 'KO22', 'SN0Z'],
                    ['A1U', 'PLATINUM WHITE PEARL', 'SP1N', 'KO23', 'SN11'],
                    ['B1R', 'SILVER', 'SP1O', 'KO24', 'SN12'],
                    ['B2M', 'GRAY', 'SP1P', 'KO25', 'SN13'],
                    ['B38', 'DARK STEEL', 'SP27', 'KO26', 'SN14'],
                    ['C0H', 'ATTITUDE BLACK', 'SP1R', 'KO27', 'SN15'],
                    ['E2V', 'AVANT GRADE BRONZE', 'SP29', 'KO28', 'SN16'],
                    ['E3G', 'BLACKISH BROWN', 'SP1T', 'KO29', 'SN17'],
                    ['B40', 'SILVER METALLIC', 'SP2E', 'KO41', 'SN33'],
                ],
            ]),
            $this->buildRows([
                'fg_base' => '484170-12570',
                'fg_name' => 'HANDLE ASSY, ELECTRICAL KEY, ANTENNA (PLAT WOH)',
                'sa_base' => '423101-10430',
                'sa_name' => 'HANDLE, DOOR OUTSIDE, NO.1 SUB ASSY (SMART PLAT WOH)',
                'painting_base' => '423115-11090',
                'painting_name' => 'HANDLE DOOR OUTSIDE. NO 1 (SMART PLAT WOH)',
                'items' => [
                    ['A01', 'SUPER WHITE 2', 'SP1U', 'KO32', 'SN18'],
                    ['A1U', 'PLATINUM WHITE PEARL', 'SP1V', 'KO33', 'SN19'],
                    ['B1R', 'SILVER', 'SP1W', 'KO2C', 'SN20'],
                    ['B2M', 'GRAY', 'SP1X', 'KO2D', 'SN21'],
                    ['B38', 'DARK STEEL', 'SP1Y', 'KO2E', 'SN22'],
                    ['C0H', 'ATTITUDE BLACK', 'SP2B', 'KO2F', 'SN23'],
                    ['E2V', 'AVANT GRADE BRONZE', 'SP21', 'KO2G', 'SN24'],
                    ['E3G', 'BLACKISH BROWN', 'SP22', 'KO2H', 'SN25'],
                    ['B40', 'SILVER METALLIC', 'SP2F', 'KO42', 'SN34'],
                ],
            ])
        );
    }

    private function buildRows(array $group): array
    {
        return array_map(function (array $item) use ($group) {
            [$suffix, $color, $fgBack, $saBack, $paintingBack] = $item;

            return [
                'fg_part' => $group['fg_base'].'-'.$suffix,
                'fg_name' => $group['fg_name'].' '.$color,
                'fg_back' => $fgBack,
                'sa_part' => $group['sa_base'].'-'.$suffix,
                'sa_name' => $group['sa_name'].' '.$color,
                'sa_back' => $saBack,
                'painting_part' => $group['painting_base'].'-'.$suffix,
                'painting_name' => $group['painting_name'].' '.$color,
                'painting_back' => $paintingBack,
            ];
        }, $group['items']);
    }
}
