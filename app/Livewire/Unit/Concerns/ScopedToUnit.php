<?php

namespace App\Livewire\Unit\Concerns;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

/**
 * AKAR MASALAH yang diperbaiki trait ini:
 *
 * Sebelumnya, semua class App\Livewire\Unit\* mengunci scoping datanya
 * lewat `Auth::user()->unit_id` — id unit milik USER YANG SEDANG LOGIN.
 * Ini benar untuk Unit Admin (memang unit_id-nya sendiri), tapi SALAH
 * untuk Master Admin: Master Admin tidak terikat ke unit manapun, jadi
 * `Auth::user()->unit_id` miliknya selalu NULL.
 *
 * Middleware `unit.access` (App\Http\Middleware\EnsureUnitAccess) memang
 * SENGAJA mengizinkan Master Admin membuka dashboard/menu unit MANA PUN
 * untuk keperluan monitoring — tapi begitu halaman dibuka, kode di
 * dalamnya salah mengunci ke unit_id Master Admin sendiri (null),
 * bukan ke unit yang SEDANG DIBUKA. Akibatnya (dua bug yang sama-sama
 * dilaporkan dari satu akar ini):
 *   1. Data tidak ter-filter sama sekali (query `where('unit_id', null)`
 *      pada beberapa relasi berperilaku seperti tanpa filter), sehingga
 *      data unit lain ikut tampil.
 *   2. Dropdown/daftar unit yang dihitung dari `Unit::where('id', null)`
 *      jadi KOSONG total (elemen <select> tampil tanpa isi/opsi).
 *
 * PERBAIKAN: seluruh scoping memakai unit yang terikat ke ROUTE yang
 * sedang dibuka (`request()->route('unit')`, yang sudah pasti berupa
 * instance model Unit, bukan string slug mentah — lihat komentar di
 * EnsureUnitAccess::handle()), BUKAN unit milik user login. Ini otomatis
 * benar untuk KEDUA kasus:
 *   - Unit Admin: middleware unit.access sudah memastikan slug di URL
 *     memang miliknya sendiri (kalau bukan, sudah 403 sebelum sampai
 *     sini) — jadi "unit di route" = "unit miliknya sendiri", selalu.
 *   - Master Admin memantau: "unit di route" = unit yang sedang dia buka,
 *     persis yang diharapkan.
 *
 * `Auth::user()->unit_id` HANYA dipakai sebagai fallback kalau karena
 * suatu sebab parameter route belum ter-resolve (mis. dipanggil dari
 * luar konteks route, seperti command/test).
 *
 * CATATAN REVISI: resolve dari route ini SEKARANG hanya dilakukan SEKALI,
 * di request pertama (full page load), lalu hasilnya dikunci ke properti
 * publik `$lockedUnitId` yang otomatis dipertahankan Livewire di setiap
 * request berikutnya. Lihat komentar "BUGFIX LANJUTAN" di bawah untuk
 * alasannya — resolve ULANG dari route di setiap request action Livewire
 * (bukan cuma sekali di awal) ternyata bisa gagal dan jatuh ke fallback
 * `Auth::user()->unit_id` (null untuk Master Admin) di tengah pemakaian,
 * padahal fallback ini seharusnya cabang yang TIDAK PERNAH tereksekusi
 * dalam pemakaian normal lewat browser.
 */
trait ScopedToUnit
{
    /**
     * BUGFIX LANJUTAN (dilaporkan: Master Admin membuka modul Transaksi/
     * Inventaris via monitoring unit tertentu -> data awal SUDAH benar
     * ter-scope, tapi begitu memakai modul-nya -- buka modal "Tambah",
     * simpan data, dsb -- dropdown Unit Usaha mendadak KOSONG dan tabel
     * mendadak menampilkan data LINTAS UNIT lagi).
     *
     * AKAR MASALAHNYA: currentUnitId() sebelumnya resolve
     * `request()->route('unit')` ULANG di SETIAP request Livewire --
     * termasuk request AJAX untuk satu action (openCreateModal,
     * saveProduct, saveTransaction, dst), bukan cuma saat halaman pertama
     * kali dibuka penuh. Resolve ulang seperti ini tidak selalu konsisten
     * berhasil di request action (berbeda dengan full page load yang pasti
     * melewati middleware `unit.access` secara utuh) -- begitu gagal,
     * currentUnit() jadi null, lalu fallback ke Auth::user()->unit_id yang
     * SELALU null untuk Master Admin. Efeknya dua bug yang dilaporkan
     * sekaligus: dropdown Unit::where('id', null) jadi kosong, DAN
     * lockUnitScope() di Unit\Inventory\Index / Unit\Transactions\Index
     * ikut menimpa $this->unitFilter jadi null pada request itu -- yang
     * artinya tabel kehilangan filter unit-nya dan balik menampilkan
     * semua unit.
     *
     * PERBAIKAN: resolve `request()->route('unit')` HANYA SEKALI, di
     * request PALING AWAL saat komponen ini pertama kali dibuka (full page
     * load, di mana middleware `unit.access` PASTI sudah jalan dan model
     * Unit PASTI sudah ter-bind ke route). Hasilnya disimpan ke properti
     * PUBLIK `$lockedUnitId` -- karena properti publik otomatis
     * dipertahankan Livewire di setiap request berikutnya (snapshot),
     * seluruh action call sesudahnya (klik tombol, submit form, dsb)
     * TIDAK PERLU resolve ulang dari route sama sekali, jadi tidak lagi
     * rentan terhadap kegagalan resolve di tengah jalan.
     *
     * `bootScopedToUnit()` adalah lifecycle hook bawaan Livewire: method
     * `boot{NamaTrait}()` otomatis dipanggil Livewire di SETIAP request
     * (baik full page load maupun action call), sebelum method lain di
     * komponen dijalankan -- jadi guard `=== null` di bawah memastikan
     * resolve dari route betul-betul cuma terjadi SEKALI (di request
     * pertama), dan di request-request berikutnya nilai yang sudah
     * tersimpan langsung dipakai.
     */
    public ?int $lockedUnitId = null;

    public function bootScopedToUnit(): void
    {
        if ($this->lockedUnitId === null) {
            $routeUnit = request()->route('unit');
            $this->lockedUnitId = $routeUnit instanceof Unit
                ? $routeUnit->id
                : Auth::user()?->unit_id;
        }
    }

    /**
     * Id unit yang sedang aktif untuk halaman ini. WAJIB dipakai untuk
     * setiap query/filter/penguncian input yang berhubungan dengan
     * "unit mana yang sedang dilihat/dikelola" — jangan lagi memakai
     * Auth::user()->unit_id langsung di class yang memakai trait ini.
     */
    protected function currentUnitId(): ?int
    {
        return $this->lockedUnitId;
    }

    /**
     * Instance model Unit yang sedang aktif untuk halaman ini (kalau ada).
     */
    protected function currentUnit(): ?Unit
    {
        return $this->lockedUnitId ? Unit::find($this->lockedUnitId) : null;
    }
}