<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Service OTP berbasis WhatsApp (Fonnte).
 *
 * Dipakai untuk verifikasi tindakan sensitif: ganti password & ganti nomor WA.
 * OTP disimpan di Cache (bukan DB) supaya otomatis expired, dan di-hash
 * sebelum disimpan supaya tidak bisa dibaca langsung meski cache bocor.
 */
class FonnteOtpService
{
    protected const OTP_TTL_MINUTES = 5;
    protected const MAX_VERIFY_ATTEMPTS = 3;
    protected const RESEND_COOLDOWN_SECONDS = 60;
    protected const MAX_SEND_PER_HOUR = 5;

    protected function otpCacheKey(int $userId, string $purpose): string
    {
        return "otp:{$purpose}:{$userId}";
    }

    protected function cooldownCacheKey(int $userId, string $purpose): string
    {
        return "otp-cooldown:{$purpose}:{$userId}";
    }

    protected function rateLimitKey(int $userId, string $purpose): string
    {
        return "otp-send-limit:{$purpose}:{$userId}";
    }

    /**
     * Generate OTP baru, simpan (di-hash), dan kirim ke nomor WA target.
     *
     * @param int    $userId
     * @param string $purpose      'password_change' | 'phone_change'
     * @param string $targetPhone  nomor tujuan pengiriman OTP
     * @return array{success: bool, message: string}
     */
    public function generateAndSend(int $userId, string $purpose, string $targetPhone): array
    {
        // 1. Rate limit: maksimal 5 kali kirim per jam per user per purpose
        $rlKey = $this->rateLimitKey($userId, $purpose);
        if (RateLimiter::tooManyAttempts($rlKey, self::MAX_SEND_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rlKey);
            $minutes = ceil($seconds / 60);
            return [
                'success' => false,
                'message' => "Terlalu banyak permintaan OTP. Coba lagi dalam {$minutes} menit.",
            ];
        }

        // 2. Cooldown antar-request: minimal 60 detik antar kirim OTP
        $cooldownKey = $this->cooldownCacheKey($userId, $purpose);
        if (Cache::has($cooldownKey)) {
            return [
                'success' => false,
                'message' => 'Mohon tunggu sebentar sebelum meminta kode OTP baru.',
            ];
        }

        if (empty($targetPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak tersedia. Hubungi admin untuk verifikasi manual.',
            ];
        }

        $otp = (string) random_int(100000, 999999);

        Cache::put($this->otpCacheKey($userId, $purpose), [
            'code'     => Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        $sent = $this->sendViaFonnte($targetPhone, $otp, $purpose);

        if (! $sent) {
            // Jangan hitung sebagai "terkirim" kalau gagal, tapi tetap catat sebagai attempt
            // supaya tidak dipakai untuk spam percobaan ke provider.
            RateLimiter::hit($rlKey, 3600);
            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP via WhatsApp. Periksa konfigurasi Fonnte atau coba lagi nanti.',
            ];
        }

        RateLimiter::hit($rlKey, 3600);
        Cache::put($cooldownKey, true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        return [
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke WhatsApp yang terdaftar.',
        ];
    }

    /**
     * Verifikasi kode OTP yang diinput user.
     *
     * @return array{success: bool, message: string}
     */
    public function verify(int $userId, string $purpose, string $inputOtp): array
    {
        $key  = $this->otpCacheKey($userId, $purpose);
        $data = Cache::get($key);

        if (! $data) {
            return [
                'success' => false,
                'message' => 'Kode OTP kadaluarsa atau belum diminta. Silakan minta kode baru.',
            ];
        }

        if ($data['attempts'] >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($key);
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.',
            ];
        }

        if (! Hash::check($inputOtp, $data['code'])) {
            $data['attempts']++;
            Cache::put($key, $data, now()->addMinutes(self::OTP_TTL_MINUTES));

            $sisa = self::MAX_VERIFY_ATTEMPTS - $data['attempts'];
            return [
                'success' => false,
                'message' => $sisa > 0
                    ? "Kode OTP salah. Sisa percobaan: {$sisa}."
                    : 'Kode OTP salah. Silakan minta kode baru.',
            ];
        }

        // Valid → langsung hapus supaya tidak bisa dipakai ulang (single use)
        Cache::forget($key);

        return ['success' => true, 'message' => 'Kode OTP valid.'];
    }

    /**
     * Batalkan OTP yang sedang aktif (mis. saat user membatalkan proses).
     */
    public function invalidate(int $userId, string $purpose): void
    {
        Cache::forget($this->otpCacheKey($userId, $purpose));
    }

    /**
     * Kirim pesan WhatsApp bebas (bukan OTP) lewat Fonnte, mis. untuk
     * pengumuman manual dari Master\Announcements\Index. Reuse kredensial
     * (wa_api_key) & normalisasi nomor yang sama dengan alur OTP, TANPA
     * ikut rate limit/cooldown OTP di atas (itu khusus alur keamanan).
     */
    public function sendPlainMessage(string $phone, string $message): bool
    {
        $token = Setting::get('wa_api_key');

        if (empty($token)) {
            Log::warning('FonnteOtpService: wa_api_key belum dikonfigurasi di pengaturan sistem.');
            return false;
        }

        if (empty($phone)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target'      => $this->normalizePhone($phone),
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

            if (! $response->successful()) {
                Log::error('FonnteOtpService: gagal kirim pesan WA', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FonnteOtpService: exception saat kirim pesan WA - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan OTP ke nomor tujuan lewat Fonnte.
     */
    protected function sendViaFonnte(string $phone, string $otp, string $purpose): bool
    {
        $label = match ($purpose) {
            'password_change' => 'perubahan password',
            'phone_change'     => 'perubahan nomor WhatsApp',
            default            => 'verifikasi akun',
        };

        $message = "Kode OTP Anda untuk {$label}: *{$otp}*\n\n"
            . "Kode berlaku " . self::OTP_TTL_MINUTES . " menit.\n"
            . "Jangan berikan kode ini kepada siapapun, termasuk yang mengaku sebagai admin/petugas.";

        return $this->sendPlainMessage($phone, $message);
    }

    /**
     * Normalisasi nomor ke format 62xxxxxxxxxx yang dipakai Fonnte.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}