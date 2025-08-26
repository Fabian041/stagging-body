<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Kanban;
use Illuminate\Support\Str;
use App\Models\InternalPart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    // public function run()
    // {
    //     // get all part number internal id
    //     $internalCount = InternalPart::count();
    //     $internalPart = InternalPart::select('id')->get();

    //     for($i= 0; $i < $internalCount; $i++){
    //         for($j= 1; $j<=1000; $j++){
    //             $formattedSerial = sprintf('%04d', $j);
    //             Kanban::create([
    //                 'serial_number' => $formattedSerial,
    //                 'internal_part_id' => $internalPart[$i]->id,
    //             ]);
    //         }
    //     }

    //     // $lines = [
    //     //     'AS524', 'AS501', 'AS523', 'AS526', 'AS546',
    //     //     'AS561', 'AS522', 'AS600', 'AS525', 'AS547',
    //     //     'AS548', 'AS711', 'AS528', 'AS549', 'AS731',
    //     //     'TORIMETRON', 'PASSTROUGH'
    //     // ];

    //     // foreach ($lines as $line) {
    //     //     DB::table('line_qty_temp')->insert([
    //     //         'line' => $line,
    //     //         'qty' => 0,
    //     //         'created_at' => now(),
    //     //         'updated_at' => now(),
    //     //     ]);
    //     // }

    // }

    public function run()
    {
        $users = [
            ['000081', 'Adhetya Rizki'],
            ['000113', 'Supriyanto'],
            ['000114', 'Surya Adi Nugroho'],
            ['002671', 'Teza Sukardi Yantono'],
            ['002956', 'Salafudin Wakhid'],
            ['000344', 'Ahmad Syaifudin'],
            ['000364', 'Muh. Rifki Hidayat'],
            ['002645', 'Khaerul Fadli'],
            ['002746', 'Hagun Cipta Nugraha'],
            ['002964', 'Muhammad Rizik'],
            ['002805', 'Wawan Edi Cahyono'],
            ['002670', 'Ilham Ardiasyah'],
            ['000316', 'Abdul Rahman'],
            ['002745', 'Alfan Riyan Athallah'],
            ['000741', 'Faisal Luqman Al-Hakim'],
            ['002785', 'Aldi Syahputra'],
            ['002672', 'Luzein Alwedy'],
            ['002907', 'Ilham Afriansah'],
            ['000538', 'Hariyanto'],
            ['002674', 'Aldi Tri Pamungkas'],
            ['002905', 'Arju Eka Ropika'],
            ['002887', 'Jawahir'],
            ['002920', 'Arief Choirul Amal'],
            ['002934', 'Irfan Agus Satriyo'],
            ['000977', 'Muhammad Fajri Ardha'],
            ['000093', 'Riki Biyantoro'],
            ['000095', 'Supriyanto'],
            ['000206', 'Sumiyanto'],
            ['000182', 'Heri Indrayana'],
            ['002935', 'Aditya Fauzi Maulana'],
            ['002648', 'Fajar Defriyanto'],
            ['002861', 'Afif Naufal Fanani'],
            ['000540', 'Ahmad Aufa'],
            ['002647', 'Binar Fajar Nugroho'],
            ['002860', 'Marsito'],
            ['000586', 'Bambang Riyanto'],
            ['002738', 'Ilyas Syifak Arrahman'],
            ['002673', 'Bagas Setiawan'],
            ['002866', 'Mohamad Budi Kurniawan'],
            ['002924', 'Muhibudin'],
            ['002777', 'Adi Alfiansyah'],
            ['000621', 'Lasmo'],
            ['002903', 'Nazril A. Fauzan'],
            ['002762', 'Healendo Tihsan F.'],
            ['002963', 'Rafli Reza Annaufal'],
            ['002902', 'Mohamad Alan Khakiki'],
        ];

        foreach ($users as [$npk, $name]) {
            $parts = explode(' ', $name);
            $firstName = strtolower($parts[0]);
            $email = $firstName . '@gmail.com';

            // kalau sudah ada email dengan nama depan yg sama → pakai kata kedua
            if (User::where('email', $email)->exists()) {
                if (isset($parts[1])) {
                    $secondName = strtolower(preg_replace('/[^a-z0-9]/i', '', $parts[1])); // buang simbol
                    $email = $secondName . '@gmail.com';
                } else {
                    // fallback kalau cuma 1 kata
                    $email = $firstName . '.' . Str::random(3) . '@gmail.com';
                }
            }

            User::updateOrCreate(
                ['npk' => $npk],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('123456'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
