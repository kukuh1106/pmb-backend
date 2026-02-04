<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CekKelulusanRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Prodi;
use App\Services\NotifikasiService;
use App\Services\PendaftaranService;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function __construct(
        private PendaftaranService $pendaftaranService,
        private NotifikasiService $notifikasiService
    ) {}

    /**
     * Get list of active prodi for registration form
     */
    public function getProdi(): JsonResponse
    {
        $prodi = Prodi::active()
            ->select('id', 'kode', 'nama', 'jenjang')
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $prodi,
        ]);
    }

    /**
     * Register new pendaftar (public - no auth)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->pendaftaranService->register($request->validated());

            // Send WhatsApp notification with credentials
            $this->notifikasiService->sendKredensial(
                $result['pendaftar']->no_whatsapp,
                $result['pendaftar']->nama_lengkap,
                $result['pendaftar']->nomor_pendaftaran,
                $result['kode_akses']
            );

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan cek WhatsApp Anda untuk mendapatkan nomor pendaftaran dan kode akses.',
                'data' => [
                    'nomor_pendaftaran' => $result['pendaftar']->nomor_pendaftaran,
                    'nama_lengkap' => $result['pendaftar']->nama_lengkap,
                    'prodi' => $result['pendaftar']->prodi->nama ?? null,
                    // NOTE: kode_akses hanya ditampilkan sekali saat registrasi dan dikirim via WA
                    'kode_akses' => $result['kode_akses'], // For display only, will be sent via WA
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check graduation status (public - no auth)
     */
    public function cekKelulusan(CekKelulusanRequest $request): JsonResponse
    {
        $result = $this->pendaftaranService->cekKelulusan(
            $request->nomor_pendaftaran,
            $request->tanggal_lahir
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan. Pastikan nomor pendaftaran dan tanggal lahir sudah benar.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => $result,
        ]);
    }
}
