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

    protected $appends = ['file_url'];

    /**
     * Get the pendaftar for this dokumen
     */
    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(Pendaftar::class);
    }

    /**
     * Get file url attribute
     */
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        // If file_path is already a full URL
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        // Generate URL based on disk
        try {
            if (config('filesystems.default') === 's3') {
                return \Illuminate\Support\Facades\Storage::temporaryUrl(
                    $this->file_path,
                    now()->addMinutes(60)
                );
            }
        } catch (\Exception $e) {
            // Fallback if temporaryUrl fails or not supported
        }

        return \Illuminate\Support\Facades\Storage::url($this->file_path);
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
