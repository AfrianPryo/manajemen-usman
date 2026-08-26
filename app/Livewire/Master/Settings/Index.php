<?php

namespace App\Livewire\Master\Settings;

use App\Models\Setting;
use App\Services\FonnteOtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
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

    // 1b. Ubah Nomor WhatsApp (2 langkah: request OTP -> verifikasi OTP)
    public string $newPhone = '';
    public string $phoneChangePassword = '';
    public bool $phoneOtpRequested = false;
    public string $phoneOtp = '';

    // 2. Preferensi Sistem
    public string $appName = '';
    public int $sessionTimeout = 120;
    public int $itemsPerPage = 10;
    public string $timezone = 'Asia/Jakarta';
    public string $currencySymbol = 'Rp';
    public bool $maintenanceMode = false;

    // 3. Modul & Fitur
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

        $this->appName         = Setting::get('app_name', 'USMAN - Usaha Mandiri Sekolah');
        $this->currencySymbol  = Setting::get('currency_symbol', 'Rp');
        $this->sessionTimeout  = (int) Setting::get('session_timeout', 120);
        $this->itemsPerPage    = (int) Setting::get('items_per_page', 10);
        $this->timezone        = Setting::get('timezone', 'Asia/Jakarta');
        $this->maintenanceMode = (bool) Setting::get('maintenance_mode', false);

        $this->defaultCategory     = Setting::get('default_category', 'ritel');
        $this->allowMultiUnitAdmin = (bool) Setting::get('allow_multi_unit_admin', true);

        $this->enableWaNotifications = (bool) Setting::get('enable_wa_notifications', false);
        $this->waProvider            = Setting::get('wa_provider', 'fonnte');
        $this->waSenderNumber        = Setting::get('wa_sender_number', '');
        $this->waApiKey              = Setting::get('wa_api_key', '');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
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

        $user->update([
            'name'               => $this->name,
            'username'           => $this->username,
            'email'              => $this->email,
            'employee_status'    => $this->employeeStatus,
            'nip'                => $this->employeeStatus === 'nip' ? $this->nip : null,
            'profile_photo_path' => $this->existingAvatar,
        ]);

        session()->flash('success', 'Profil admin master berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH PASSWORD — Langkah 1: validasi & kirim OTP
    |--------------------------------------------------------------------------
    */
    public function requestPasswordChangeOtp(): void
    {
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

        // TODO: catat ke tabel audit log (old_phone, new_phone, ip, user_id, timestamp)
        // dan idealnya kirim notifikasi WA terakhir ke nomor lama: "Nomor Anda telah diganti."

        $this->reset(['newPhone', 'phoneChangePassword', 'phoneOtp', 'phoneOtpRequested']);

        session()->flash('success', 'Nomor WhatsApp berhasil diperbarui.');

        unset($oldPhone); // dipakai kalau nanti audit log/notifikasi ditambahkan
    }

    public function cancelPhoneOtp(): void
    {
        app(FonnteOtpService::class)->invalidate(Auth::id(), 'phone_change');
        $this->reset(['phoneOtp', 'phoneOtpRequested']);
    }

    public function savePreferences(): void
    {
        $this->validate([
            'appName'        => 'required|string|max:50',
            'sessionTimeout' => 'required|integer|min:5|max:1440',
            'itemsPerPage'   => 'required|integer|in:5,10,25,50,100',
            'timezone'       => 'required|string',
            'currencySymbol' => 'required|string|max:5',
        ]);

        Setting::set('app_name', $this->appName);
        Setting::set('currency_symbol', $this->currencySymbol);
        Setting::set('session_timeout', $this->sessionTimeout);
        Setting::set('items_per_page', $this->itemsPerPage);
        Setting::set('timezone', $this->timezone);
        Setting::set('maintenance_mode', $this->maintenanceMode);

        session()->flash('success', 'Preferensi sistem berhasil diperbarui.');
    }

    public function saveFeatures(): void
    {
        $this->validate([
            'defaultCategory' => 'required|in:ritel,jasa',
            'waProvider'      => 'nullable|string|in:fonnte,wablas,twilio,lainnya',
            'waSenderNumber'  => 'required_if:enableWaNotifications,true|nullable|string|max:20',
            'waApiKey'        => 'required_if:enableWaNotifications,true|nullable|string|max:255',
        ]);

        Setting::set('default_category', $this->defaultCategory);
        Setting::set('allow_multi_unit_admin', $this->allowMultiUnitAdmin);

        Setting::set('enable_wa_notifications', $this->enableWaNotifications);
        Setting::set('wa_provider', $this->waProvider);
        Setting::set('wa_sender_number', $this->waSenderNumber);
        Setting::set('wa_api_key', $this->waApiKey);

        session()->flash('success', 'Pengaturan fitur berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}