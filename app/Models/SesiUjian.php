<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiUjian extends Model
{
    protected $table = 'sesi_ujian';

    protected $fillable = [
        'nama',
        'jam_mulai',
        'jam_selesai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all jadwal ujian for this sesi
     */
    public function jadwalUjian(): HasMany
    {
        return $this->hasMany(JadwalUjian::class, 'sesi_id');
    }

    /**
     * Scope untuk sesi aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
