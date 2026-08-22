<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RecurringTransaction;
use App\Models\FinanceTransaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationSidebar extends Component
{
    public string $role = 'master';
    public string $badgeText = 'Baru';
    public string $viewAllUrl = '#';

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function approve($notificationId)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);
        if (!$notification) return;

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                // Cari ID Kategori dengan Fallback otomatis
                $categoryId = $recurring->finance_category_id 
                    ?? $recurring->category_id 
                    ?? \App\Models\FinanceCategory::where('type', $recurring->type)->value('id');

                // Cegah eksekusi jika kategori tetap tidak ditemukan
                if (!$categoryId) {
                    session()->flash('error', 'Kategori keuangan tidak ditemukan untuk transaksi ini.');
                    return;
                }

                // 1. Buat Transaksi Riil Keuangan
                FinanceTransaction::create([
                    'unit_id'             => $recurring->unit_id,
                    'finance_category_id' => $categoryId,
                    'user_id'             => Auth::id() ?? 1,
                    'reference_no'        => 'TRX-REC-' . time() . '-' . $recurring->id,
                    'type'                => $recurring->type,
                    'status'              => 'completed',
                    'amount'              => $recurring->amount,
                    'description'         => "{$recurring->title} (Disetujui dari Transaksi Berulang)",
                    'transaction_date'    => now(),
                ]);

                // 2. Majukan tanggal jatuh tempo berikutnya
                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        // 3. Tandai notifikasi sebagai dibaca
        $notification->markAsRead();
    }

    public function reject($notificationId)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);
        if (!$notification) return;

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                // Majukan tanggal tanpa membuat transaksi riil
                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        $notification->markAsRead();
    }

    public function markAsRead($notificationId, $url = null)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }

        if ($url && $url !== '#') {
            return redirect()->to($url);
        }
    }

    public function markAllAsRead()
    {
        Auth::user()?->unreadNotifications->markAsRead();
    }

    private function calculateNextRunDate($currentDate, $frequency)
    {
        $date = Carbon::parse($currentDate);

        // Tampung hasil match ke variabel terlebih dahulu
        $nextDate = match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'yearly'  => $date->addYear(),
            default   => $date->addMonth(),
        };

        return $nextDate->toDateString();
    }

    public function render()
    {
        $notifications = Auth::check() 
            ? Auth::user()->unreadNotifications()->latest()->take(10)->get() 
            : collect();

        // Ubah 'livewire.notification-sidebar' menjadi 'components.notification-sidebar'
        return view('components.notification-sidebar', [
            'notifications' => $notifications
        ]);
    }
}