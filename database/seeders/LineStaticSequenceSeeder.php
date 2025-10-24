<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LineStaticSequence;

class LineStaticSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // MA1 : CI12, CI14, CI13 (dom), CI11
            ['line'=>'MA001','back_no'=>'CI12','seq_no'=>1,'dock_hint'=>null],
            ['line'=>'MA001','back_no'=>'CI14','seq_no'=>2,'dock_hint'=>null],
            ['line'=>'MA001','back_no'=>'CI13','seq_no'=>3,'dock_hint'=>'DOM'],
            ['line'=>'MA001','back_no'=>'CI11','seq_no'=>4,'dock_hint'=>null],

            // MA2 : CI17
            ['line'=>'MA002','back_no'=>'CI17','seq_no'=>1,'dock_hint'=>null],

            // MA3 : CI15, CI13 (exp)
            ['line'=>'MA003','back_no'=>'CI15','seq_no'=>1,'dock_hint'=>null],
            ['line'=>'MA003','back_no'=>'CI13','seq_no'=>2,'dock_hint'=>'EXP'],

            // MA4 : CI16
            ['line'=>'MA004','back_no'=>'CI16','seq_no'=>1,'dock_hint'=>null],
        ];

        foreach ($rows as $r) {
            LineStaticSequence::updateOrCreate(
                ['line' => $r['line'], 'seq_no' => $r['seq_no']],
                $r + ['is_active' => true]
            );
        }
    }
}