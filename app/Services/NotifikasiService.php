<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    private string $baseUrl;
    private string $deviceId;
    private ?string $basicAuthUser;
    private ?string $basicAuthPassword;

    public function __construct()
    {
        $this->baseUrl = config('services.gowa.url', 'http://localhost:3000');
        $this->deviceId = config('services.gowa.device_id', '');
        $this->basicAuthUser = config('services.gowa.basic_auth_user');
        $this->basicAuthPassword = config('services.gowa.basic_auth_password');
    }

    /**
     * Send WhatsApp message via GOWA (go-whatsapp-web-multidevice) API
     * 
     * @see https://github.com/aldinokemal/go-whatsapp-web-multidevice
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $request = Http::asJson();
            
            // Add basic auth if configured
            if (!empty($this->basicAuthUser) && !empty($this->basicAuthPassword)) {
                $request = $request->withBasicAuth($this->basicAuthUser, $this->basicAuthPassword);
            }
            
            // Add device ID header if configured
            if (!empty($this->deviceId)) {
                $request = $request->withHeaders([
                    'X-Device-Id' => $this->deviceId,
                ]);
            }
            
            // GOWA API endpoint: POST /send/message
            $response = $request->post("{$this->baseUrl}/send/message", [
                'phone' => $this->formatPhone($phone),
                'message' => $message,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['code']) && $body['code'] === 'SUCCESS') {
                    Log::info("WA sent to {$phone}");
                    return true;
                }
                Log::error("WA failed to {$phone}: " . $response->body());
                return false;
            }

            Log::error("WA failed to {$phone}: HTTP {$response->status()} - " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WA exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check WhatsApp connection status
     */
    public function checkStatus(): array
    {
        try {
            $request = Http::asJson();
            
            if (!empty($this->basicAuthUser) && !empty($this->basicAuthPassword)) {
                $request = $request->withBasicAuth($this->basicAuthUser, $this->basicAuthPassword);
            }
            
            if (!empty($this->deviceId)) {
                $request = $request->withHeaders([
                    'X-Device-Id' => $this->deviceId,
                ]);
            }
            
            $response = $request->get("{$this->baseUrl}/app/status");

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'connected' => $body['results']['is_connected'] ?? false,
                    'logged_in' => $body['results']['is_logged_in'] ?? false,
                    'device_id' => $body['results']['device_id'] ?? null,
                ];
            }

            return [
                'connected' => false,
                'logged_in' => false,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'logged_in' => false,
                'error' => $e->getMessage(),
            ];
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
     * Send document valid notification
     */
    public function sendDokumenValid(string $phone, string $nama, string $jenisDokumen): bool
    {
        $message = "✅ *PMB Pascasarjana - Dokumen Terverifikasi*\n\n";
        $message .= "Yth. *{$nama}*,\n\n";
        $message .= "Dokumen *{$jenisDokumen}* Anda telah diverifikasi dan dinyatakan *VALID*.\n\n";
        $message .= "Silakan login untuk melihat status dokumen lainnya.";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send all documents verified notification
     */
    public function sendVerifikasiSelesai(string $phone, string $nama): bool
    {
        $message = "🎉 *PMB Pascasarjana - Verifikasi Selesai*\n\n";
        $message .= "Yth. *{$nama}*,\n\n";
        $message .= "Selamat! *Semua dokumen* Anda telah diverifikasi dan dinyatakan *VALID*.\n\n";
        $message .= "Silakan login untuk melihat informasi selanjutnya dan menunggu pengumuman hasil seleksi.\n\n";
        $message .= "_Terima kasih atas partisipasi Anda._";

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
     * Convert 08xxx to 628xxx@s.whatsapp.net format
     */
    private function formatPhone(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Add @s.whatsapp.net suffix for GOWA
        return $phone . '@s.whatsapp.net';
    }
}
