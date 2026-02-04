<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    protected $table = 'jadwal_ujian';

    protected $fillable = [
        'periode_id',
        'tanggal',
        'sesi_id',
        'ruang_id',
        'kuota',
        'terisi',
        'is_active',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the periode for this jadwal
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id');
    }

    /**
     * Get the sesi for this jadwal
     */
    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_id');
    }

    /**
     * Get the ruang for this jadwal
     */
    public function ruang(): BelongsTo
    {
        return $this->belongsTo(RuangUjian::class, 'ruang_id');
    }

    /**
     * Get all pendaftar for this jadwal
     */
    public function pendaftar(): HasMany
    {
        return $this->hasMany(Pendaftar::class, 'jadwal_ujian_id');
    }

    /**
     * Scope untuk jadwal aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk jadwal yang masih tersedia (belum penuh)
     */
    public function scopeAvailable($query)
    {
        return $query->whereColumn('terisi', '<', 'kuota');
    }

    /**
     * Check if jadwal is full
     */
    public function isFull(): bool
    {
        return $this->terisi >= $this->kuota;
    }

    /**
     * Get sisa kuota
     */
    public function getSisaKuotaAttribute(): int
    {
        return $this->kuota - $this->terisi;
    }
}
