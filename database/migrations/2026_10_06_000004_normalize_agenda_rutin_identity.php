<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('agenda_rutin')->whereNull('shift_id')->update(['shift_id' => 0]);

        $duplicateGroups = DB::table('agenda_rutin')
            ->select('hari', 'shift_id', 'is_testing_data', 'tahun_ajaran_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('hari', 'shift_id', 'is_testing_data', 'tahun_ajaran_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $duplicates = DB::table('agenda_rutin')
                ->where('hari', $group->hari)
                ->where('shift_id', $group->shift_id)
                ->where('is_testing_data', $group->is_testing_data)
                ->when(
                    $group->tahun_ajaran_id === null,
                    fn ($query) => $query->whereNull('tahun_ajaran_id'),
                    fn ($query) => $query->where('tahun_ajaran_id', $group->tahun_ajaran_id)
                )
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('id');

            DB::table('agenda_rutin')->whereIn('id', $duplicates->skip(1))->delete();
        }

        Schema::table('agenda_rutin', function (Blueprint $table) {
            $table->dropUnique('agenda_rutin_tahun_shift_testing_unique');
            $table->unique(
                ['hari', 'shift_id', 'is_testing_data', 'tahun_ajaran_id'],
                'agenda_rutin_tahun_shift_testing_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('agenda_rutin', function (Blueprint $table) {
            $table->dropUnique('agenda_rutin_tahun_shift_testing_unique');
            $table->unique(
                ['hari', 'jam_ke', 'shift_id', 'is_testing_data', 'tahun_ajaran_id'],
                'agenda_rutin_tahun_shift_testing_unique'
            );
        });
    }
};
