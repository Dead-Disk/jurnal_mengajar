<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed Data Master User:
     * HANYA 1 akun user Super Admin sesuai instruksi:
     * - ID: 1
     * - nama: SUPERADMIN
     * - username: admin
     * - email: admin@school.id
     * - password: password (di-hash dengan Hash::make)
     * - role: admin
     * - sub_role: super_admin
     * - is_active: 1
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'nama' => 'SUPERADMIN',
                'username' => 'admin',
                'email' => 'admin@school.id',
                'password' => Hash::make('password'),
                'kode_aktivasi' => 'ADM-SECURE-99',
                'role' => User::ROLE_ADMIN,
                'sub_role' => 'super_admin',
                'is_active' => 1,
            ]
        );

        $this->command->info(' [UserSeeder] 1 Akun SUPERADMIN (admin / password) berhasil dibuat/diperbarui.');
    }
}
