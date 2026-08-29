<?php

namespace App\Http\Middleware;

use App\Models\Unit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard tambahan untuk route yang HANYA relevan untuk kategori unit
 * tertentu (saat ini dipakai untuk fitur "Pesanan Layanan" yang cuma
 * berguna bagi unit berkategori 'jasa').
 *
 * SENGAJA dipisah dari EnsureUnitAccess (bukan ditambahkan ke sana):
 * EnsureUnitAccess murni soal SIAPA yang boleh membuka unit ini (Master
 * Admin vs Unit Admin pemilik), sedangkan middleware ini soal FITUR APA
 * yang boleh dibuka untuk KATEGORI unit ini. Keduanya dipasang berurutan
 * di routes/web.php: 'unit.access' dulu (autentikasi & kepemilikan), baru
 * 'unit.category:jasa' (kesesuaian fitur <-> kategori unit).
 *
 * Pemakaian di route: ->middleware('unit.category:jasa')
 */
class EnsureUnitCategory
{
    public function handle(Request $request, Closure $next, string ...$allowedCategories): Response
    {
        $unit = $request->route('unit');

        // EnsureUnitAccess (dipasang sebelum middleware ini di route group)
        // sudah memastikan $unit ter-resolve jadi model Unit. Fallback ini
        // hanya jaga-jaga kalau urutan middleware suatu saat berubah.
        if (!$unit instanceof Unit) {
            $unit = Unit::where('slug', $unit)->first();
        }

        if (!$unit) {
            abort(404);
        }

        if (!in_array($unit->category, $allowedCategories, true)) {
            abort(403, 'Fitur ini hanya tersedia untuk unit usaha dengan kategori yang sesuai.');
        }

        return $next($request);
    }
}
