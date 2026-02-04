<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodiList = [
            // S2 Programs
            ['kode' => 'MIK', 'nama' => 'Magister Ilmu Komunikasi', 'jenjang' => 'S2'],
            ['kode' => 'MH', 'nama' => 'Magister Hukum', 'jenjang' => 'S2'],
            ['kode' => 'MM', 'nama' => 'Magister Manajemen', 'jenjang' => 'S2'],
            ['kode' => 'MPd', 'nama' => 'Magister Pendidikan', 'jenjang' => 'S2'],
            ['kode' => 'MTI', 'nama' => 'Magister Teknologi Informasi', 'jenjang' => 'S2'],
            ['kode' => 'MAk', 'nama' => 'Magister Akuntansi', 'jenjang' => 'S2'],
            
            // S3 Programs
            ['kode' => 'DIK', 'nama' => 'Doktor Ilmu Komunikasi', 'jenjang' => 'S3'],
            ['kode' => 'DH', 'nama' => 'Doktor Hukum', 'jenjang' => 'S3'],
            ['kode' => 'DM', 'nama' => 'Doktor Manajemen', 'jenjang' => 'S3'],
        ];

        foreach ($prodiList as $prodi) {
            Prodi::create($prodi);
        }
    }
}
