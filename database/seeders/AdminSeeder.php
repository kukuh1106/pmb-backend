<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        User::create([
            'username' => 'admin',
            'name' => 'Super Admin',
            'email' => 'admin@pmb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create prodi staff for each prodi
        $prodiList = Prodi::all();
        
        foreach ($prodiList as $index => $prodi) {
            User::create([
                'username' => 'prodi' . ($index + 1),
                'name' => 'Staff ' . $prodi->nama,
                'email' => 'prodi' . ($index + 1) . '@pmb.ac.id',
                'password' => Hash::make('password'),
                'role' => 'prodi',
                'prodi_id' => $prodi->id,
                'is_active' => true,
            ]);
        }
    }
}
