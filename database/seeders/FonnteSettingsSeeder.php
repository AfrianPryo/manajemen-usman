<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class FonnteSettingsSeeder extends Seeder
{
    /**
     * Mengisi pengaturan Fonnte AWAL dari config('services.fonnte.*')
     * (yang dibaca dari .env). Tujuannya supaya OTP & notifikasi WA
     * langsung jalan sejak login pertama tanpa harus isi manual di
     * Pengaturan.
     *
     * Aturan: seeder ini HANYA MENGISI, tidak pernah menimpa.
     *  - Baris yang sudah ada & nilainya terisi (mis. sudah diubah lewat
     *    Pengaturan) dibiarkan apa adanya, jadi aman dijalankan ulang
     *    saat deploy.
     *  - Kalau FONNTE_TOKEN kosong, seluruh seeder dilewati -- alur
     *    "Lewati" pada verifikasi OTP tetap berlaku seperti biasa dan
     *    notifikasi WA tidak diaktifkan tanpa token.
     *
     * Semua nilai tetap bisa diubah kapan saja di Master > Pengaturan
     * (Setting::set), sumber kebenarannya tetap tabel `settings`.
     */
    public function run(): void
    {
        $token = trim((string) config('services.fonnte.token', ''));

        if ($token === '') {
            $this->command?->warn('FonnteSettingsSeeder dilewati: FONNTE_TOKEN kosong.');

            return;
        }

        $defaults = [
            'wa_api_key'              => $token,
            'wa_provider'             => 'fonnte',
            'wa_sender_number'        => trim((string) config('services.fonnte.sender', '')),
            'enable_wa_notifications' => true,  // tersimpan '1'
            'wa_notify_credentials'   => true,
            'wa_notify_announcements' => true,
        ];

        foreach ($defaults as $key => $value) {
            // find() langsung ke DB (bukan Setting::get) supaya tidak
            // tertipu nilai default yang ikut ter-cache.
            $existing = Setting::find($key);

            if ($existing !== null && trim((string) $existing->value) !== '') {
                continue; // sudah ada / sudah diubah admin -> jangan timpa
            }

            if ($value === '' || $value === null) {
                continue; // jangan membuat baris kosong tanpa isi
            }

            // set() sekaligus membersihkan cache key terkait.
            Setting::set($key, $value);
        }
    }
}