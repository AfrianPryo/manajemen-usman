<?php

namespace App\Http\Middleware;

use App\Models\AuthLog;
use App\Models\Unit;
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

        // Route model binding ({unit:slug}) hanya otomatis mengubah segmen
        // URL jadi instance model Unit kalau komponen/controller tujuan
        // punya parameter atau properti yang ber-type-hint Unit (contoh:
        // App\Livewire\Unit\Dashboard::mount(Unit $unit)). Komponen unit
        // lain (Transaksi, Transaksi Berulang, Inventaris, dll.) TIDAK
        // punya type-hint itu, jadi $unit di sini bisa saja masih berupa
        // string slug mentah ("tefa"), bukan model — dan $unit->id di
        // bawah akan error "Attempt to read property 'id' on string".
        //
        // Supaya middleware ini konsisten bekerja untuk SEMUA route di
        // grup unit/{unit:slug} — apa pun komponennya, dan tanpa perlu
        // mengandalkan tiap komponen baru mengingat menambahkan type-hint
        // Unit — slug di-resolve manual jadi model di sini kalau memang
        // belum ter-resolve otomatis.
        if (!$unit instanceof Unit) {
            $unit = Unit::where('slug', $unit)->first();

            if (!$unit) {
                abort(404);
            }

            // Simpan balik model yang sudah di-resolve ke parameter route,
            // supaya komponen Livewire yang menerima parameter $unit tetap
            // mendapat instance model yang benar (bukan string) juga.
            $request->route()->setParameter('unit', $unit);
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