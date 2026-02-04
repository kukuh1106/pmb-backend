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
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran')->unique();
            $table->string('kode_akses'); // hashed
            $table->string('nama_lengkap');
            $table->string('no_whatsapp');
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('asal_institusi')->nullable();
            $table->string('foto_path')->nullable();
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->foreignId('jadwal_ujian_id')->nullable()->constrained('jadwal_ujian')->onDelete('set null');
            $table->foreignId('periode_id')->constrained('periode_pendaftaran')->onDelete('cascade');
            $table->decimal('nilai_ujian', 5, 2)->nullable();
            $table->enum('status_kelulusan', ['belum_diproses', 'lulus', 'tidak_lulus'])->default('belum_diproses');
            $table->enum('status_pendaftaran', ['registrasi', 'biodata_lengkap', 'jadwal_dipilih', 'selesai'])->default('registrasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftar');
    }
};
