<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use App\Models\BlockedAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && !$user->is_active) {
            Auth::logout();
            AuthLog::log('login.failed', null, $user->email, 'Akun nonaktif');

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun tidak aktif. Silakan hubungi Master Admin.']);
        }

        // 🔒 Menu Keamanan (Master Admin): tolak permintaan dari IP atau
        // perangkat yang sedang diblokir, meski sesi login-nya masih ada.
        // Ini jaring pengaman untuk kasus IP/perangkat baru diblokir SAAT
        // pengguna masih dalam sesi aktif -- pengecekan saat login sendiri
        // ada di App\Livewire\Auth\Login::login(). Dicek di sini (bukan
        // middleware terpisah) supaya tidak perlu registrasi alias baru
        // di bootstrap/app.php; middleware 'user.active' ini sudah
        // terpasang di seluruh route terautentikasi (lihat routes/web.php).
        if ($user && (BlockedAccess::isIpBlocked($request->ip()) || BlockedAccess::isDeviceBlocked($request->userAgent()))) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuthLog::log('login.failed', $user->id, $user->email, 'Akses ditolak: IP/perangkat diblokir Master Admin');

            return redirect()->route('login')
                ->withErrors(['email' => 'Akses dari perangkat/IP ini telah diblokir oleh Master Admin.']);
        }

        return $next($request);
    }
}