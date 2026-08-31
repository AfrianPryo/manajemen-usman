<?php

namespace App\Livewire\Unit\Profile;

use App\Livewire\Master\Settings\Index as MasterSettingsIndex;
use Illuminate\Support\Facades\Auth;
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
 *   card "Informasi Akun" (nama/username/email/status kepegawaian/foto)
 *   dan card baru "Ajukan Reset Password" (lihat canRequestPasswordReset()
 *   di bawah). Ganti nomor WA Admin Unit tetap lewat Master Admin
 *   (Master > Admin); untuk password, Admin Unit sekarang bisa MENGAJUKAN
 *   permintaan reset lewat card baru itu -- bukan mengubah sendiri --
 *   yang lalu perlu di-Approve oleh Admin Master lewat notifikasi
 *   (lihat App\Livewire\NotificationSidebar & App\Livewire\Master\
 *   Notifications\Index).
 *
 * Ketiga flag di atas dijaga juga di sisi server (bukan cuma UI) lewat
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

    /**
     * Tombol "Ajukan Reset Password" HANYA untuk akun yang benar-benar
     * ber-role 'unit-admin'. Sengaja TIDAK cukup hanya mengandalkan
     * isAccountOnlyView() (yang selalu true di class ini) -- middleware
     * 'unit.access' pada route 'unit.profile.index' (lihat routes/web.php)
     * mengizinkan Master Admin membuka halaman ini juga untuk keperluan
     * monitoring unit mana pun, dan Master Admin sama sekali tidak boleh
     * memicu permintaan reset password untuk akunnya sendiri lewat jalur
     * ini. Dicek juga di server lewat guard di
     * Master\Settings\Index::requestPasswordReset(), bukan cuma UI.
     */
    public function canRequestPasswordReset(): bool
    {
        return Auth::user()?->isUnitAdmin() ?? false;
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}