<?php

namespace App\Livewire\Unit\Notifications;

use App\Livewire\Master\Notifications\Index as MasterNotificationsIndex;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari halaman "Notifikasi".
 *
 * Mewarisi App\Livewire\Master\Notifications\Index APA ADANYA -- termasuk
 * render()-nya, yang sudah hardcode ke view 'livewire.master.notifications.
 * index' (satu view dipakai bersama, sama seperti App\Livewire\Unit\
 * Profile\Index yang mewarisi Master\Settings\Index dan memakai view
 * 'livewire.master.settings.index' yang sama persis).
 *
 * Tidak perlu override render() ataupun logic filter/paginasi apa pun --
 * notifikasi melekat ke AKUN yang sedang login (`$user->notifications()`),
 * bukan ke unit usaha, jadi otomatis benar untuk Master Admin maupun Unit
 * Admin tanpa scoping tambahan. Yang di-override di sini HANYA Layout,
 * supaya halaman dirender dengan sidebar Unit (bukan Master).
 */
#[Layout('components.layouts.unit', [
    'category' => 'Notifikasi',
    'role'     => 'unit',
])]
class Index extends MasterNotificationsIndex
{
    //
}
