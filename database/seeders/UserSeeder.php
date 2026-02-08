<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Sarpras',
            'email' => 'admin@smk.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Guru SMK',
            'email' => 'guru@smk.com',
            'password' => Hash::make('12345678'),
            'role' => 'guru',
        ]);

        User::create([
            'name' => 'Kepala Sarana',
            'email' => 'sarpras@smk.com',
            'password' => Hash::make('12345678'),
            'role' => 'kepala_sarana',
        ]);

        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@smk.com',
            'password' => Hash::make('12345678'),
            'role' => 'kepala_sekolah',
        ]);
    }
}
