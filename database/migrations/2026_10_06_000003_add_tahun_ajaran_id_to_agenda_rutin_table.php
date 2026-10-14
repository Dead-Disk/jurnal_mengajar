<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_rutin', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')
                ->nullable()
                ->after('jam_ke')
                ->constrained('tahun_ajaran')
                ->nullOnDelete();
        });

        $tahunAjaranAktif = DB::table('tahun_ajaran')
            ->where('is_active', true)
            ->get(['id', 'is_testing_data']);

        foreach ($tahunAjaranAktif as $tahunAjaran) {
            DB::table('agenda_rutin')
                ->where('is_testing_data', $tahunAjaran->is_testing_data)
                ->whereNull('tahun_ajaran_id')
                ->update(['tahun_ajaran_id' => $tahunAjaran->id]);
        }

        Schema::table('agenda_rutin', function (Blueprint $table) {
            $table->dropUnique('agenda_rutin_hari_shift_testing_unique');
            $table->unique(
                ['hari', 'jam_ke', 'shift_id', 'is_testing_data', 'tahun_ajaran_id'],
                'agenda_rutin_tahun_shift_testing_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('agenda_rutin', function (Blueprint $table) {
            $table->dropUnique('agenda_rutin_tahun_shift_testing_unique');
            $table->dropConstrainedForeignId('tahun_ajaran_id');
            $table->unique(
                ['hari', 'jam_ke', 'shift_id', 'is_testing_data'],
                'agenda_rutin_hari_shift_testing_unique'
            );
        });
    }
};
