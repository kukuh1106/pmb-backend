<?php

namespace Database\Seeders;

use App\Models\JadwalUjian;
use App\Models\PeriodePendaftaran;
use App\Models\RuangUjian;
use App\Models\SesiUjian;
use Illuminate\Database\Seeder;

class JadwalUjianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periode = PeriodePendaftaran::where('is_active', true)->first();
        $sesiPagi = SesiUjian::where('nama', 'Sesi Pagi')->first();
        $sesiSiang = SesiUjian::where('nama', 'Sesi Siang')->first();
        $ruang = RuangUjian::all();

        if (!$periode || !$sesiPagi || !$sesiSiang || $ruang->isEmpty()) {
            return;
        }

        // Create jadwal for next month
        $dates = [
            '2026-02-15',
            '2026-02-16',
            '2026-02-22',
            '2026-02-23',
        ];

        foreach ($dates as $date) {
            foreach ($ruang as $r) {
                // Morning session
                JadwalUjian::create([
                    'periode_id' => $periode->id,
                    'tanggal' => $date,
                    'sesi_id' => $sesiPagi->id,
                    'ruang_id' => $r->id,
                    'kuota' => $r->kapasitas,
                ]);

                // Afternoon session
                JadwalUjian::create([
                    'periode_id' => $periode->id,
                    'tanggal' => $date,
                    'sesi_id' => $sesiSiang->id,
                    'ruang_id' => $r->id,
                    'kuota' => $r->kapasitas,
                ]);
            }
        }
    }
}
