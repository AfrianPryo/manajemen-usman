<?php

namespace App\Livewire\Master\Notifications;

use App\Support\Concerns\HandlesNotificationActions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman "Lihat Semua Notifikasi" -- arsip lengkap (dibaca maupun belum)
 * yang bisa difilter status & tipe dan dipaginasi, pelengkap lonceng di
 * header (App\Livewire\NotificationSidebar) yang hanya menampilkan 10
 * notifikasi belum dibaca terakhir.
 *
 * Class ini dipakai langsung untuk Master Admin dan diwarisi apa adanya oleh
 * App\Livewire\Unit\Notifications\Index (hanya Layout yang di-override).
 * Notifikasi melekat ke AKUN (`$user->notifications()`), bukan ke unit usaha,
 * jadi tidak perlu scoping per-unit di sini.
 *
 * Aksi buka detail / tandai dibaca / approve / reject ada di trait
 * HandlesNotificationActions, dipakai bersama dengan lonceng di header.
 */
#[Layout('components.layouts.app')]
#[Title('Notifikasi')]
class Index extends Component
{
    use HandlesNotificationActions;
    use WithPagination;

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

    public function markAsUnread(string $notificationId): void
    {
        $notification = Auth::user()?->notifications()->find($notificationId);

        // Yang butuh keputusan tidak boleh "dihidupkan lagi" setelah
        // diproses, karena tombol Approve/Reject akan aktif kembali.
        if ($notification && empty($notification->data['actionable'])) {
            $notification->markAsUnread();
        }

        $this->notificationsChanged();
    }

    public function delete(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->delete();

        $this->notificationsChanged();
    }

    /**
     * Daftar badge/tipe unik milik user ini untuk dropdown filter tipe.
     */
    private function availableBadges()
    {
        return Auth::user()
            ?->notifications()
            ->get(['data'])
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
