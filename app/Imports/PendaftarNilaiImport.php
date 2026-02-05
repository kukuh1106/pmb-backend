<?php

namespace App\Imports;

use App\Models\Pendaftar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PendaftarNilaiImport implements ToCollection, WithHeadingRow
{
    private int $prodiId;
    private array $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public function __construct(int $prodiId)
    {
        $this->prodiId = $prodiId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 karena heading row dan 0-indexed
            
            try {
                // Get nomor_pendaftaran from various possible header names
                $nomorPendaftaran = $this->getValue($row, ['nomor_pendaftaran', 'nomor pendaftaran', 'no_pendaftaran']);
                
                if (empty($nomorPendaftaran)) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNum,
                        'message' => 'Nomor pendaftaran kosong',
                    ];
                    continue;
                }

                $pendaftar = Pendaftar::where('nomor_pendaftaran', $nomorPendaftaran)
                    ->where('prodi_id', $this->prodiId)
                    ->first();

                if (!$pendaftar) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNum,
                        'nomor_pendaftaran' => $nomorPendaftaran,
                        'message' => 'Pendaftar tidak ditemukan atau bukan milik prodi Anda',
                    ];
                    continue;
                }

                $updateData = [];

                // Handle nilai_ujian
                $nilai = $this->getValue($row, ['nilai_ujian_0_100', 'nilai_ujian', 'nilai', 'nilai_ujian_0100']);
                if ($nilai !== null && $nilai !== '') {
                    $nilaiNum = floatval($nilai);
                    if ($nilaiNum < 0 || $nilaiNum > 100) {
                        $this->results['failed']++;
                        $this->results['errors'][] = [
                            'row' => $rowNum,
                            'nomor_pendaftaran' => $nomorPendaftaran,
                            'message' => "Nilai harus antara 0-100, diberikan: {$nilai}",
                        ];
                        continue;
                    }
                    $updateData['nilai_ujian'] = $nilaiNum;
                }

                // Handle status_kelulusan
                $status = $this->getValue($row, [
                    'status_kelulusan_lulustidaslk_lulusbelum_diproses', 
                    'status_kelulusan', 
                    'status',
                    'kelulusan'
                ]);
                if ($status !== null && $status !== '') {
                    $status = strtolower(trim($status));
                    // Normalize status variations
                    if (in_array($status, ['lulus', 'pass', 'passed', 'l'])) {
                        $updateData['status_kelulusan'] = 'lulus';
                        $updateData['status_pendaftaran'] = 'selesai';
                    } elseif (in_array($status, ['tidak_lulus', 'tidak lulus', 'fail', 'failed', 'tl', 'gagal'])) {
                        $updateData['status_kelulusan'] = 'tidak_lulus';
                        $updateData['status_pendaftaran'] = 'selesai';
                    } elseif (in_array($status, ['belum_diproses', 'belum diproses', 'pending', 'menunggu', 'bp'])) {
                        $updateData['status_kelulusan'] = 'belum_diproses';
                    } else {
                        $this->results['failed']++;
                        $this->results['errors'][] = [
                            'row' => $rowNum,
                            'nomor_pendaftaran' => $nomorPendaftaran,
                            'message' => "Status tidak valid: {$status}. Gunakan: lulus, tidak_lulus, atau belum_diproses",
                        ];
                        continue;
                    }
                }

                if (empty($updateData)) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNum,
                        'nomor_pendaftaran' => $nomorPendaftaran,
                        'message' => 'Tidak ada data nilai atau status yang diisi',
                    ];
                    continue;
                }

                $pendaftar->update($updateData);
                $this->results['success']++;

            } catch (\Exception $e) {
                Log::error('Import error at row ' . $rowNum, ['error' => $e->getMessage()]);
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row' => $rowNum,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Get value from row with multiple possible header names
     */
    private function getValue(Collection $row, array $keys)
    {
        foreach ($keys as $key) {
            // Try exact match
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
            // Try with underscores replaced
            $altKey = str_replace(' ', '_', strtolower($key));
            if (isset($row[$altKey]) && $row[$altKey] !== null && $row[$altKey] !== '') {
                return $row[$altKey];
            }
        }
        return null;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
