<?php

namespace App\Services;

use App\Models\JadwalUjian;
use App\Models\Pendaftar;
use App\Models\PeriodePendaftaran;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    /**
     * Get available jadwal for current periode
     */
    public function getAvailableJadwal(): \Illuminate\Database\Eloquent\Collection
    {
        $periode = PeriodePendaftaran::active()->first();

        if (!$periode) {
            return collect([]);
        }

        return JadwalUjian::with(['sesi', 'ruang'])
            ->where('periode_id', $periode->id)
            ->active()
            ->available()
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('sesi_id')
            ->get();
    }

    /**
     * Pilih jadwal untuk pendaftar
     */
    public function pilihJadwal(Pendaftar $pendaftar, int $jadwalId): array
    {
        $jadwal = JadwalUjian::findOrFail($jadwalId);

        // Check if jadwal is still available
        if ($jadwal->isFull()) {
            return [
                'success' => false,
                'message' => 'Kuota jadwal sudah penuh',
            ];
        }

        // Check if jadwal is active
        if (!$jadwal->is_active) {
            return [
                'success' => false,
                'message' => 'Jadwal tidak aktif',
            ];
        }

        // Check if tanggal sudah lewat
        if ($jadwal->tanggal < now()->toDateString()) {
            return [
                'success' => false,
                'message' => 'Jadwal sudah terlewat',
            ];
        }

        // Use transaction to ensure data consistency
        DB::transaction(function () use ($pendaftar, $jadwal) {
            // If pendaftar already has jadwal, decrement old jadwal terisi
            if ($pendaftar->jadwal_ujian_id) {
                JadwalUjian::where('id', $pendaftar->jadwal_ujian_id)
                    ->decrement('terisi');
            }

            // Update pendaftar jadwal
            $pendaftar->update([
                'jadwal_ujian_id' => $jadwal->id,
                'status_pendaftaran' => 'jadwal_dipilih',
            ]);

            // Increment jadwal terisi
            $jadwal->increment('terisi');
        });

        return [
            'success' => true,
            'message' => 'Jadwal berhasil dipilih',
            'jadwal' => $jadwal->fresh()->load(['sesi', 'ruang']),
        ];
    }
}
