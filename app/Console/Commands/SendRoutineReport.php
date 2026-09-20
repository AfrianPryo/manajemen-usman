<?php

namespace App\Console\Commands;

use App\Services\RoutineReportService;
use Illuminate\Console\Command;

class SendRoutineReport extends Command
{
    protected $signature = 'report:routine-send';
    protected $description = 'Cek & kirim Laporan Rutin Otomatis (WhatsApp) ke Admin Master sesuai jadwal di Pengaturan';

    /**
     * Implementasi lengkap (due-check, susun pesan, kirim ke seluruh
     * Admin Master aktif) ada di App\Services\RoutineReportService,
     * mengikuti pola yang sama seperti ProcessRecurringTransactions ->
     * RecurringTransactionService. Command ini sengaja "tipis" supaya
     * gampang dites & tidak menduplikasi logika.
     */
    public function handle(RoutineReportService $service): void
    {
        $service->process();
    }
}
