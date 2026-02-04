<?php

namespace App\Services;

use App\Models\Pendaftar;
use App\Models\PeriodePendaftaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PendaftaranService
{
    /**
     * Register new pendaftar
     */
    public function register(array $data): array
    {
        $periode = PeriodePendaftaran::active()->first();

        if (!$periode) {
            throw new \Exception('Tidak ada periode pendaftaran yang aktif');
        }

        $nomorPendaftaran = $this->generateNomorPendaftaran($periode);
        $kodeAkses = $this->generateKodeAkses();

        $pendaftar = Pendaftar::create([
            'nomor_pendaftaran' => $nomorPendaftaran,
            'kode_akses' => Hash::make($kodeAkses),
            'nama_lengkap' => $data['nama_lengkap'],
            'no_whatsapp' => $data['no_whatsapp'],
            'prodi_id' => $data['prodi_id'],
            'periode_id' => $periode->id,
            'status_pendaftaran' => 'registrasi',
        ]);

        return [
            'pendaftar' => $pendaftar,
            'kode_akses' => $kodeAkses, // Plain text untuk dikirim via WA
        ];
    }

    /**
     * Generate nomor pendaftaran
     * Format: PMB{YEAR}{SEQUENCE} e.g., PMB202600001
     */
    private function generateNomorPendaftaran(PeriodePendaftaran $periode): string
    {
        $year = date('Y');
        $count = Pendaftar::where('periode_id', $periode->id)->count() + 1;
        return sprintf('PMB%s%05d', $year, $count);
    }

    /**
     * Generate random kode akses (6 characters alphanumeric)
     */
    private function generateKodeAkses(): string
    {
        return strtoupper(Str::random(6));
    }

    /**
     * Update biodata pendaftar
     */
    public function updateBiodata(Pendaftar $pendaftar, array $data): Pendaftar
    {
        $pendaftar->update($data);

        // Update status if biodata is complete
        if ($pendaftar->isBiodataComplete() && $pendaftar->status_pendaftaran === 'registrasi') {
            $pendaftar->update(['status_pendaftaran' => 'biodata_lengkap']);
        }

        return $pendaftar->fresh();
    }

    /**
     * Cek kelulusan by nomor pendaftaran dan tanggal lahir
     */
    public function cekKelulusan(string $nomorPendaftaran, string $tanggalLahir): ?array
    {
        $pendaftar = Pendaftar::where('nomor_pendaftaran', $nomorPendaftaran)
            ->whereDate('tanggal_lahir', $tanggalLahir)
            ->first();

        if (!$pendaftar) {
            return null;
        }

        return [
            'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
            'nama_lengkap' => $pendaftar->nama_lengkap,
            'prodi' => $pendaftar->prodi->nama ?? null,
            'jenjang' => $pendaftar->prodi->jenjang ?? null,
            'status_kelulusan' => $pendaftar->status_kelulusan,
            'nilai_ujian' => $pendaftar->nilai_ujian,
        ];
    }
}
