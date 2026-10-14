<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('nama_event');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('tipe_dampak', ['non_aktif', 'penyesuaian_jam', 'daring']);
            $table->enum('scope', ['semua_kelas', 'kelas_tertentu']);
            $table->json('target_kelas')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_testing_data')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
