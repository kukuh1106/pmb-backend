<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuangUjian extends Model
{
    protected $table = 'ruang_ujian';

    protected $fillable = [
        'kode',
        'nama',
        'kapasitas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all jadwal ujian for this ruang
     */
    public function jadwalUjian(): HasMany
    {
        return $this->hasMany(JadwalUjian::class, 'ruang_id');
    }

    /**
     * Scope untuk ruang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
