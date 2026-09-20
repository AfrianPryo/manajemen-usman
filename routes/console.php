<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Menjalankan perintah pembersihan model (MassPrunable) setiap hari pukul 00:00.
// CATATAN: AuditLog TIDAK lagi memakai MassPrunable (dulu langsung menghapus
// log >= 90 hari tanpa jejak) -- pembersihan log login & audit log sekarang
// lewat 'logs:archive' di bawah (arsip Excel dulu, baru hapus).
Schedule::command('model:prune')->daily();

// Rotasi log: ekspor log login & audit log yang sudah lewat batas retensi
// (Pengaturan > Fitur & Modul > Retensi & Arsip Log, default 90 hari) ke
// file Excel per bulan di storage/app/private/log-archives, VERIFIKASI
// isinya, baru hapus dari tabel utama. Lihat App\Services\LogArchiveService.
// Jam 02:00 = jam sepi, setelah model:prune (00:00) & recurring:process (01:00).
// withoutOverlapping() mencegah dobel kalau eksekusi sebelumnya (mis. backlog
// besar di run pertama) belum selesai.
Schedule::command('logs:archive')->dailyAt('02:00')->withoutOverlapping();

// Proses transaksi berulang (auto-approve langsung dibuatkan transaksi,
// non-auto-approve dikirimi notifikasi konfirmasi) -- sebelumnya command
// ini ADA tapi tidak pernah terdaftar di sini sehingga tidak pernah
// berjalan otomatis. Dijalankan tiap hari jam 01:00 (setelah tengah malam,
// sebelum jam sibuk) supaya transaksi yang jatuh tempo hari ini langsung
// diproses/dinotifikasikan sejak pagi.
Schedule::command('recurring:process')->dailyAt('01:00');

// Laporan Rutin Otomatis (WhatsApp) ke Admin Master -- lihat
// App\Services\RoutineReportService. Dijalankan tiap 15 menit (BUKAN
// exact-match ke jam yang dikonfigurasi admin di Pengaturan) supaya
// pengiriman tetap terjadi walau scheduler/queue sempat berhenti persis
// di jam terjadwal; service sendiri yang menentukan apakah sudah
// waktunya kirim & mencegah kiriman dobel untuk periode yang sama.
Schedule::command('report:routine-send')->everyFifteenMinutes();