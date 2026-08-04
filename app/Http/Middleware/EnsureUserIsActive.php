<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
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

        return $next($request);
    }
}