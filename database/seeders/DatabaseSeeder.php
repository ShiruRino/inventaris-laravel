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

        Karyawan::create([
            'nip' => '011107',
            'nama_karyawan' => 'Muhammad Jibran Al Fabian',
            'divisi' => 'IT',
            'jabatan' => 'Junior Website Developer',
            'kontak' => '081937361264'
        ]);
        Karyawan::create([
            'nip' => '160108',
            'nama_karyawan' => 'Dewa Rakha Adhistanaya',
            'divisi' => 'OPS',
            'jabatan' => 'Staff Lapangan',
            'kontak' => '081937361264'
        ]);
        User::create([
            'id_karyawan' => 1,
            'username' => 'jbfab',
            'password' => Hash::make('jb1234'),
            'role' => 'admin'
        ]);
        User::create([
            'id_karyawan' => 2,
            'username' => 'dwrak',
            'password' => Hash::make('dw1234'),
            'role' => 'lapangan'
        ]);
    }
}
