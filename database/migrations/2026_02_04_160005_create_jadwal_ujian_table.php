<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_pendaftaran')->onDelete('cascade');
            $table->date('tanggal');
            $table->foreignId('sesi_id')->constrained('sesi_ujian')->onDelete('cascade');
            $table->foreignId('ruang_id')->constrained('ruang_ujian')->onDelete('cascade');
            $table->integer('kuota');
            $table->integer('terisi')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: satu ruang hanya bisa 1 sesi per tanggal
            $table->unique(['tanggal', 'sesi_id', 'ruang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian');
    }
};
