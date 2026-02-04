<?php

namespace Database\Seeders;

use App\Models\SesiUjian;
use Illuminate\Database\Seeder;

class SesiUjianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sesiList = [
            [
                'nama' => 'Sesi Pagi',
                'jam_mulai' => '08:00',
                'jam_selesai' => '11:00',
            ],
            [
                'nama' => 'Sesi Siang',
                'jam_mulai' => '13:00',
                'jam_selesai' => '16:00',
            ],
        ];

        foreach ($sesiList as $sesi) {
            SesiUjian::create($sesi);
        }
    }
}
