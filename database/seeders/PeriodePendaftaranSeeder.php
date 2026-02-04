<?php

namespace Database\Seeders;

use App\Models\PeriodePendaftaran;
use Illuminate\Database\Seeder;

class PeriodePendaftaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PeriodePendaftaran::create([
            'nama' => 'PMB 2026 Gelombang 1',
            'tanggal_buka' => '2026-01-01',
            'tanggal_tutup' => '2026-03-31',
            'is_active' => true,
        ]);

        PeriodePendaftaran::create([
            'nama' => 'PMB 2026 Gelombang 2',
            'tanggal_buka' => '2026-04-01',
            'tanggal_tutup' => '2026-06-30',
            'is_active' => false,
        ]);
    }
}
