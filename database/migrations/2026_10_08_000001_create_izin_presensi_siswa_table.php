<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_presensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('id_kelas')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('id_guru_piket')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['sakit', 'izin']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('keterangan', 255)->nullable();
            $table->string('foto_surat')->nullable();
            $table->boolean('is_testing_data')->default(false);
            $table->timestamps();

            $table->unique(['id_siswa', 'tanggal_mulai', 'tanggal_selesai'], 'izin_presensi_siswa_rentang_unique');
            $table->index('is_testing_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_presensi_siswa');
    }
};
