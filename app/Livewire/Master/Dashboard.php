<?php

namespace App\Livewire\Master;

use App\Models\AuthLog;
use App\Models\Unit;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard Master Admin')]
class Dashboard extends Component
{
    public function render()
    {
        // Agar diffForHumans() berbahasa Indonesia ("2 menit yang lalu")
        \Carbon\Carbon::setLocale('id');

        $units = Unit::with('users')->orderBy('name')->get();
        $users = User::with(['unit', 'roles'])->orderBy('name')->get();
        $logs  = AuthLog::latest()->limit(6)->get();

        $totalUnits  = $units->count();
        $activeUnits = $units->where('is_active', true)->count();

        return view('livewire.master.dashboard', [
            'units'         => $units,
            'users'         => $users,
            'logs'          => $logs,
            'totalUnits'    => $totalUnits,
            'activeUnits'   => $activeUnits,
            'inactiveUnits' => $totalUnits - $activeUnits,
            'totalAdmins'   => $users->filter(fn ($u) => $u->isUnitAdmin())->count(),
        ]);
    }

    /**
     * Label & warna badge untuk setiap jenis log aktivitas.
     */
    public function eventInfo(string $event): array
    {
        return match ($event) {
            'login.success'  => ['label' => 'Login Berhasil', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
            'login.failed'   => ['label' => 'Login Gagal', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
            'logout'         => ['label' => 'Logout', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
            'password.changed' => ['label' => 'Password Diubah', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'password.reset_by_admin' => ['label' => 'Reset Password', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'access.forbidden' => ['label' => 'Akses Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
            default => ['label' => ucwords(str_replace(['.', '_'], ' ', $event)), 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
        };
    }
}