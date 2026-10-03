<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Smart redirect dashboard. Pakai facade Auth::user() (bukan auth()->user())
 * agar method custom (isMasterAdmin/isUnitAdmin/unit) di App\Models\User
 * ter-resolve oleh IDE.
 */
class DashboardRedirectController extends Controller
{
    public function __invoke()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isMasterAdmin()) {
            return redirect()->route('master.dashboard');
        }

        if ($user->isUnitAdmin()) {
            if ($user->unit) {
                return redirect()->route('unit.dashboard', $user->unit->slug);
            }
            abort(403, 'Akun Admin Unit Anda belum terhubung dengan Unit Usaha manapun.');
        }

        return redirect('/');
    }
}
