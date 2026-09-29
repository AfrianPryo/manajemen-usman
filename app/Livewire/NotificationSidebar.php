<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\Concerns\HandlesNotificationActions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Lonceng notifikasi di header (slide-over). Menampilkan 10 notifikasi BELUM
 * DIBACA terakhir; arsip lengkapnya ada di halaman
 * App\Livewire\Master\Notifications\Index (link "Lihat Semua Notifikasi").
 *
 * Seluruh aksi (buka detail, tandai dibaca, approve/reject) ada di trait
 * HandlesNotificationActions dan dipakai bersama dengan halaman penuh.
 */
class NotificationSidebar extends Component
{
    use HandlesNotificationActions;

    public string $role = 'master';
    public string $badgeText = 'Baru';
    public string $viewAllUrl = '#';

    protected $listeners = ['refreshNotifications' => '$refresh'];

    /** Komponen ini sendiri yang sedang di-render ulang, tidak perlu event. */
    protected function notificationsChanged(): void
    {
        //
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $limit         = 10;
        $notifications = $user
            ? $user->unreadNotifications()->latest()->take($limit)->get()
            : collect();

        // OPTIMASI: sebelumnya selalu menembak 2 query (list + count()).
        // Kalau list yang diambil belum penuh (< $limit), jumlah itu SUDAH
        // sama persis dengan total unread, jadi query count() tidak perlu.
        // Hanya kalau list penuh (mungkin ada lebih dari $limit unread)
        // count() tetap dijalankan supaya angka badge tetap akurat.
        if (! $user) {
            $unreadCount = 0;
        } elseif ($notifications->count() < $limit) {
            $unreadCount = $notifications->count();
        } else {
            $unreadCount = $user->unreadNotifications()->count();
        }

        return view('components.notification-sidebar', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
