<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PilihJadwalRequest;
use App\Http\Requests\UpdateBiodataRequest;
use App\Http\Requests\UploadDokumenRequest;
use App\Models\Pendaftar;
use App\Services\FileUploadService;
use App\Services\JadwalService;
use App\Services\PendaftaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendaftarController extends Controller
{
    public function __construct(
        private PendaftaranService $pendaftaranService,
        private JadwalService $jadwalService,
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Get pendaftar dashboard (status & hasil)
     */
    public function dashboard(Request $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();
        $pendaftar->load(['prodi', 'jadwalUjian.sesi', 'jadwalUjian.ruang', 'dokumen']);

        // Calculate completion status
        $dokumenCount = $pendaftar->dokumen->count();
        $dokumenValid = $pendaftar->dokumen->where('status_verifikasi', 'valid')->count();
        $dokumenPending = $pendaftar->dokumen->where('status_verifikasi', 'pending')->count();
        $dokumenInvalid = $pendaftar->dokumen->where('status_verifikasi', 'tidak_valid')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pendaftar' => [
                    'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
                    'nama_lengkap' => $pendaftar->nama_lengkap,
                    'status_pendaftaran' => $pendaftar->status_pendaftaran,
                    'status_kelulusan' => $pendaftar->status_kelulusan,
                    'nilai_ujian' => $pendaftar->nilai_ujian,
                ],
                'prodi' => $pendaftar->prodi ? [
                    'nama' => $pendaftar->prodi->nama,
                    'jenjang' => $pendaftar->prodi->jenjang,
                ] : null,
                'jadwal_ujian' => $pendaftar->jadwalUjian ? [
                    'tanggal' => $pendaftar->jadwalUjian->tanggal->format('Y-m-d'),
                    'sesi' => $pendaftar->jadwalUjian->sesi->nama ?? null,
                    'jam' => $pendaftar->jadwalUjian->sesi ? 
                        "{$pendaftar->jadwalUjian->sesi->jam_mulai} - {$pendaftar->jadwalUjian->sesi->jam_selesai}" : null,
                    'ruang' => $pendaftar->jadwalUjian->ruang->nama ?? null,
                ] : null,
                'biodata_lengkap' => $pendaftar->isBiodataComplete(),
                'dokumen' => [
                    'total' => $dokumenCount,
                    'valid' => $dokumenValid,
                    'pending' => $dokumenPending,
                    'tidak_valid' => $dokumenInvalid,
                ],
            ],
        ]);
    }

    /**
     * Get biodata pendaftar
     */
    public function getBiodata(Request $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();
        $pendaftar->load('prodi');

        return response()->json([
            'success' => true,
            'data' => [
                'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
                'nama_lengkap' => $pendaftar->nama_lengkap,
                'no_whatsapp' => $pendaftar->no_whatsapp,
                'tanggal_lahir' => $pendaftar->tanggal_lahir?->format('Y-m-d'),
                'tempat_lahir' => $pendaftar->tempat_lahir,
                'jenis_kelamin' => $pendaftar->jenis_kelamin,
                'alamat' => $pendaftar->alamat,
                'pendidikan_terakhir' => $pendaftar->pendidikan_terakhir,
                'asal_institusi' => $pendaftar->asal_institusi,
                'foto_path' => $pendaftar->foto_path,
                'prodi' => $pendaftar->prodi ? [
                    'id' => $pendaftar->prodi->id,
                    'nama' => $pendaftar->prodi->nama,
                    'jenjang' => $pendaftar->prodi->jenjang,
                ] : null,
                'status_pendaftaran' => $pendaftar->status_pendaftaran,
                'is_complete' => $pendaftar->isBiodataComplete(),
            ],
        ]);
    }

    /**
     * Update biodata pendaftar
     */
    public function updateBiodata(UpdateBiodataRequest $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();

        $updated = $this->pendaftaranService->updateBiodata($pendaftar, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Biodata berhasil diupdate',
            'data' => [
                'nama_lengkap' => $updated->nama_lengkap,
                'status_pendaftaran' => $updated->status_pendaftaran,
                'is_complete' => $updated->isBiodataComplete(),
            ],
        ]);
    }

    /**
     * Upload dokumen
     */
    public function uploadDokumen(UploadDokumenRequest $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();

        try {
            $dokumen = $this->fileUploadService->uploadDokumen(
                $pendaftar,
                $request->file('file'),
                $request->jenis_dokumen
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => [
                    'id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'file_name' => $dokumen->file_name,
                    'file_size' => $dokumen->file_size,
                    'status_verifikasi' => $dokumen->status_verifikasi,
                ],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload foto
     */
    public function uploadFoto(Request $request): JsonResponse
    {
        $request->validate([
            'foto' => 'required|file|mimes:jpg,jpeg|max:2048',
        ]);

        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();

        try {
            $path = $this->fileUploadService->uploadFoto($pendaftar, $request->file('foto'));

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload',
                'data' => [
                    'foto_path' => $path,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available jadwal ujian
     */
    public function getJadwal(): JsonResponse
    {
        $jadwal = $this->jadwalService->getAvailableJadwal();

        $data = $jadwal->map(function ($item) {
            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal->format('Y-m-d'),
                'tanggal_formatted' => $item->tanggal->translatedFormat('l, d F Y'),
                'sesi' => [
                    'nama' => $item->sesi->nama,
                    'jam_mulai' => $item->sesi->jam_mulai,
                    'jam_selesai' => $item->sesi->jam_selesai,
                ],
                'ruang' => [
                    'kode' => $item->ruang->kode,
                    'nama' => $item->ruang->nama,
                ],
                'kuota' => $item->kuota,
                'terisi' => $item->terisi,
                'sisa_kuota' => $item->sisa_kuota,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Pilih jadwal ujian
     */
    public function pilihJadwal(PilihJadwalRequest $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();

        $result = $this->jadwalService->pilihJadwal($pendaftar, $request->jadwal_ujian_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'jadwal' => [
                    'tanggal' => $result['jadwal']->tanggal->format('Y-m-d'),
                    'sesi' => $result['jadwal']->sesi->nama ?? null,
                    'ruang' => $result['jadwal']->ruang->nama ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get kartu pendaftaran
     */
    public function getKartu(Request $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();
        $pendaftar->load(['prodi', 'jadwalUjian.sesi', 'jadwalUjian.ruang', 'periode']);

        // Check if pendaftar has selected jadwal
        if (!$pendaftar->jadwal_ujian_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memilih jadwal ujian',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kartu' => [
                    'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
                    'nama_lengkap' => $pendaftar->nama_lengkap,
                    'tempat_tanggal_lahir' => $pendaftar->tempat_lahir && $pendaftar->tanggal_lahir
                        ? "{$pendaftar->tempat_lahir}, {$pendaftar->tanggal_lahir->format('d F Y')}"
                        : null,
                    'jenis_kelamin' => $pendaftar->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    'prodi' => $pendaftar->prodi ? [
                        'nama' => $pendaftar->prodi->nama,
                        'jenjang' => $pendaftar->prodi->jenjang,
                    ] : null,
                    'periode' => $pendaftar->periode->nama ?? null,
                    'jadwal_ujian' => [
                        'tanggal' => $pendaftar->jadwalUjian->tanggal->translatedFormat('l, d F Y'),
                        'sesi' => $pendaftar->jadwalUjian->sesi->nama ?? null,
                        'jam' => $pendaftar->jadwalUjian->sesi
                            ? "{$pendaftar->jadwalUjian->sesi->jam_mulai} - {$pendaftar->jadwalUjian->sesi->jam_selesai}"
                            : null,
                        'ruang' => $pendaftar->jadwalUjian->ruang->nama ?? null,
                    ],
                    'foto' => $pendaftar->foto_path,
                ],
            ],
        ]);
    }

    /**
     * Get hasil ujian
     */
    public function getHasil(Request $request): JsonResponse
    {
        /** @var Pendaftar $pendaftar */
        $pendaftar = $request->user();
        $pendaftar->load('prodi');

        return response()->json([
            'success' => true,
            'data' => [
                'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
                'nama_lengkap' => $pendaftar->nama_lengkap,
                'prodi' => $pendaftar->prodi ? [
                    'nama' => $pendaftar->prodi->nama,
                    'jenjang' => $pendaftar->prodi->jenjang,
                ] : null,
                'nilai_ujian' => $pendaftar->nilai_ujian,
                'status_kelulusan' => $pendaftar->status_kelulusan,
                'status_label' => match ($pendaftar->status_kelulusan) {
                    'lulus' => 'LULUS',
                    'tidak_lulus' => 'TIDAK LULUS',
                    default => 'BELUM DIPROSES',
                },
            ],
        ]);
    }
}
