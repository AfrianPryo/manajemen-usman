<?php

namespace App\Livewire\Master\Settings;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\FonnteOtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    use WithFileUploads;

    public string $activeTab = 'profile';

    // 1. Profil Admin Master (data akun yang sedang login, tabel users)
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = ''; // read-only display; perubahan lewat alur OTP terpisah
    public string $employeeStatus = 'non-nip'; // 'nip' | 'non-nip'
    public string $nip = '';
    public $avatar;
    public ?string $existingAvatar = null;

    // 1a. Ubah Password (2 langkah: request OTP -> verifikasi OTP)
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPassword_confirmation = '';
    public bool $passwordOtpRequested = false;
    public string $passwordOtp = '';

    // 1b. Ubah Nomor WhatsApp (2 langkah: request OTP -> ver===ifikasi OTP)
    public string $newPhone = '';
    public string $phoneChangePassword = '';
    public bool $phoneOtpRequested = false;
    public string $phoneOtp = '';

    // 2. Fitur & Modul — Parameter Aplikasi
    public string $appName = '';
    public bool $maintenanceMode = false;

    // 2a. Fitur & Modul — Akses Fitur & Otomatisasi
    public bool $allowMultiUnitAdmin = true;
    public string $defaultCategory = 'ritel';

    // 3a. Notifikasi WhatsApp (provider Fonnte dipakai juga untuk OTP di atas)
    public bool $enableWaNotifications = false;
    public string $waProvider = 'fonnte';
    public string $waSenderNumber = '';
    public string $waApiKey = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name           = $user->name ?? '';
        $this->username       = $user->username ?? '';
        $this->email          = $user->email ?? '';
        $this->phone          = $user->phone ?? '';
        $this->employeeStatus = $user->employee_status ?? 'non-nip';
        $this->nip            = $user->nip ?? '';
        $this->existingAvatar = $user->profile_photo_path;

        $this->appName          = Setting::get('app_name', 'USMAN - Usaha Mandiri Sekolah');
        $this->maintenanceMode  = (bool) Setting::get('maintenance_mode', false);

        $this->defaultCategory     = Setting::get('default_category', 'ritel');
        $this->allowMultiUnitAdmin = (bool) Setting::get('allow_multi_unit_admin', true);

        $this->enableWaNotifications = (bool) Setting::get('enable_wa_notifications', false);
        $this->waProvider            = Setting::get('wa_provider', 'fonnte');
        $this->waSenderNumber        = Setting::get('wa_sender_number', '');
        $this->waApiKey              = Setting::get('wa_api_key', '');
    }

    public function setTab(string $tab): void
    {
        // Tab "Fitur & Modul" berisi pengaturan aplikasi yang bersifat
        // GLOBAL (mode maintenance, kategori default, kredensial WA/OTP,
        // dst -- lihat App\Models\Setting yang memang bukan per-unit).
        // Hanya boleh diakses kalau canAccessFeaturesTab() true (default:
        // ya, untuk Master Admin). Override di App\Livewire\Unit\Profile\Index
        // mengembalikan false supaya Admin Unit tidak bisa membuka tab ini
        // sama sekali, termasuk lewat manipulasi wire:click di client.
        if ($tab === 'features' && ! $this->canAccessFeaturesTab()) {
            return;
        }

        $this->activeTab = $tab;
    }

    /**
     * Apakah tab "Fitur & Modul" (pengaturan aplikasi global) ditampilkan
     * & boleh dipakai di halaman ini. Master Admin: ya (default). Unit
     * Admin: TIDAK -- lihat override di App\Livewire\Unit\Profile\Index,
     * karena tab ini mengubah setting lintas-sistem yang bukan wilayah
     * Admin Unit, bukan sekadar soal UI (saveFeatures() juga dijaga di
     * sisi server lewat method ini).
     */
    public function canAccessFeaturesTab(): bool
    {
        return true;
    }

    /**
     * Apakah halaman ini dirender dalam mode "hanya akun" -- cuma
     * menampilkan card Informasi Akun (nama/username/email/status
     * kepegawaian/foto), TANPA nav tab, tanpa heading "Pengaturan Sistem",
     * dan tanpa card "Ubah Nomor WhatsApp" / "Ubah Password". Master
     * Admin: false (default, tampilan lengkap seperti biasa). Unit Admin:
     * TRUE -- lihat override di App\Livewire\Unit\Profile\Index, karena
     * halaman "Profil Saya" milik Unit memang sengaja dibatasi hanya
     * untuk melihat/mengubah data identitas akun saja; ganti nomor WA dan
     * ganti password Admin Unit tetap lewat Master Admin (Master > Admin),
     * bukan mandiri dari halaman ini.
     */
    public function isAccountOnlyView(): bool
    {
        return false;
    }

    public function saveProfile(): void
    {
        $user = Auth::user();

        // Catatan: 'phone' sengaja TIDAK divalidasi/diupdate di sini.
        // Perubahan nomor WA punya alur sendiri (requestPhoneChangeOtp / verifyPhoneChangeOtp)
        // karena nomor ini dipakai sebagai kanal OTP keamanan.
        $this->validate([
            'name'           => 'required|string|max:100',
            'username'       => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'          => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'employeeStatus' => 'required|in:nip,non-nip',
            'nip'            => 'required_if:employeeStatus,nip|nullable|string|max:30',
            'avatar'         => 'nullable|image|max:2048',
        ]);

        if ($this->avatar) {
            if ($this->existingAvatar && Storage::disk('public')->exists($this->existingAvatar)) {
                Storage::disk('public')->delete($this->existingAvatar);
            }
            $this->existingAvatar = $this->avatar->store('avatars', 'public');
            $this->reset('avatar');
        }

        // Audit log: simpan nilai lama sebelum ditimpa
        $oldValues = $user->only(['name', 'username', 'email', 'employee_status', 'nip']);

        $user->update([
            'name'               => $this->name,
            'username'           => $this->username,
            'email'              => $this->email,
            'employee_status'    => $this->employeeStatus,
            'nip'                => $this->employeeStatus === 'nip' ? $this->nip : null,
            'profile_photo_path' => $this->existingAvatar,
        ]);

        AuditLog::record(
            event: 'PROFILE_UPDATED',
            identifier: $user->username,
            description: 'Admin master memperbarui data profil (nama/username/email/status kepegawaian).',
            oldValues: $oldValues,
            newValues: $user->only(['name', 'username', 'email', 'employee_status', 'nip']),
        );

        session()->flash('success', 'Profil admin master berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH PASSWORD — Langkah 1: validasi & kirim OTP
    |--------------------------------------------------------------------------
    */
    public function requestPasswordChangeOtp(): void
    {
        // Guard sisi server: sinkron dengan card "Ubah Password" yang
        // disembunyikan di UI saat isAccountOnlyView() true (lihat blade).
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        $user = Auth::user();

        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword'     => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        if (empty($user->phone)) {
            $this->addError('currentPassword', 'Nomor WhatsApp belum terdaftar. Hubungi admin untuk verifikasi manual.');
            return;
        }

        $result = app(FonnteOtpService::class)->generateAndSend($user->id, 'password_change', $user->phone);

        if (! $result['success']) {
            $this->addError('currentPassword', $result['message']);
            return;
        }

        $this->passwordOtpRequested = true;
        session()->flash('success', $result['message']);
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH PASSWORD — Langkah 2: verifikasi OTP & eksekusi perubahan
    |--------------------------------------------------------------------------
    */
    public function verifyPasswordChangeOtp(): void
    {
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        $this->validate(['passwordOtp' => 'required|digits:6']);

        $user   = Auth::user();
        $result = app(FonnteOtpService::class)->verify($user->id, 'password_change', $this->passwordOtp);

        if (! $result['success']) {
            $this->addError('passwordOtp', $result['message']);
            return;
        }

        $user->update([
            'password' => $this->newPassword, // otomatis di-hash lewat cast 'hashed' pada model User
        ]);

        // Audit log: catat aktivitas ubah password (nilai password itu sendiri TIDAK dicatat)
        AuditLog::record(
            event: 'PASSWORD_CHANGED',
            identifier: $user->username,
            description: 'Admin master berhasil mengubah password melalui verifikasi OTP WhatsApp.',
        );

        // Logout semua sesi lain (device/browser lain) demi keamanan
        Auth::logoutOtherDevices($this->newPassword);

        $this->reset([
            'currentPassword', 'newPassword', 'newPassword_confirmation',
            'passwordOtp', 'passwordOtpRequested',
        ]);

        session()->flash('success', 'Password berhasil diperbarui. Sesi di perangkat lain telah otomatis keluar.');
    }

    public function cancelPasswordOtp(): void
    {
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        app(FonnteOtpService::class)->invalidate(Auth::id(), 'password_change');
        $this->reset(['passwordOtp', 'passwordOtpRequested']);
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH NOMOR WA — Langkah 1: validasi & kirim OTP
    |--------------------------------------------------------------------------
    | OTP dikirim ke NOMOR LAMA (jika ada) sebagai konfirmasi pemilik akun asli.
    | Kalau belum pernah punya nomor terdaftar, OTP dikirim ke nomor baru untuk
    | verifikasi kepemilikan nomor tersebut.
    */
    public function requestPhoneChangeOtp(): void
    {
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        $user = Auth::user();

        $this->validate([
            'newPhone'            => ['required', 'string', 'max:20', 'regex:/^[0-9+]+$/', 'different:phone'],
            'phoneChangePassword' => ['required', 'current_password'],
        ]);

        $targetForOtp = $user->phone ?: $this->newPhone;

        $result = app(FonnteOtpService::class)->generateAndSend($user->id, 'phone_change', $targetForOtp);

        if (! $result['success']) {
            $this->addError('newPhone', $result['message']);
            return;
        }

        $this->phoneOtpRequested = true;

        $info = $user->phone
            ? $result['message'] . ' Kode dikirim ke nomor LAMA untuk konfirmasi.'
            : $result['message'] . ' Kode dikirim ke nomor BARU untuk verifikasi kepemilikan.';

        session()->flash('success', $info);
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH NOMOR WA — Langkah 2: verifikasi OTP & eksekusi perubahan
    |--------------------------------------------------------------------------
    */
    public function verifyPhoneChangeOtp(): void
    {
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        $this->validate(['phoneOtp' => 'required|digits:6']);

        $user   = Auth::user();
        $result = app(FonnteOtpService::class)->verify($user->id, 'phone_change', $this->phoneOtp);

        if (! $result['success']) {
            $this->addError('phoneOtp', $result['message']);
            return;
        }

        $oldPhone = $user->phone;

        $user->update(['phone' => $this->newPhone]);
        $this->phone = $this->newPhone;

        // Audit log: catat perubahan nomor WhatsApp
        AuditLog::record(
            event: 'PHONE_CHANGED',
            identifier: $user->username,
            description: 'Admin master mengubah nomor WhatsApp terdaftar melalui verifikasi OTP.',
            oldValues: ['phone' => $oldPhone],
            newValues: ['phone' => $this->newPhone],
        );

        // TODO: idealnya kirim notifikasi WA terakhir ke nomor lama: "Nomor Anda telah diganti."

        $this->reset(['newPhone', 'phoneChangePassword', 'phoneOtp', 'phoneOtpRequested']);

        session()->flash('success', 'Nomor WhatsApp berhasil diperbarui.');
    }

    public function cancelPhoneOtp(): void
    {
        if ($this->isAccountOnlyView()) {
            abort(403);
        }

        app(FonnteOtpService::class)->invalidate(Auth::id(), 'phone_change');
        $this->reset(['phoneOtp', 'phoneOtpRequested']);
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR & MODUL — gabungan Parameter Aplikasi (dulu tab "Preferensi Sistem")
    | dan Akses Fitur & Otomatisasi (dulu tab "Fitur & Modul") dalam satu tab
    | dan satu aksi simpan.
    |--------------------------------------------------------------------------
    */
    public function saveFeatures(): void
    {
        // Guard sisi server: jangan sampai aksi ini tetap bisa dipanggil
        // (mis. lewat request wire:submit yang dimanipulasi) oleh role
        // yang tabnya sudah disembunyikan di UI. Lihat canAccessFeaturesTab().
        if (! $this->canAccessFeaturesTab()) {
            abort(403);
        }

        $this->validate([
            // Parameter Aplikasi
            'appName' => 'required|string|max:50',

            // Akses Fitur & Otomatisasi
            'defaultCategory' => 'required|in:ritel,jasa',
            'waProvider'      => 'nullable|string|in:fonnte,wablas,twilio,lainnya',
            'waSenderNumber'  => 'required_if:enableWaNotifications,true|nullable|string|max:20',
            'waApiKey'        => 'required_if:enableWaNotifications,true|nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Audit log: ambil nilai lama sebelum ditimpa.
        // Catatan: 'wa_api_key' sengaja TIDAK ikut dicatat (data sensitif/kredensial).
        $oldValues = [
            'app_name'                => Setting::get('app_name'),
            'maintenance_mode'        => (bool) Setting::get('maintenance_mode', false),
            'default_category'       => Setting::get('default_category'),
            'allow_multi_unit_admin' => (bool) Setting::get('allow_multi_unit_admin', true),
            'enable_wa_notifications'=> (bool) Setting::get('enable_wa_notifications', false),
            'wa_provider'            => Setting::get('wa_provider'),
            'wa_sender_number'       => Setting::get('wa_sender_number'),
        ];

        // Parameter Aplikasi
        Setting::set('app_name', $this->appName);
        Setting::set('maintenance_mode', $this->maintenanceMode);

        // Akses Fitur & Otomatisasi
        Setting::set('default_category', $this->defaultCategory);
        Setting::set('allow_multi_unit_admin', $this->allowMultiUnitAdmin);

        Setting::set('enable_wa_notifications', $this->enableWaNotifications);
        Setting::set('wa_provider', $this->waProvider);
        Setting::set('wa_sender_number', $this->waSenderNumber);
        Setting::set('wa_api_key', $this->waApiKey);

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: $user->username ?? null,
            description: 'Admin master memperbarui pengaturan Fitur & Modul.',
            oldValues: $oldValues,
            newValues: [
                'app_name'                => $this->appName,
                'maintenance_mode'        => $this->maintenanceMode,
                'default_category'        => $this->defaultCategory,
                'allow_multi_unit_admin'  => $this->allowMultiUnitAdmin,
                'enable_wa_notifications' => $this->enableWaNotifications,
                'wa_provider'             => $this->waProvider,
                'wa_sender_number'        => $this->waSenderNumber,
            ],
        );

        session()->flash('success', 'Pengaturan fitur & modul berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}