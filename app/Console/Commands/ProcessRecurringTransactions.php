<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Proses transaksi berulang otomatis atau kirim notifikasi konfirmasi';

    /**
     * Implementasi sesungguhnya sudah dipindah & disatukan ke
     * App\Services\RecurringTransactionService, supaya command terjadwal
     * ini dan App\Livewire\Master\RecurringTransaction\Index (yang
     * menjalankan pengecekan sama saat halaman dibuka) selalu memakai
     * SATU logika yang sama persis -- lihat docblock di service tersebut
     * untuk detail alasan penggabungan & perbaikan N+1-nya.
     */
    public function handle(RecurringTransactionService $service): void
    {
        $service->processDueTransactions();
    }
}