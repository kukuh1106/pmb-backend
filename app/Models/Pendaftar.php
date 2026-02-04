<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Pendaftar extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pendaftar';

    protected $fillable = [
        'nomor_pendaftaran',
        'kode_akses',
        'nama_lengkap',
        'no_whatsapp',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'alamat',
        'pendidikan_terakhir',
        'asal_institusi',
        'foto_path',
        'prodi_id',
        'jadwal_ujian_id',
        'periode_id',
        'nilai_ujian',
        'status_kelulusan',
        'status_pendaftaran',
    ];

    protected $hidden = [
        'kode_akses',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'nilai_ujian' => 'decimal:2',
    ];

    /**
     * Get the prodi for this pendaftar
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Get the jadwal ujian for this pendaftar
     */
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Get the periode for this pendaftar
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id');
    }

    /**
     * Get all dokumen for this pendaftar
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class);
    }

    /**
     * Check if biodata is complete
     */
    public function isBiodataComplete(): bool
    {
        return !empty($this->tanggal_lahir)
            && !empty($this->tempat_lahir)
            && !empty($this->jenis_kelamin)
            && !empty($this->alamat)
            && !empty($this->pendidikan_terakhir)
            && !empty($this->asal_institusi);
    }

    /**
     * Scope by prodi
     */
    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    /**
     * Scope by periode
     */
    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    /**
     * Scope by status pendaftaran
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_pendaftaran', $status);
    }
}
