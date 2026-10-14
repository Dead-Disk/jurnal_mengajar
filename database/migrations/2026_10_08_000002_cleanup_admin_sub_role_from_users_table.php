<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hapus akun testing dummy (misal: username 'test') jika memiliki sub_role 'admin'
        DB::table('users')
            ->where('username', 'test')
            ->where('sub_role', 'admin')
            ->delete();

        // 2. Migrasi akun pengguna existing lain yang masih ber-sub_role 'admin' ke 'petugas_tu'
        DB::table('users')
            ->where('sub_role', 'admin')
            ->update([
                'sub_role' => 'petugas_tu',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu me-revert akun yang telah dimigrasi/dibersihkan.
    }
};
