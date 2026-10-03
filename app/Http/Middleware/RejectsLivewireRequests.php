<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipakai middleware keamanan (akun aktif, sesi tunggal, idle timeout) yang
 * kini juga dijalankan pada request aksi Livewire (/livewire/update) lewat
 * Livewire::addPersistentMiddleware() di AppServiceProvider.
 *
 * Redirect biasa ke halaman login TIDAK cocok untuk request Livewire (fetch
 * akan mengikuti redirect dan Livewire menampilkan HTML halaman login di
 * dalam modal). Untuk request Livewire dikembalikan 419 sehingga Livewire
 * menawarkan muat ulang halaman -- request halaman penuh berikutnya lalu
 * dialihkan ke login oleh middleware 'auth' seperti biasa.
 */
trait RejectsLivewireRequests
{
    protected function isLivewireRequest(Request $request): bool
    {
        return $request->hasHeader('X-Livewire');
    }

    protected function rejectLivewire(): Response
    {
        return response('', 419);
    }
}
