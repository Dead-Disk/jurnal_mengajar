<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jam_pulang', 'mode_pulang')) {
            Schema::table('jam_pulang', function (Blueprint $table) {
                $table->string('mode_pulang', 20)->nullable()->after('max_jam_ke');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jam_pulang', 'mode_pulang')) {
            Schema::table('jam_pulang', function (Blueprint $table) {
                $table->dropColumn('mode_pulang');
            });
        }
    }
};
