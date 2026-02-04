<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    protected $table = 'prodi';

    protected $fillable = [
        'kode',
        'nama',
        'jenjang',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all pendaftar for this prodi
     */
    public function pendaftar(): HasMany
    {
        return $this->hasMany(Pendaftar::class);
    }

    /**
     * Get all users (staf prodi) assigned to this prodi
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope untuk prodi aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
