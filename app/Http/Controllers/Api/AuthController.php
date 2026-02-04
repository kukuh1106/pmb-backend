<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginAdminRequest;
use App\Http\Requests\LoginPendaftarRequest;
use App\Models\Pendaftar;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login pendaftar with nomor_pendaftaran + kode_akses
     */
    public function loginPendaftar(LoginPendaftarRequest $request): JsonResponse
    {
        $pendaftar = Pendaftar::where('nomor_pendaftaran', $request->nomor_pendaftaran)->first();

        if (!$pendaftar || !Hash::check($request->kode_akses, $pendaftar->kode_akses)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor pendaftaran atau kode akses salah',
            ], 401);
        }

        // Create token
        $token = $pendaftar->createToken('pendaftar-token', ['role:pendaftar'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $pendaftar->id,
                    'nomor_pendaftaran' => $pendaftar->nomor_pendaftaran,
                    'nama_lengkap' => $pendaftar->nama_lengkap,
                    'role' => 'pendaftar',
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Login admin/staf prodi with username + password
     */
    public function loginAdmin(LoginAdminRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif',
            ], 403);
        }

        // Create token with role ability
        $token = $user->createToken('admin-token', ["role:{$user->role}"])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'prodi_id' => $user->prodi_id,
                    'prodi' => $user->prodi ? [
                        'id' => $user->prodi->id,
                        'nama' => $user->prodi->nama,
                    ] : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is Pendaftar or User (Admin/Prodi)
        if ($user instanceof Pendaftar) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'nomor_pendaftaran' => $user->nomor_pendaftaran,
                    'nama_lengkap' => $user->nama_lengkap,
                    'no_whatsapp' => $user->no_whatsapp,
                    'role' => 'pendaftar',
                    'status_pendaftaran' => $user->status_pendaftaran,
                    'prodi' => $user->prodi ? [
                        'id' => $user->prodi->id,
                        'nama' => $user->prodi->nama,
                        'jenjang' => $user->prodi->jenjang,
                    ] : null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'prodi' => $user->prodi ? [
                    'id' => $user->prodi->id,
                    'nama' => $user->prodi->nama,
                ] : null,
            ],
        ]);
    }
}
