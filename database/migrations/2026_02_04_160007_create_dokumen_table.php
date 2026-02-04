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
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftar_id')->constrained('pendaftar')->onDelete('cascade');
            $table->enum('jenis_dokumen', [
                'ijazah',
                'transkrip',
                'ktp',
                'pas_foto',
                'surat_rekomendasi',
                'proposal',
                'lainnya'
            ]);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->enum('status_verifikasi', ['pending', 'valid', 'tidak_valid'])->default('pending');
            $table->text('catatan')->nullable(); // alasan jika tidak valid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
