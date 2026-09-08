<?php

namespace App\Jobs;

use App\Services\FonnteOtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Kirim pesan WhatsApp NON-OTP (notifikasi kredensial akun baru, reset
 * password, broadcast pengumuman) lewat Fonnte secara asynchronous, supaya
 * panggilan Http::post() ke API eksternal Fonnte tidak lagi memblokir
 * request web yang memicunya (mis. approve reset password di Notification
 * Sidebar, buat admin baru, dsb).
 *
 * SENGAJA TIDAK dipakai untuk alur OTP (generateAndSend/verify) di
 * FonnteOtpService: OTP tetap sinkron seperti semula karena user butuh
 * feedback sukses/gagal terkirim SAAT ITU JUGA di halaman -- kalau OTP
 * dibuat async, kegagalan kirim baru diketahui belakangan padahal user
 * sudah menunggu kode masuk, dan itu akan merusak alur keamanan yang ada.
 */
class SendFonnteMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika pengiriman gagal (mis. API Fonnte
     * timeout/down sesaat). Pesan non-kritis, jadi retry singkat sudah
     * cukup -- tidak perlu retry agresif seperti alur pembayaran dsb.
     */
    public int $tries = 3;

    /**
     * Jeda antar percobaan ulang (detik).
     */
    public int $backoff = 30;

    public function __construct(
        public string $phone,
        public string $message,
    ) {
    }

    public function handle(FonnteOtpService $fonnte): void
    {
        $sent = $fonnte->sendPlainMessage($this->phone, $this->message);

        if (! $sent) {
            // sendPlainMessage() sudah mencatat detail error (status/body) ke
            // log. Di sini lempar exception supaya queue worker melakukan
            // retry otomatis sesuai $tries/$backoff di atas -- job baru
            // benar-benar ditandai "failed" oleh Laravel setelah percobaan
            // terakhir tetap gagal (bukan langsung gagal di percobaan pertama).
            throw new \RuntimeException("Gagal mengirim pesan WA via Fonnte ke {$this->phone}.");
        }
    }

    /**
     * Dipanggil Laravel setelah SEMUA percobaan ($tries) habis dan job tetap
     * gagal. Notifikasi WA memang non-kritis (tidak membatalkan proses utama
     * yang memicunya), tapi kegagalan akhir tetap perlu tercatat supaya bisa
     * ditindaklanjuti manual (mis. admin salin kredensial dari modal).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendFonnteMessageJob: gagal mengirim pesan WA setelah semua percobaan habis.', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}