<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jadwal_pelajaran', 'mode_jadwal')) {
            Schema::table('jadwal_pelajaran', function (Blueprint $table) {
                $table->string('mode_jadwal')->nullable()->after('id_tahun_ajaran');
            });
        }

        // Isi nilai mode_jadwal untuk baris lama: default 'global'
        \DB::table('jadwal_pelajaran')
            ->whereNull('mode_jadwal')
            ->update(['mode_jadwal' => 'global']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('jadwal_pelajaran', 'mode_jadwal')) {
            Schema::table('jadwal_pelajaran', function (Blueprint $table) {
                $table->dropColumn('mode_jadwal');
            });
        }
    }
};
