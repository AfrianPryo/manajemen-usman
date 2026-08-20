<?php
namespace App\Livewire;

use Livewire\Component;

class NotificationSidebar extends Component
{
    // Mengubah status 1 notifikasi menjadi sudah dibaca
    public function markAsRead($notificationId, $redirectUrl = null)
    {
        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if ($redirectUrl && $redirectUrl !== '#') {
            return redirect()->to($redirectUrl);
        }
    }

    // Mengubah status semua notifikasi menjadi sudah dibaca
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = auth()->user();
        
        // Mengambil 20 notifikasi terbaru & jumlah yang belum dibaca
        $notifications = $user ? $user->notifications()->take(20)->get() : collect();
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;

        return view('livewire.notification-sidebar', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}