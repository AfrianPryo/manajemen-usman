<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-logout karena TIDAK ADA AKTIVITAS (idle timeout) -- ini BEDA dengan
 * batas umur sesi absolut di config/session.php ('lifetime'), yang tetap
 * jalan seperti biasa dan tidak disentuh middleware ini.
 *
 * Durasi diatur dari menu Pengaturan > Fitur & Modul > Sesi & Keamanan
 * (lihat App\Livewire\Master\Settings\Index::saveFeatures()), dan SENGAJA
 * dibedakan antara Master Admin dan Admin Unit -- Admin Unit biasanya
 * memakai perangkat kasir/shared yang dipakai bergantian, jadi wajar kalau
 * timeout-nya dibuat lebih pendek daripada Master Admin.
 *
 * Cara kerja: setiap request yang lolos middleware ini mencatat waktu
 * aktivitas terakhir ke session ('last_activity_at'). Kalau selisih waktu
 * sejak request TERAKHIR sudah melebihi batas untuk role user tsb, user
 * di-logout paksa (session di-invalidate, bukan cuma redirect ke login).
 *
 * Kalau fitur ini dimatikan lewat setting ('session_timeout_enabled' =
 * false, ini DEFAULT-nya), middleware ini tidak melakukan apa pun selain
 * satu kali baca Setting::get() (yang sudah di-cache forever oleh
 * App\Models\Setting), jadi overhead-nya bisa diabaikan.
 *
 * CATATAN: request Livewire (mis. wire:click, wire:model.live) ikut
 * dihitung sebagai aktivitas -- jadi selama user benar-benar berinteraksi
 * dengan halaman, sesi tidak akan timeout meski tab dibiarkan terbuka lama.
 * wire:poll di suatu komponen (kalau ada) akan ikut menghitung sebagai
 * aktivitas juga, karena middleware ini tidak bisa membedakan polling
 * otomatis dari interaksi asli -- ini keterbatasan yang wajar untuk
 * pendekatan idle-timeout berbasis request, bukan bug.
 */
class EnsureSessionNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if (! (bool) Setting::get('session_timeout_enabled', false)) {
            return $next($request);
        }

        $user = Auth::user();

        $timeoutMinutes = (int) Setting::get(
            $user->isMasterAdmin() ? 'session_timeout_master_minutes' : 'session_timeout_unit_minutes',
            $user->isMasterAdmin() ? 60 : 30
        );

        // Nilai 0/negatif dianggap "tidak valid" -- lewati saja daripada
        // langsung logout semua orang gara-gara setting kosong/salah input.
        if ($timeoutMinutes < 1) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $timeoutMinutes) {
            AuthLog::log(
                'session.timeout',
                $user->id,
                $user->username ?? $user->email,
                "Sesi diakhiri otomatis karena tidak ada aktivitas selama {$timeoutMinutes} menit."
            );

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
        }

        $request->session()->put('last_activity_at', now());

        return $next($request);
    }
}