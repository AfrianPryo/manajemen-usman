<?php

namespace App\Livewire\Unit\Profile;

use App\Livewire\Master\Settings\Index as MasterSettingsIndex;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Profil Saya".
 *
 * Sebelumnya class ini mewarisi Master\Profile\Index (halaman profil
 * berdiri sendiri). Komponen tersebut sudah dihapus karena duplikat dengan
 * tab "Profil Admin" di dalam Settings (App\Livewire\Master\Settings\Index)
 * -- satu-satunya halaman profil admin master yang dipakai sekarang.
 *
 * Class ini mewarisi Master\Settings\Index apa adanya (logic profil, ganti
 * password, ganti nomor WA -- semuanya lewat OTP -- tidak perlu ditulis
 * ulang) dan memakai view yang sama persis (livewire.master.settings.index).
 * Tapi TIDAK seluruh bagiannya ditampilkan untuk Unit Admin:
 * - Tab "Fitur & Modul" (pengaturan aplikasi GLOBAL -- mode maintenance,
 *   kategori default, kredensial WhatsApp/OTP, dst -- lihat
 *   App\Models\Setting yang memang bukan per-unit) -- disembunyikan lewat
 *   canAccessFeaturesTab() => false.
 * - Nav tab, heading "Pengaturan Sistem", card "Ubah Nomor WhatsApp", dan
 *   card "Ubah Password" -- disembunyikan lewat isAccountOnlyView() =>
 *   true, sehingga halaman "Profil Saya" milik Unit HANYA menyisakan
 *   card "Informasi Akun" (nama/username/email/status kepegawaian/foto).
 *   Ganti nomor WA & password Admin Unit tetap lewat Master Admin
 *   (Master > Admin), bukan mandiri dari halaman ini.
 *
 * Kedua flag di atas dijaga juga di sisi server (bukan cuma UI) lewat
 * guard di masing-masing method aksi pada class induk.
 *
 * Class ini sendiri hanya membungkus ulang supaya folder & nama route-nya
 * tetap konsisten dengan pola Unit\* lainnya (unit.profile.index), dan
 * supaya halaman ini dirender dengan layout sidebar Unit, bukan Master.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterSettingsIndex
{
    public function canAccessFeaturesTab(): bool
    {
        return false;
    }

    public function isAccountOnlyView(): bool
    {
        return true;
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}