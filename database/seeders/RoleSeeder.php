<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'guru',
            'kepala_sarana',
            'bendahara',
            'kepala_sekolah',
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['nama_role' => $role],
                ['nama_role' => $role],
            );
        }
    }
}
