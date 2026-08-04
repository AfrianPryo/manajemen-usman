<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = session()->getId();
            $storedSessionId = $user->current_session_id; // 🟢 Gunakan current_session_id

            // Jika session di DB berbeda dengan session browser saat ini, logout paksa
            if ($storedSessionId && $storedSessionId !== $currentSessionId) {
                // 🟢 Catat audit log pengakhiran sesi
                AuthLog::log(
                    'session.terminated',
                    $user->id,
                    $user->username ?? $user->email,
                    'Sesi diakhiri karena login di perangkat/browser lain'
                );

                // 🟢 Logout dan bersihkan payload session browser
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir karena login di perangkat lain.');
            }
        }

        return $next($request);
    }
}