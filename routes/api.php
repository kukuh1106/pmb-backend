<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\PendaftarController;
use App\Http\Controllers\Api\ProdiStafController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| PMB Pascasarjana - Backend API Routes
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::get('/prodi', [PublicController::class, 'getProdi']);
Route::post('/register', [PublicController::class, 'register']);
Route::post('/cek-kelulusan', [PublicController::class, 'cekKelulusan']);

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'loginPendaftar']);
    Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Pendaftar Routes (Role: pendaftar)
|--------------------------------------------------------------------------
*/
Route::prefix('pendaftar')
    ->middleware(['auth:sanctum', 'role:pendaftar'])
    ->group(function () {
        Route::get('/dashboard', [PendaftarController::class, 'dashboard']);
        Route::get('/biodata', [PendaftarController::class, 'getBiodata']);
        Route::put('/biodata', [PendaftarController::class, 'updateBiodata']);
        Route::get('/dokumen', [PendaftarController::class, 'getDokumen']);
        Route::post('/dokumen', [PendaftarController::class, 'uploadDokumen']);
        Route::post('/foto', [PendaftarController::class, 'uploadFoto']);
        Route::get('/jadwal', [PendaftarController::class, 'getJadwal']);
        Route::post('/pilih-jadwal', [PendaftarController::class, 'pilihJadwal']);
        Route::get('/kartu', [PendaftarController::class, 'getKartu']);
        Route::get('/hasil', [PendaftarController::class, 'getHasil']);
    });

/*
|--------------------------------------------------------------------------
| Staf Prodi Routes (Role: prodi)
|--------------------------------------------------------------------------
*/
Route::prefix('prodi')
    ->middleware(['auth:sanctum', 'role:prodi'])
    ->group(function () {
        Route::get('/dashboard', [ProdiStafController::class, 'dashboard']);
        Route::get('/pendaftar', [ProdiStafController::class, 'getPendaftar']);
        Route::get('/pendaftar/{id}', [ProdiStafController::class, 'getDetailPendaftar']);
        Route::put('/verifikasi/{id}', [ProdiStafController::class, 'verifikasiDokumen']);
        Route::get('/form-nilai', [ProdiStafController::class, 'downloadFormNilai']);
        Route::post('/upload-nilai', [ProdiStafController::class, 'uploadNilai']);
        Route::put('/pendaftar/{id}/nilai', [ProdiStafController::class, 'inputNilai']);
        Route::put('/pendaftar/{id}/status', [ProdiStafController::class, 'setStatus']);
        Route::post('/notifikasi/{id}', [ProdiStafController::class, 'kirimNotifikasi']);
    });

/*
|--------------------------------------------------------------------------
| Admin Routes (Role: admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // WhatsApp Status
        Route::get('/whatsapp/status', [AdminController::class, 'getWhatsappStatus']);
        
        // Prodi CRUD
        Route::get('/prodi', [AdminController::class, 'indexProdi']);
        Route::post('/prodi', [AdminController::class, 'storeProdi']);
        Route::get('/prodi/{id}', [AdminController::class, 'showProdi']);
        Route::put('/prodi/{id}', [AdminController::class, 'updateProdi']);
        Route::delete('/prodi/{id}', [AdminController::class, 'destroyProdi']);
        
        // Periode CRUD
        Route::get('/periode', [AdminController::class, 'indexPeriode']);
        Route::post('/periode', [AdminController::class, 'storePeriode']);
        Route::get('/periode/{id}', [AdminController::class, 'showPeriode']);
        Route::put('/periode/{id}', [AdminController::class, 'updatePeriode']);
        Route::delete('/periode/{id}', [AdminController::class, 'destroyPeriode']);
        
        // Sesi Ujian CRUD
        Route::get('/sesi', [AdminController::class, 'indexSesi']);
        Route::post('/sesi', [AdminController::class, 'storeSesi']);
        Route::get('/sesi/{id}', [AdminController::class, 'showSesi']);
        Route::put('/sesi/{id}', [AdminController::class, 'updateSesi']);
        Route::delete('/sesi/{id}', [AdminController::class, 'destroySesi']);
        
        // Ruang Ujian CRUD
        Route::get('/ruang', [AdminController::class, 'indexRuang']);
        Route::post('/ruang', [AdminController::class, 'storeRuang']);
        Route::get('/ruang/{id}', [AdminController::class, 'showRuang']);
        Route::put('/ruang/{id}', [AdminController::class, 'updateRuang']);
        Route::delete('/ruang/{id}', [AdminController::class, 'destroyRuang']);
        
        // Jadwal Ujian CRUD
        Route::get('/jadwal', [AdminController::class, 'indexJadwal']);
        Route::post('/jadwal', [AdminController::class, 'storeJadwal']);
        Route::get('/jadwal/{id}', [AdminController::class, 'showJadwal']);
        Route::put('/jadwal/{id}', [AdminController::class, 'updateJadwal']);
        Route::delete('/jadwal/{id}', [AdminController::class, 'destroyJadwal']);
        
        // Users CRUD
        Route::get('/users', [AdminController::class, 'indexUsers']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        
        // Pendaftar (Read Only)
        Route::get('/pendaftar', [AdminController::class, 'getPendaftar']);
        Route::get('/pendaftar/{id}', [AdminController::class, 'showPendaftar']);
    });
