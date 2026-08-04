<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUnitAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $unit = $request->route('unit');

        if (!$unit) {
            abort(404);
        }

        // Master Admin boleh akses semua unit (monitoring)
        if ($user->isMasterAdmin()) {
            return $next($request);
        }

        // Unit Admin hanya boleh akses unit sendiri
        if ($user->isUnitAdmin()) {
            if ($user->unit_id !== $unit->id) {
                AuthLog::log('access.forbidden', $user->id, $user->email, "Mencoba akses unit: {$unit->slug}");
                abort(403);
            }

            if (!$unit->is_active) {
                AuthLog::log('access.forbidden', $user->id, $user->email, "Unit tidak aktif: {$unit->slug}");
                abort(403, 'Unit sedang nonaktif.');
            }

            return $next($request);
        }

        abort(403);
    }
}