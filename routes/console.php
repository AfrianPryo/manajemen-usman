<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Menjalankan perintah pembersihan model (MassPrunable) setiap hari pukul 00:00
Schedule::command('model:prune')->daily();

// Proses transaksi berulang (auto-approve langsung dibuatkan transaksi,
// non-auto-approve dikirimi notifikasi konfirmasi) -- sebelumnya command
// ini ADA tapi tidak pernah terdaftar di sini sehingga tidak pernah
// berjalan otomatis. Dijalankan tiap hari jam 01:00 (setelah tengah malam,
// sebelum jam sibuk) supaya transaksi yang jatuh tempo hari ini langsung
// diproses/dinotifikasikan sejak pagi.
Schedule::command('recurring:process')->dailyAt('01:00');