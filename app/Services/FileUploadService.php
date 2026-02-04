<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\Pendaftar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
    }

    /**
     * Upload dokumen for pendaftar
     */
    public function uploadDokumen(Pendaftar $pendaftar, UploadedFile $file, string $jenisDokumen): Dokumen
    {
        // Validate file type
        $this->validateDokumenFile($file, $jenisDokumen);

        // Generate unique filename
        $filename = $this->generateFilename($pendaftar, $jenisDokumen, $file->getClientOriginalExtension());
        
        // Store file
        $path = $file->storeAs(
            "dokumen/{$pendaftar->nomor_pendaftaran}",
            $filename,
            $this->disk
        );

        // Delete old dokumen if exists
        $oldDokumen = Dokumen::where('pendaftar_id', $pendaftar->id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->first();

        if ($oldDokumen) {
            Storage::disk($this->disk)->delete($oldDokumen->file_path);
            $oldDokumen->delete();
        }

        // Create dokumen record
        return Dokumen::create([
            'pendaftar_id' => $pendaftar->id,
            'jenis_dokumen' => $jenisDokumen,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'status_verifikasi' => 'pending',
        ]);
    }

    /**
     * Upload foto for pendaftar
     */
    public function uploadFoto(Pendaftar $pendaftar, UploadedFile $file): string
    {
        // Validate file type (JPG/JPEG only)
        $allowedExtensions = ['jpg', 'jpeg'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('File foto harus berformat JPG/JPEG');
        }

        // Validate file size (max 2MB)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \InvalidArgumentException('Ukuran file foto maksimal 2MB');
        }

        // Generate unique filename
        $filename = "foto_{$pendaftar->nomor_pendaftaran}.{$extension}";
        
        // Store file
        $path = $file->storeAs(
            "foto",
            $filename,
            $this->disk
        );

        // Delete old foto if exists
        if ($pendaftar->foto_path) {
            Storage::disk($this->disk)->delete($pendaftar->foto_path);
        }

        // Update pendaftar foto path
        $pendaftar->update(['foto_path' => $path]);

        return $path;
    }

    /**
     * Get file URL
     * For S3, returns temporary signed URL (valid for 1 hour)
     * For local storage, returns regular URL
     */
    public function getFileUrl(string $path): string
    {
        if ($this->disk === 's3') {
            return $this->getTemporaryUrl($path);
        }
        
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Get temporary signed URL for S3 files
     * 
     * @param string $path File path in storage
     * @param int $expiresInMinutes URL expiration time in minutes (default: 60)
     * @return string Temporary signed URL
     */
    public function getTemporaryUrl(string $path, int $expiresInMinutes = 60): string
    {
        if (!Storage::disk($this->disk)->exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        return Storage::disk($this->disk)->temporaryUrl(
            $path,
            now()->addMinutes($expiresInMinutes)
        );
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get file size in bytes
     */
    public function getFileSize(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Delete file
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Validate dokumen file
     */
    private function validateDokumenFile(UploadedFile $file, string $jenisDokumen): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // pas_foto allows JPG/JPEG, others PDF only
        if ($jenisDokumen === 'pas_foto') {
            $allowedExtensions = ['jpg', 'jpeg'];
            $maxSize = 2 * 1024 * 1024; // 2MB
        } else {
            $allowedExtensions = ['pdf'];
            $maxSize = 5 * 1024 * 1024; // 5MB
        }

        if (!in_array($extension, $allowedExtensions)) {
            $allowedStr = implode('/', array_map('strtoupper', $allowedExtensions));
            throw new \InvalidArgumentException(
                "File {$jenisDokumen} harus berformat {$allowedStr}"
            );
        }

        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \InvalidArgumentException(
                "Ukuran file {$jenisDokumen} maksimal {$maxSizeMB}MB"
            );
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(Pendaftar $pendaftar, string $jenisDokumen, string $extension): string
    {
        $timestamp = now()->format('YmdHis');
        return "{$jenisDokumen}_{$pendaftar->nomor_pendaftaran}_{$timestamp}.{$extension}";
    }
}
