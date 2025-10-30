<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LineStaticSequence;

class LineStaticSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // ========== MA1 ==========
            // Sebelumnya CI13 dock_hint 'DOM' → ganti ke 'NR'
            // Urutan: CI12, CI14, CI13(NR), CI11
            ['line'=>'MA001','back_no'=>'CI12','seq_no'=>1,'dock_hint'=>null],
            ['line'=>'MA001','back_no'=>'CI14','seq_no'=>2,'dock_hint'=>null],
            ['line'=>'MA001','back_no'=>'CI13','seq_no'=>3,'dock_hint'=>'NR'],
            ['line'=>'MA001','back_no'=>'CI11','seq_no'=>4,'dock_hint'=>null],

            // ========== MA2 ==========
            // Urutan: CI17
            ['line'=>'MA002','back_no'=>'CI17','seq_no'=>1,'dock_hint'=>null],

            // ========== MA3 ==========
            // Urutan: CI15, CI13(EXP)
            ['line'=>'MA003','back_no'=>'CI15','seq_no'=>1,'dock_hint'=>null],
            ['line'=>'MA003','back_no'=>'CI13','seq_no'=>2,'dock_hint'=>'EXP'],

            // ========== MA4 ==========
            // Urutan: CI16
            ['line'=>'MA004','back_no'=>'CI16','seq_no'=>1,'dock_hint'=>null],

            // ========== MA6 ==========
            // Catatan: CI14 @ 6I lalu CI12 (tanpa hint)
            // Urutan: CI14(6I), CI12
            ['line'=>'MA006','back_no'=>'CI14','seq_no'=>1,'dock_hint'=>'6I'],
            ['line'=>'MA006','back_no'=>'CI12','seq_no'=>2,'dock_hint'=>null],

            // ========== MA7 ==========
            // Urutan: CI13(EXP), CI11
            ['line'=>'MA007','back_no'=>'CI13','seq_no'=>1,'dock_hint'=>'EXP'],
            ['line'=>'MA007','back_no'=>'CI11','seq_no'=>2,'dock_hint'=>null],

            // ========== MA8 ==========
            // Keterangan kamu: CI13 (NR), CI14 (ADM)
            // Tambahanmu: CI14 di MA8 adalah Dock NR maupun EXP → kita pecah dua hint (NR lalu EXP)
            // Urutan: CI13(NR), CI14(NR), CI14(EXP)
            ['line'=>'MA008','back_no'=>'CI13','seq_no'=>1,'dock_hint'=>'NR'],
            ['line'=>'MA008','back_no'=>'CI14','seq_no'=>2,'dock_hint'=>'NR'],
            ['line'=>'MA008','back_no'=>'CI14','seq_no'=>3,'dock_hint'=>'EXP'],
        ];

        foreach ($rows as $r) {
            LineStaticSequence::updateOrCreate(
                ['line' => $r['line'], 'seq_no' => $r['seq_no']],
                $r + ['is_active' => true]
            );
        }
    }
}   