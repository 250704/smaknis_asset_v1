<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Sarpras',
                'email' => 'admin@smk.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Guru SMK',
                'email' => 'guru@smk.com',
                'role' => 'guru',
            ],
            [
                'name' => 'Kepala Sarana',
                'email' => 'sarpras@smk.com',
                'role' => 'kepala_sarana',
            ],
            [
                'name' => 'Bendahara SMK',
                'email' => 'bendahara@smk.com',
                'role' => 'bendahara',
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@smk.com',
                'role' => 'kepala_sekolah',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::query()->where('nama_role', $userData['role'])->first();

            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'nama' => $userData['name'],
                    'password' => Hash::make('12345678'),
                    'role' => $userData['role'],
                    'role_id' => $role?->id,
                    'status_akun' => 'AKTIF',
                ],
            );
        }
    }
}
