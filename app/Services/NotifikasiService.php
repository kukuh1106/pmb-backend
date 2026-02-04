<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    private string $baseUrl;
    private string $session;

    public function __construct()
    {
        $this->baseUrl = config('services.waha.url', 'http://localhost:3000');
        $this->session = config('services.waha.session', 'default');
    }

    /**
     * Send WhatsApp message via WAHA API
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/sendText", [
                'chatId' => $this->formatPhone($phone),
                'text' => $message,
                'session' => $this->session,
            ]);

            if ($response->successful()) {
                Log::info("WA sent to {$phone}");
                return true;
            }

            Log::error("WA failed to {$phone}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WA exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send registration credentials
     */
    public function sendKredensial(string $phone, string $nama, string $nomorPendaftaran, string $kodeAkses): bool
    {
        $message = "🎓 *PMB Pascasarjana*\n\n";
        $message .= "Halo *{$nama}*,\n\n";
        $message .= "Selamat! Registrasi Anda berhasil.\n\n";
        $message .= "📋 *Nomor Pendaftaran:* {$nomorPendaftaran}\n";
        $message .= "🔑 *Kode Akses:* {$kodeAkses}\n\n";
        $message .= "Silakan login untuk melengkapi biodata dan dokumen persyaratan.\n\n";
        $message .= "_Simpan pesan ini dengan baik._";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send exam result notification
     */
    public function sendHasilUjian(string $phone, string $nama, string $status, ?float $nilai = null): bool
    {
        $emoji = $status === 'lulus' ? '🎉' : '📢';
        $statusText = $status === 'lulus' ? 'LULUS' : 'TIDAK LULUS';

        $message = "{$emoji} *PMB Pascasarjana - Pengumuman*\n\n";
        $message .= "Yth. *{$nama}*,\n\n";
        $message .= "Hasil seleksi Anda: *{$statusText}*\n";
        
        if ($nilai !== null) {
            $message .= "Nilai: *{$nilai}*\n";
        }
        
        $message .= "\nSilakan login untuk melihat detail hasil seleksi.\n\n";
        $message .= "_Terima kasih._";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send document invalid notification
     */
    public function sendDokumenTidakValid(string $phone, string $nama, string $jenisDokumen, string $catatan): bool
    {
        $message = "⚠️ *PMB Pascasarjana - Dokumen Tidak Valid*\n\n";
        $message .= "Yth. *{$nama}*,\n\n";
        $message .= "Dokumen *{$jenisDokumen}* Anda memerlukan perbaikan:\n";
        $message .= "_{$catatan}_\n\n";
        $message .= "Silakan login dan upload ulang dokumen yang diperlukan.";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send custom notification
     */
    public function sendCustom(string $phone, string $nama, string $subject, string $content): bool
    {
        $message = "📢 *PMB Pascasarjana - {$subject}*\n\n";
        $message .= "Yth. *{$nama}*,\n\n";
        $message .= $content;

        return $this->sendMessage($phone, $message);
    }

    /**
     * Format phone number for WhatsApp
     * Convert 08xxx to 628xxx format
     */
    private function formatPhone(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Add @c.us suffix for WAHA
        return $phone . '@c.us';
    }
}
