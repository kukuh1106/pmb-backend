<?php

namespace Database\Seeders;

use App\Models\RuangUjian;
use Illuminate\Database\Seeder;

class RuangUjianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruangList = [
            ['kode' => 'R101', 'nama' => 'Ruang 101', 'kapasitas' => 30],
            ['kode' => 'R102', 'nama' => 'Ruang 102', 'kapasitas' => 30],
            ['kode' => 'R103', 'nama' => 'Ruang 103', 'kapasitas' => 25],
            ['kode' => 'AULA', 'nama' => 'Aula Utama', 'kapasitas' => 100],
            ['kode' => 'LAB1', 'nama' => 'Lab Komputer 1', 'kapasitas' => 40],
        ];

        foreach ($ruangList as $ruang) {
            RuangUjian::create($ruang);
        }
    }
}
