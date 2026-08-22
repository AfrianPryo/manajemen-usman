<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTransaction;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Proses transaksi berulang otomatis atau kirim notifikasi konfirmasi';

    public function handle()
    {
        $today = now()->toDateString();

        // Ambil transaksi aktif yang sudah memasuki tanggal jatuh tempo
        $recurringTransactions = RecurringTransaction::where('status', 'active')
            ->whereDate('next_run_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
            })
            ->get();

        foreach ($recurringTransactions as $item) {
            if ($item->auto_approve) {
                // JIKA OTOMATIS: Langsung buat transaksi & update tanggal berikutnya
                FinanceTransaction::create([
                    'unit_id' => $item->unit_id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'transaction_date' => now(),
                    'notes' => 'Otomatis dibuat dari Sistem Transaksi Berulang',
                ]);

                $item->next_run_date = $this->calculateNextRunDate($item->next_run_date, $item->frequency);
                $item->save();
            } else {
                // JIKA KONFIRMASI MANUAL: Kirim Notifikasi Interaktif
                $targetUsers = User::all(); // Atur filter penerima, misal: User::where('role', 'admin')->get();

                foreach ($targetUsers as $user) {
                    // Mencegah duplikasi notifikasi jika notifikasi sebelumnya belum dibaca
                    $hasPending = $user->unreadNotifications()
                        ->where('data->recurring_transaction_id', $item->id)
                        ->exists();

                    if (!$hasPending) {
                        $user->notify(new SystemNotification(
                            title: 'Konfirmasi Transaksi Berulang',
                            message: "Transaksi '{$item->title}' (Rp " . number_format($item->amount, 0, ',', '.') . ") memerlukan konfirmasi Anda.",
                            badge: 'Jatuh Tempo',
                            actionable: true,
                            url: route('master.recurring-transactions.index'),
                            extraData: [
                                'recurring_transaction_id' => $item->id
                            ]
                        ));
                    }
                }
            }
        }
    }

    private function calculateNextRunDate($currentDate, $frequency)
    {
        $date = Carbon::parse($currentDate);
        return match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'yearly'  => $date->addYear(),
            default   => $date->addMonth(),
        }->toDateString();
    }
}