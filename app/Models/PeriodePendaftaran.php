<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodePendaftaran extends Model
{
    protected $table = 'periode_pendaftaran';

    protected $fillable = [
        'nama',
        'tanggal_buka',
        'tanggal_tutup',
        'is_active',
    ];

    protected $casts = [
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get all pendaftar in this periode
     */
    public function pendaftar(): HasMany
    {
        return $this->hasMany(Pendaftar::class, 'periode_id');
    }

    /**
     * Get all jadwal ujian in this periode
     */
    public function jadwalUjian(): HasMany
    {
        return $this->hasMany(JadwalUjian::class, 'periode_id');
    }

    /**
     * Scope untuk periode aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk periode yang sedang berjalan
     */
    public function scopeOngoing($query)
    {
        $today = now()->toDateString();
        return $query->where('tanggal_buka', '<=', $today)
                     ->where('tanggal_tutup', '>=', $today)
                     ->where('is_active', true);
    }
}
