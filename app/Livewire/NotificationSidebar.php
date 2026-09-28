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

        return view('components.notification-sidebar', [
            'notifications' => $user
                ? $user->unreadNotifications()->latest()->take(10)->get()
                : collect(),
            'unreadCount'   => $user ? $user->unreadNotifications()->count() : 0,
        ]);
    }
}
