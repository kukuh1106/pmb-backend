<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InputNilaiRequest;
use App\Http\Requests\SetStatusRequest;
use App\Http\Requests\VerifikasiDokumenRequest;
use App\Models\Dokumen;
use App\Models\Pendaftar;
use App\Services\FileUploadService;
use App\Services\KelulusanService;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdiStafController extends Controller
{
    public function __construct(
        private KelulusanService $kelulusanService,
        private NotifikasiService $notifikasiService,
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Get list pendaftar for this prodi
     */
    public function getPendaftar(Request $request): JsonResponse
    {
        $user = $request->user();
        $prodiId = $user->prodi_id;

        if (!$prodiId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke prodi manapun',
            ], 403);
        }

        $query = Pendaftar::with(['prodi', 'jadwalUjian.sesi', 'jadwalUjian.ruang'])
            ->where('prodi_id', $prodiId);

        // Filter by status_pendaftaran
        if ($request->has('status_pendaftaran')) {
            $query->where('status_pendaftaran', $request->status_pendaftaran);
        }

        // Filter by status_kelulusan
        if ($request->has('status_kelulusan')) {
            $query->where('status_kelulusan', $request->status_kelulusan);
        }

        // Search by nama or nomor
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$search}%");
            });
        }

        $pendaftar = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Get statistics
        $statistics = $this->kelulusanService->getProdiStatistics($prodiId);

        return response()->json([
            'success' => true,
            'data' => $pendaftar->items(),
            'meta' => [
                'current_page' => $pendaftar->currentPage(),
                'last_page' => $pendaftar->lastPage(),
                'per_page' => $pendaftar->perPage(),
                'total' => $pendaftar->total(),
            ],
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get detail pendaftar
     */
    public function getDetailPendaftar(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $pendaftar = Pendaftar::with([
            'prodi',
            'jadwalUjian.sesi',
            'jadwalUjian.ruang',
            'periode',
            'dokumen'
        ])->findOrFail($id);

        // Check if pendaftar belongs to user's prodi
        if ($pendaftar->prodi_id !== $user->prodi_id) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar bukan milik prodi Anda',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pendaftar' => [
                    'id' => $pendaftar->id,
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
                    'nilai_ujian' => $pendaftar->nilai_ujian,
                    'status_pendaftaran' => $pendaftar->status_pendaftaran,
                    'status_kelulusan' => $pendaftar->status_kelulusan,
                    'created_at' => $pendaftar->created_at->format('Y-m-d H:i:s'),
                ],
                'prodi' => $pendaftar->prodi ? [
                    'nama' => $pendaftar->prodi->nama,
                    'jenjang' => $pendaftar->prodi->jenjang,
                ] : null,
                'jadwal_ujian' => $pendaftar->jadwalUjian ? [
                    'tanggal' => $pendaftar->jadwalUjian->tanggal->format('Y-m-d'),
                    'sesi' => $pendaftar->jadwalUjian->sesi->nama ?? null,
                    'ruang' => $pendaftar->jadwalUjian->ruang->nama ?? null,
                ] : null,
                'periode' => $pendaftar->periode->nama ?? null,
                'dokumen' => $pendaftar->dokumen->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'jenis_dokumen' => $doc->jenis_dokumen,
                        'file_name' => $doc->file_name,
                        'file_size' => $doc->file_size,
                        'status_verifikasi' => $doc->status_verifikasi,
                        'catatan' => $doc->catatan,
                        'created_at' => $doc->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Verifikasi dokumen
     */
    public function verifikasiDokumen(VerifikasiDokumenRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pendaftar = Pendaftar::findOrFail($id);

        // Check if pendaftar belongs to user's prodi
        if ($pendaftar->prodi_id !== $user->prodi_id) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar bukan milik prodi Anda',
            ], 403);
        }

        $dokumen = Dokumen::where('id', $request->dokumen_id)
            ->where('pendaftar_id', $pendaftar->id)
            ->firstOrFail();

        $updated = $this->kelulusanService->verifikasiDokumen(
            $dokumen,
            $request->status,
            $request->catatan
        );

        // Send notification if document is invalid
        if ($request->status === 'tidak_valid') {
            $this->notifikasiService->sendDokumenTidakValid(
                $pendaftar->no_whatsapp,
                $pendaftar->nama_lengkap,
                $dokumen->jenis_dokumen,
                $request->catatan
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diverifikasi',
            'data' => [
                'id' => $updated->id,
                'jenis_dokumen' => $updated->jenis_dokumen,
                'status_verifikasi' => $updated->status_verifikasi,
                'catatan' => $updated->catatan,
            ],
        ]);
    }

    /**
     * Download template form nilai (Excel/CSV)
     */
    public function downloadFormNilai(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $pendaftar = Pendaftar::where('prodi_id', $user->prodi_id)
            ->whereNotNull('jadwal_ujian_id')
            ->select('nomor_pendaftaran', 'nama_lengkap', 'nilai_ujian')
            ->get();

        // Return as JSON for now, can be extended to generate Excel file
        return response()->json([
            'success' => true,
            'message' => 'Template form nilai',
            'data' => $pendaftar->map(function ($p) {
                return [
                    'nomor_pendaftaran' => $p->nomor_pendaftaran,
                    'nama_lengkap' => $p->nama_lengkap,
                    'nilai' => $p->nilai_ujian ?? '',
                ];
            }),
        ]);
    }

    /**
     * Upload batch nilai
     */
    public function uploadNilai(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.nomor_pendaftaran' => 'required|string',
            'data.*.nilai' => 'required|numeric|min:0|max:100',
        ]);

        $user = $request->user();

        $result = $this->kelulusanService->batchInputNilai(
            $request->data,
            $user->prodi_id
        );

        return response()->json([
            'success' => true,
            'message' => "Berhasil update {$result['success']} nilai, gagal {$result['failed']}",
            'data' => $result,
        ]);
    }

    /**
     * Input nilai per peserta
     */
    public function inputNilai(InputNilaiRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pendaftar = Pendaftar::findOrFail($id);

        // Check if pendaftar belongs to user's prodi
        if ($pendaftar->prodi_id !== $user->prodi_id) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar bukan milik prodi Anda',
            ], 403);
        }

        $updated = $this->kelulusanService->inputNilai($pendaftar, $request->nilai_ujian);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan',
            'data' => [
                'nomor_pendaftaran' => $updated->nomor_pendaftaran,
                'nama_lengkap' => $updated->nama_lengkap,
                'nilai_ujian' => $updated->nilai_ujian,
            ],
        ]);
    }

    /**
     * Set status kelulusan
     */
    public function setStatus(SetStatusRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pendaftar = Pendaftar::findOrFail($id);

        // Check if pendaftar belongs to user's prodi
        if ($pendaftar->prodi_id !== $user->prodi_id) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar bukan milik prodi Anda',
            ], 403);
        }

        $updated = $this->kelulusanService->setStatusKelulusan($pendaftar, $request->status_kelulusan);

        return response()->json([
            'success' => true,
            'message' => 'Status kelulusan berhasil diupdate',
            'data' => [
                'nomor_pendaftaran' => $updated->nomor_pendaftaran,
                'nama_lengkap' => $updated->nama_lengkap,
                'status_kelulusan' => $updated->status_kelulusan,
            ],
        ]);
    }

    /**
     * Kirim notifikasi WA ke pendaftar
     */
    public function kirimNotifikasi(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:hasil,custom',
            'message' => 'required_if:type,custom|nullable|string|max:1000',
            'subject' => 'required_if:type,custom|nullable|string|max:100',
        ]);

        $user = $request->user();
        $pendaftar = Pendaftar::findOrFail($id);

        // Check if pendaftar belongs to user's prodi
        if ($pendaftar->prodi_id !== $user->prodi_id) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftar bukan milik prodi Anda',
            ], 403);
        }

        $sent = false;

        if ($request->type === 'hasil') {
            $sent = $this->notifikasiService->sendHasilUjian(
                $pendaftar->no_whatsapp,
                $pendaftar->nama_lengkap,
                $pendaftar->status_kelulusan,
                $pendaftar->nilai_ujian
            );
        } else {
            $sent = $this->notifikasiService->sendCustom(
                $pendaftar->no_whatsapp,
                $pendaftar->nama_lengkap,
                $request->subject,
                $request->message
            );
        }

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim notifikasi',
        ], 500);
    }
}
