<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $jb = Karyawan::create([
            'nip' => '04832004',
            'nama_karyawan' => 'Muhammad Jibran Al Fabian',
            'divisi' => 'Website Developer',
            'jabatan' => 'Junior Website Developer'
        ]);
        User::create([
            'id_karyawan' => 1,
            'username' => 'jbfab',
            'password' => Hash::make('jb1234'),
            'role' => 'admin'
        ]);
    }
}
