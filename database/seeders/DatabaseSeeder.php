<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProdiSeeder::class,
            SesiUjianSeeder::class,
            RuangUjianSeeder::class,
            PeriodePendaftaranSeeder::class,
            JadwalUjianSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
