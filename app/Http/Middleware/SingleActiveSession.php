<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $currentSessionId = session()->getId();
            $storedSessionId = $user->last_login_ip; // Kita gunakan field ini untuk session ID

            // Jika session berbeda, logout paksa
            if ($storedSessionId && $storedSessionId !== $currentSessionId) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Sesi Anda telah berakhir karena login di perangkat lain.']);
            }
        }

        return $next($request);
    }
}