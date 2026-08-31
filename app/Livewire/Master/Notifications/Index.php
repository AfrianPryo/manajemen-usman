<?php

namespace App\Livewire\Master\Notifications;

use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\FonnteOtpService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman "Lihat Semua Notifikasi" -- pelengkap App\Livewire\NotificationSidebar
 * (lonceng di header) yang cuma menampilkan 10 notifikasi BELUM DIBACA
 * terakhir. Sidebar itu dead-end: begitu ditandai dibaca atau lebih dari
 * 10 item, notifikasi lama hilang tanpa bisa dilihat lagi. Halaman ini
 * jadi arsip lengkapnya -- SEMUA notifikasi (dibaca maupun belum), bisa
 * difilter status & tipe, dan dipaginasi -- sekaligus mengisi link
 * "Lihat Semua Notifikasi" di footer sidebar yang sebelumnya selalu
 * mengarah ke '#' (lihat components.layouts.app & components.layouts.unit,
 * properti $viewAllUrl yang tidak pernah di-set dari mana pun).
 *
 * Class ini dipakai LANGSUNG untuk Master Admin, dan diwarisi apa adanya
 * oleh App\Livewire\Unit\Notifications\Index (hanya override Layout &
 * label kategori) -- pola yang sama persis dengan App\Livewire\Unit\
 * Profile\Index yang mewarisi Master\Settings\Index. Notifikasi memang
 * melekat ke AKUN (`$user->notifications()`), bukan ke unit usaha, jadi
 * tidak perlu scoping per-unit (trait Unit\Concerns\ScopedToUnit) sama
 * sekali di sini -- otomatis benar untuk Master Admin maupun Unit Admin.
 *
 * Aksi approve()/reject() untuk notifikasi "Konfirmasi Transaksi Berulang"
 * (actionable: true, lihat App\Notifications\SystemNotification) sengaja
 * ikut disediakan di sini juga -- logic-nya disalin dari
 * NotificationSidebar::approve()/reject() supaya halaman ini berdiri
 * sendiri. Ini BUKAN fitur khusus Master: lihat
 * Commands\ProcessRecurringTransactions::handle(), notifikasi itu dikirim
 * ke User::all() (termasuk Unit Admin), jadi tombolnya perlu ada juga di
 * versi Unit Admin dari halaman ini.
 *
 * Sejak ditambahkannya fitur "Ajukan Reset Password" (lihat App\Livewire\
 * Master\Settings\Index::requestPasswordReset() & App\Livewire\Unit\
 * Profile\Index), approve()/reject() di sini JUGA menangani notifikasi
 * bertipe 'password_reset_request' -- logic-nya disalin persis dari
 * NotificationSidebar::approvePasswordResetRequest() dengan alasan yang
 * sama (halaman ini berdiri sendiri, tidak reuse Livewire component lain).
 */
#[Layout('components.layouts.app')]
#[Title('Notifikasi')]
class Index extends Component
{
    use WithPagination;

    // Kredensial (username/password baru) untuk ditampilkan lewat popup
    // <x-credentials-modal> setelah Approve permintaan reset password --
    // lihat approvePasswordResetRequest() di bawah. Sama persis dengan pola
    // App\Livewire\Master\Users\Index::$createdCredentials & App\Livewire\
    // NotificationSidebar::$createdCredentials (nama properti WAJIB sama,
    // lihat komentar di resources/views/components/credentials-modal.blade.php).
    public ?array $createdCredentials = null;

    // Filter status baca: 'all' | 'unread' | 'read'
    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    // Filter tipe/badge notifikasi (mis. 'Pengumuman'). 'all' = semua tipe
    #[Url(as: 'tipe', history: true)]
    public string $badgeFilter = 'all';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBadgeFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Buka satu notifikasi: tandai dibaca, lalu redirect ke url terkait
     * (kalau ada dan bukan placeholder '#').
     */
    public function open(string $notificationId, ?string $url = null)
    {
        Auth::user()?->notifications()->find($notificationId)?->markAsRead();

        if ($url && $url !== '#') {
            return redirect()->to($url);
        }

        return null;
    }

    public function markAsRead(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->markAsRead();
    }

    public function markAsUnread(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->markAsUnread();
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();

        // Supaya lonceng notifikasi di header (badge unread) ikut ter-refresh
        // tanpa perlu reload halaman -- NotificationSidebar sudah listen
        // event ini (lihat protected $listeners di sana).
        $this->dispatch('refreshNotifications');
    }

    public function delete(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->delete();
    }

    /**
     * Setujui transaksi berulang yang menunggu konfirmasi.
     * Mirror App\Livewire\NotificationSidebar::approve() apa adanya.
     */
    public function approve(string $notificationId): void
    {
        $notification = Auth::user()?->notifications()->find($notificationId);
        if (! $notification) {
            return;
        }

        if (($notification->data['type'] ?? null) === 'password_reset_request') {
            $this->approvePasswordResetRequest($notification);
            $notification->markAsRead();
            return;
        }

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                $categoryId = $recurring->finance_category_id
                    ?? $recurring->category_id
                    ?? FinanceCategory::where('type', $recurring->type)->value('id');

                if (! $categoryId) {
                    session()->flash('error', 'Kategori keuangan tidak ditemukan untuk transaksi ini.');
                    return;
                }

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

                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        $notification->markAsRead();
    }

    /**
     * Mirror App\Livewire\NotificationSidebar::approvePasswordResetRequest()
     * apa adanya -- lihat komentar lengkap di sana.
     */
    private function approvePasswordResetRequest($notification): void
    {
        $targetUserId = $notification->data['target_user_id'] ?? null;
        $user = $targetUserId ? User::find($targetUserId) : null;

        if (! $user) {
            session()->flash('error', 'Akun admin yang mengajukan permintaan ini sudah tidak ditemukan.');
            return;
        }

        $newPassword = Str::random(8);
        $user->password = Hash::make($newPassword);
        $user->must_change_password = true;
        $user->save();

        AuditLog::record(
            event: 'ADMIN_PASSWORD_RESET',
            identifier: $user->username,
            description: "Admin master menyetujui permintaan reset password dari '{$user->username}' via notifikasi.",
            oldValues: null,
            newValues: ['must_change_password' => true]
        );

        $waMessage = "🔑 *Password Direset*\n\n"
            . "Halo {$user->name}, permintaan reset password Anda telah disetujui oleh Master Admin.\n\n"
            . "Username: *{$user->username}*\n"
            . "Password baru: *{$newPassword}*\n\n"
            . "Segera login dan ganti password Anda. Jangan bagikan kredensial ini kepada siapapun.";

        $waSent = app(FonnteOtpService::class)->sendPlainMessage($user->phone, $waMessage);

        // Munculkan popup kredensial -- lihat komentar identik di
        // App\Livewire\NotificationSidebar::approvePasswordResetRequest().
        $this->createdCredentials = [
            'title'    => '🔑 Permintaan Reset Password Disetujui!',
            'name'     => $user->name,
            'username' => $user->username,
            'password' => $newPassword,
            'wa_sent'  => $waSent,
        ];
    }

    /**
     * Tolak transaksi berulang yang menunggu konfirmasi.
     * Mirror App\Livewire\NotificationSidebar::reject() apa adanya.
     */
    public function reject(string $notificationId): void
    {
        $notification = Auth::user()?->notifications()->find($notificationId);
        if (! $notification) {
            return;
        }

        if (($notification->data['type'] ?? null) === 'password_reset_request') {
            $targetUserName = $notification->data['target_user_name'] ?? null;
            session()->flash('message', 'Permintaan reset password'
                . ($targetUserName ? " dari '{$targetUserName}'" : '')
                . ' ditolak.');
            $notification->markAsRead();
            return;
        }

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        $notification->markAsRead();
    }

    private function calculateNextRunDate($currentDate, $frequency)
    {
        $date = Carbon::parse($currentDate);

        $nextDate = match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'yearly'  => $date->addYear(),
            default   => $date->addMonth(),
        };

        return $nextDate->toDateString();
    }

    /**
     * Daftar badge/tipe unik yang pernah muncul di notifikasi user ini,
     * dipakai untuk isi dropdown filter tipe.
     */
    private function availableBadges()
    {
        return Auth::user()
            ?->notifications()
            ->get()
            ->pluck('data.badge')
            ->filter()
            ->unique()
            ->sort()
            ->values() ?? collect();
    }

    public function render()
    {
        $user = Auth::user();
        $query = $user?->notifications();

        if ($query) {
            if ($this->statusFilter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($this->statusFilter === 'read') {
                $query->whereNotNull('read_at');
            }

            if ($this->badgeFilter !== 'all') {
                $query->where('data->badge', $this->badgeFilter);
            }

            $notifications = $query->latest()->paginate(15);
        } else {
            $notifications = new LengthAwarePaginator([], 0, 15);
        }

        return view('livewire.master.notifications.index', [
            'notifications'   => $notifications,
            'unreadCount'     => $user?->unreadNotifications()->count() ?? 0,
            'totalCount'      => $user?->notifications()->count() ?? 0,
            'availableBadges' => $this->availableBadges(),
        ]);
    }
}