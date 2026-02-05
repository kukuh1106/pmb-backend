<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Pendaftar;
use App\Models\PeriodePendaftaran;
use App\Models\Prodi;
use App\Models\RuangUjian;
use App\Models\SesiUjian;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Get admin dashboard with statistics
     */
    public function dashboard(): JsonResponse
    {
        $totalPendaftar = Pendaftar::count();
        $pendaftarByProdi = Prodi::withCount('pendaftar')
            ->get()
            ->map(fn($p) => [
                'prodi' => $p->nama,
                'jenjang' => $p->jenjang,
                'total' => $p->pendaftar_count,
            ]);

        $pendaftarByStatus = [
            'registrasi' => Pendaftar::where('status_pendaftaran', 'registrasi')->count(),
            'biodata_lengkap' => Pendaftar::where('status_pendaftaran', 'biodata_lengkap')->count(),
            'jadwal_dipilih' => Pendaftar::where('status_pendaftaran', 'jadwal_dipilih')->count(),
            'selesai' => Pendaftar::where('status_pendaftaran', 'selesai')->count(),
        ];

        $kelulusanByStatus = [
            'belum_diproses' => Pendaftar::where('status_kelulusan', 'belum_diproses')->count(),
            'lulus' => Pendaftar::where('status_kelulusan', 'lulus')->count(),
            'tidak_lulus' => Pendaftar::where('status_kelulusan', 'tidak_lulus')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'total_pendaftar' => $totalPendaftar,
                'total_prodi' => Prodi::count(),
                'total_users' => User::count(),
                'pendaftar_by_prodi' => $pendaftarByProdi,
                'pendaftar_by_status' => $pendaftarByStatus,
                'kelulusan_by_status' => $kelulusanByStatus,
                'periode_aktif' => PeriodePendaftaran::active()->first(),
            ],
        ]);
    }

    // ==================== PRODI CRUD ====================

    public function indexProdi(): JsonResponse
    {
        $prodi = Prodi::withCount('pendaftar')->orderBy('jenjang')->orderBy('nama')->get();
        return response()->json(['success' => true, 'data' => $prodi]);
    }

    public function storeProdi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:prodi,kode',
            'nama' => 'required|string|max:255',
            'jenjang' => 'required|in:S2,S3',
            'is_active' => 'boolean',
        ]);

        $prodi = Prodi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Prodi berhasil ditambahkan',
            'data' => $prodi,
        ], 201);
    }

    public function showProdi(int $id): JsonResponse
    {
        $prodi = Prodi::withCount('pendaftar')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $prodi]);
    }

    public function updateProdi(Request $request, int $id): JsonResponse
    {
        $prodi = Prodi::findOrFail($id);

        $validated = $request->validate([
            'kode' => ['sometimes', 'string', 'max:20', Rule::unique('prodi', 'kode')->ignore($id)],
            'nama' => 'sometimes|string|max:255',
            'jenjang' => 'sometimes|in:S2,S3',
            'is_active' => 'boolean',
        ]);

        $prodi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Prodi berhasil diupdate',
            'data' => $prodi,
        ]);
    }

    public function destroyProdi(int $id): JsonResponse
    {
        $prodi = Prodi::findOrFail($id);
        
        if ($prodi->pendaftar()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus prodi yang memiliki pendaftar',
            ], 422);
        }

        $prodi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prodi berhasil dihapus',
        ]);
    }

    // ==================== PERIODE CRUD ====================

    public function indexPeriode(): JsonResponse
    {
        $periode = PeriodePendaftaran::withCount('pendaftar')->orderBy('tanggal_buka', 'desc')->get();
        return response()->json(['success' => true, 'data' => $periode]);
    }

    public function storePeriode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_buka' => 'required|date',
            'tanggal_tutup' => 'required|date|after_or_equal:tanggal_buka',
            'is_active' => 'boolean',
        ]);

        $periode = PeriodePendaftaran::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil ditambahkan',
            'data' => $periode,
        ], 201);
    }

    public function showPeriode(int $id): JsonResponse
    {
        $periode = PeriodePendaftaran::withCount('pendaftar')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $periode]);
    }

    public function updatePeriode(Request $request, int $id): JsonResponse
    {
        $periode = PeriodePendaftaran::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'tanggal_buka' => 'sometimes|date',
            'tanggal_tutup' => 'sometimes|date|after_or_equal:tanggal_buka',
            'is_active' => 'boolean',
        ]);

        $periode->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil diupdate',
            'data' => $periode,
        ]);
    }

    public function destroyPeriode(int $id): JsonResponse
    {
        $periode = PeriodePendaftaran::findOrFail($id);
        
        if ($periode->pendaftar()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus periode yang memiliki pendaftar',
            ], 422);
        }

        $periode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil dihapus',
        ]);
    }

    // ==================== SESI UJIAN CRUD ====================

    public function indexSesi(): JsonResponse
    {
        $sesi = SesiUjian::orderBy('jam_mulai')->get();
        return response()->json(['success' => true, 'data' => $sesi]);
    }

    public function storeSesi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
        ]);

        $sesi = SesiUjian::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil ditambahkan',
            'data' => $sesi,
        ], 201);
    }

    public function showSesi(int $id): JsonResponse
    {
        $sesi = SesiUjian::findOrFail($id);
        return response()->json(['success' => true, 'data' => $sesi]);
    }

    public function updateSesi(Request $request, int $id): JsonResponse
    {
        $sesi = SesiUjian::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:100',
            'jam_mulai' => 'sometimes|date_format:H:i',
            'jam_selesai' => 'sometimes|date_format:H:i|after:jam_mulai',
            'is_active' => 'boolean',
        ]);

        $sesi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil diupdate',
            'data' => $sesi,
        ]);
    }

    public function destroySesi(int $id): JsonResponse
    {
        $sesi = SesiUjian::findOrFail($id);
        $sesi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil dihapus',
        ]);
    }

    // ==================== RUANG UJIAN CRUD ====================

    public function indexRuang(): JsonResponse
    {
        $ruang = RuangUjian::orderBy('kode')->get();
        return response()->json(['success' => true, 'data' => $ruang]);
    }

    public function storeRuang(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:ruang_ujian,kode',
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $ruang = RuangUjian::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruang ujian berhasil ditambahkan',
            'data' => $ruang,
        ], 201);
    }

    public function showRuang(int $id): JsonResponse
    {
        $ruang = RuangUjian::findOrFail($id);
        return response()->json(['success' => true, 'data' => $ruang]);
    }

    public function updateRuang(Request $request, int $id): JsonResponse
    {
        $ruang = RuangUjian::findOrFail($id);

        $validated = $request->validate([
            'kode' => ['sometimes', 'string', 'max:20', Rule::unique('ruang_ujian', 'kode')->ignore($id)],
            'nama' => 'sometimes|string|max:255',
            'kapasitas' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $ruang->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruang ujian berhasil diupdate',
            'data' => $ruang,
        ]);
    }

    public function destroyRuang(int $id): JsonResponse
    {
        $ruang = RuangUjian::findOrFail($id);
        $ruang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruang ujian berhasil dihapus',
        ]);
    }

    // ==================== JADWAL UJIAN CRUD ====================

    public function indexJadwal(Request $request): JsonResponse
    {
        $query = JadwalUjian::with(['periode', 'sesi', 'ruang'])
            ->withCount('pendaftar');

        if ($request->has('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        $jadwal = $query->orderBy('tanggal')->orderBy('sesi_id')->get();

        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    public function storeJadwal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode_pendaftaran,id',
            'tanggal' => 'required|date',
            'sesi_id' => 'required|exists:sesi_ujian,id',
            'ruang_id' => 'required|exists:ruang_ujian,id',
            'kuota' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Check unique constraint
        $exists = JadwalUjian::where('tanggal', $validated['tanggal'])
            ->where('sesi_id', $validated['sesi_id'])
            ->where('ruang_id', $validated['ruang_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal dengan kombinasi tanggal, sesi, dan ruang yang sama sudah ada',
            ], 422);
        }

        $jadwal = JadwalUjian::create($validated);
        $jadwal->load(['periode', 'sesi', 'ruang']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal ujian berhasil ditambahkan',
            'data' => $jadwal,
        ], 201);
    }

    public function showJadwal(int $id): JsonResponse
    {
        $jadwal = JadwalUjian::with(['periode', 'sesi', 'ruang'])
            ->withCount('pendaftar')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    public function updateJadwal(Request $request, int $id): JsonResponse
    {
        $jadwal = JadwalUjian::findOrFail($id);

        $validated = $request->validate([
            'periode_id' => 'sometimes|exists:periode_pendaftaran,id',
            'tanggal' => 'sometimes|date',
            'sesi_id' => 'sometimes|exists:sesi_ujian,id',
            'ruang_id' => 'sometimes|exists:ruang_ujian,id',
            'kuota' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $jadwal->update($validated);
        $jadwal->load(['periode', 'sesi', 'ruang']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal ujian berhasil diupdate',
            'data' => $jadwal,
        ]);
    }

    public function destroyJadwal(int $id): JsonResponse
    {
        $jadwal = JadwalUjian::findOrFail($id);
        
        if ($jadwal->terisi > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus jadwal yang sudah memiliki pendaftar',
            ], 422);
        }

        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal ujian berhasil dihapus',
        ]);
    }

    // ==================== USERS CRUD ====================

    public function indexUsers(Request $request): JsonResponse
    {
        $query = User::with('prodi');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,prodi',
            'prodi_id' => 'required_if:role,prodi|nullable|exists:prodi,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->load('prodi');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user,
        ], 201);
    }

    public function showUser(int $id): JsonResponse
    {
        $user = User::with('prodi')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id)],
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:admin,prodi',
            'prodi_id' => 'nullable|exists:prodi,id',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        $user->load('prodi');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data' => $user,
        ]);
    }

    public function destroyUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deletion
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }

    // ==================== PENDAFTAR (Read Only) ====================

    public function getPendaftar(Request $request): JsonResponse
    {
        $query = Pendaftar::with(['prodi', 'jadwalUjian.sesi', 'jadwalUjian.ruang', 'periode']);

        if ($request->has('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->has('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        if ($request->has('status_pendaftaran')) {
            $query->where('status_pendaftaran', $request->status_pendaftaran);
        }

        if ($request->has('status_kelulusan')) {
            $query->where('status_kelulusan', $request->status_kelulusan);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$search}%");
            });
        }

        $pendaftar = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $pendaftar->items(),
            'meta' => [
                'current_page' => $pendaftar->currentPage(),
                'last_page' => $pendaftar->lastPage(),
                'per_page' => $pendaftar->perPage(),
                'total' => $pendaftar->total(),
            ],
        ]);
    }

    public function showPendaftar(int $id): JsonResponse
    {
        $pendaftar = Pendaftar::with([
            'prodi',
            'jadwalUjian.sesi',
            'jadwalUjian.ruang',
            'periode',
            'dokumen'
        ])->findOrFail($id);

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
                        'file_path' => $doc->file_path, // Needed for download link construction in frontend if necessary
                        'status_verifikasi' => $doc->status_verifikasi,
                        'catatan' => $doc->catatan,
                        'created_at' => $doc->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ]);
    }
}
