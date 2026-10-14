<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Hanya membuat 1 akun Super Admin pada tabel users.
     * Tidak memanggil seeder dummy lainnya agar database benar-benar bersih.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('===================================================================');
        $this->command->info('  MEMULAI PROSES DATABASE SEEDING (HANYA AKUN SUPERADMIN)');
        $this->command->info('===================================================================');

        Schema::disableForeignKeyConstraints();

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

        Schema::enableForeignKeyConstraints();

        $this->command->info(' [DatabaseSeeder] Akun SUPERADMIN (admin / password) berhasil dibuat.');
        $this->command->info('===================================================================');
        $this->command->info('  DATABASE SEEDING SELESAI: HANYA 1 USER SUPERADMIN DI TABEL USERS');
        $this->command->info('===================================================================');
        $this->command->info('');
    }
}
