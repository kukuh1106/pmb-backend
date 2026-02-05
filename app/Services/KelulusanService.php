<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Pendaftar;
use Illuminate\Support\Facades\DB;

class KelulusanService
{
    /**
     * Verifikasi dokumen
     */
    public function verifikasiDokumen(Dokumen $dokumen, string $status, ?string $catatan = null): Dokumen
    {
        $dokumen->update([
            'status_verifikasi' => $status,
            'catatan' => $catatan,
        ]);

        return $dokumen->fresh();
    }

    /**
     * Input nilai untuk pendaftar
     */
    public function inputNilai(Pendaftar $pendaftar, float $nilai): Pendaftar
    {
        $pendaftar->update([
            'nilai_ujian' => $nilai,
        ]);

        return $pendaftar->fresh();
    }

    /**
     * Set status kelulusan
     */
    public function setStatusKelulusan(Pendaftar $pendaftar, string $status): Pendaftar
    {
        if (!in_array($status, ['lulus', 'tidak_lulus', 'belum_diproses'])) {
            throw new \InvalidArgumentException('Status kelulusan tidak valid');
        }

        $pendaftar->update([
            'status_kelulusan' => $status,
            'status_pendaftaran' => 'selesai',
        ]);

        return $pendaftar->fresh();
    }

    /**
     * Batch input nilai dan/atau status kelulusan from array
     * Format: [['nomor_pendaftaran' => 'PMB2026xxxx', 'nilai' => 85.5, 'status_kelulusan' => 'lulus'], ...]
     */
    public function batchInputNilai(array $data, int $prodiId): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                $pendaftar = Pendaftar::where('nomor_pendaftaran', $item['nomor_pendaftaran'])
                    ->where('prodi_id', $prodiId)
                    ->first();

                if (!$pendaftar) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'nomor_pendaftaran' => $item['nomor_pendaftaran'],
                        'message' => 'Pendaftar tidak ditemukan atau bukan milik prodi Anda',
                    ];
                    continue;
                }

                $updateData = [];

                // Handle nilai
                if (isset($item['nilai']) && $item['nilai'] !== null && $item['nilai'] !== '') {
                    $updateData['nilai_ujian'] = $item['nilai'];
                }

                // Handle status_kelulusan
                if (isset($item['status_kelulusan']) && $item['status_kelulusan'] !== null && $item['status_kelulusan'] !== '') {
                    if (in_array($item['status_kelulusan'], ['lulus', 'tidak_lulus', 'belum_diproses'])) {
                        $updateData['status_kelulusan'] = $item['status_kelulusan'];
                        if ($item['status_kelulusan'] !== 'belum_diproses') {
                            $updateData['status_pendaftaran'] = 'selesai';
                        }
                    }
                }

                if (!empty($updateData)) {
                    $pendaftar->update($updateData);
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'nomor_pendaftaran' => $item['nomor_pendaftaran'],
                        'message' => 'Tidak ada data nilai atau status yang diupdate',
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Get statistics for prodi
     */
    public function getProdiStatistics(int $prodiId): array
    {
        $query = Pendaftar::where('prodi_id', $prodiId);

        return [
            'total' => (clone $query)->count(),
            'registrasi' => (clone $query)->where('status_pendaftaran', 'registrasi')->count(),
            'biodata_lengkap' => (clone $query)->where('status_pendaftaran', 'biodata_lengkap')->count(),
            'jadwal_dipilih' => (clone $query)->where('status_pendaftaran', 'jadwal_dipilih')->count(),
            'selesai' => (clone $query)->where('status_pendaftaran', 'selesai')->count(),
            'belum_diproses' => (clone $query)->where('status_kelulusan', 'belum_diproses')->count(),
            'lulus' => (clone $query)->where('status_kelulusan', 'lulus')->count(),
            'tidak_lulus' => (clone $query)->where('status_kelulusan', 'tidak_lulus')->count(),
        ];
    }
}
