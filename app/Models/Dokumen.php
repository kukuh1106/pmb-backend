<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    protected $fillable = [
        'pendaftar_id',
        'jenis_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'status_verifikasi',
        'catatan',
    ];

    /**
     * Get the pendaftar for this dokumen
     */
    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(Pendaftar::class);
    }

    /**
     * Scope by jenis dokumen
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    /**
     * Scope for pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'pending');
    }

    /**
     * Scope for valid documents
     */
    public function scopeValid($query)
    {
        return $query->where('status_verifikasi', 'valid');
    }

    /**
     * Scope for invalid documents
     */
    public function scopeInvalid($query)
    {
        return $query->where('status_verifikasi', 'tidak_valid');
    }
}
