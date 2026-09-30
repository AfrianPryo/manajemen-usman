<?php

namespace App\Support;

/**
 * Helper normalisasi nomor telepon ke format E.164 ("+6281234567890").
 *
 * Dipakai sebagai lapisan pengaman di SERVER untuk komponen <x-phone-input>:
 * JavaScript di browser sudah mengirim format E.164, tetapi input dari client
 * tidak pernah boleh dipercaya begitu saja (JS mati, request dimanipulasi,
 * data lama di database yang masih berformat "08xxx", dst).
 */
class PhoneNumber
{
    /** Kode negara default (Indonesia) untuk nomor lokal tanpa kode negara. */
    public const DEFAULT_DIAL = '62';

    /**
     * Ubah berbagai format input menjadi E.164. Mengembalikan '' bila kosong.
     *
     *   "0812-3456-7890"   -> "+6281234567890"
     *   "6281234567890"    -> "+6281234567890"
     *   "+62 812 3456 7890"-> "+6281234567890"
     *   "812-3456-7890"    -> "+6281234567890"
     *   "0060123456789"    -> "+60123456789"
     */
    public static function normalize(?string $raw, string $defaultDial = self::DEFAULT_DIAL): string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($raw, '+')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return '+' . $defaultDial . ltrim($digits, '0');
        }

        if (str_starts_with($digits, $defaultDial) && strlen($digits) >= strlen($defaultDial) + 8) {
            return '+' . $digits;
        }

        return '+' . $defaultDial . $digits;
    }

    /**
     * Semua bentuk penulisan yang mungkin tersimpan di database untuk nomor
     * yang sama. Dipakai untuk cek UNIQUE supaya "081234567890" (data lama)
     * dan "+6281234567890" (format baru) terdeteksi sebagai nomor yang sama.
     *
     * @return list<string>
     */
    public static function variants(string $e164): array
    {
        $digits = ltrim($e164, '+');
        $variants = ['+' . $digits, $digits];

        if (str_starts_with($digits, self::DEFAULT_DIAL)) {
            $variants[] = '0' . substr($digits, strlen(self::DEFAULT_DIAL));
        }

        return array_values(array_unique($variants));
    }

    /**
     * Nomor seluler Indonesia selalu diawali 8 setelah +62 dan punya total
     * 9-12 digit nasional. Negara lain cukup lolos aturan panjang E.164.
     */
    public static function isValid(string $e164): bool
    {
        if (! preg_match('/^\+[1-9][0-9]{7,14}$/', $e164)) {
            return false;
        }

        if (str_starts_with($e164, '+62')) {
            return (bool) preg_match('/^\+628[0-9]{8,11}$/', $e164);
        }

        return true;
    }
}