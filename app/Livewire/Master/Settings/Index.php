<?php

namespace App\Livewire\Master\Settings;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\FonnteOtpService;
use App\Services\LogArchiveService;
use App\Services\RoutineReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    // 2a. Fitur & Modul — Logo Aplikasi (dipakai di sidebar Master & seluruh Unit)
    public $logo;
    public ?string $existingLogo = null;

    // 2b. Fitur & Modul — Akses Fitur & Otomatisasi
    public bool $allowMultiUnitAdmin = true;
    public string $defaultCategory = 'ritel';

    // 3a. Notifikasi WhatsApp (provider Fonnte dipakai juga untuk OTP di atas)
    public bool $enableWaNotifications = false;
    public string $waProvider = 'fonnte';
    public string $waSenderNumber = '';
    public string $waApiKey = '';

    // 3b. Preferensi Notifikasi per Channel -- kategori WA non-OTP yang
    // boleh dimatikan admin satu per satu, tanpa mematikan OTP (OTP selalu
    // wajib terkirim, tidak ada toggle-nya di sini). Lihat gerbang
    // sesungguhnya di App\Services\FonnteOtpService::channelEnabled().
    public bool $waNotifyCredentials = true;
    public bool $waNotifyAnnouncements = true;

    // 3c. Laporan Rutin Otomatis -- ringkasan aspek penting sistem
    // (keuangan, unit usaha, admin, stok, dst) dikirim berkala ke
    // seluruh Admin Master aktif via WhatsApp. Lihat
    // App\Services\RoutineReportService (isi & jadwal pengiriman) dan
    // App\Console\Commands\SendRoutineReport (pemicu terjadwal, lihat
    // routes/console.php).
    public bool $reportRoutineEnabled = false;
    public string $reportRoutineFrequency = 'daily'; // 'daily' | 'weekly' | 'monthly'
    public string $reportRoutineTime = '07:00';
    public int $reportRoutineDayOfWeek = 1; // 0=Minggu ... 6=Sabtu (dipakai kalau frequency='weekly')
    public int $reportRoutineDayOfMonth = 1; // 1-28 (dipakai kalau frequency='monthly')
    public array $reportRoutineSections = []; // subset key dari RoutineReportService::SECTIONS
    public ?string $reportRoutineLastSentAt = null; // info saja (read-only), diisi service saat kirim

    // 3d. Sesi & Keamanan -- auto-logout karena idle. Lihat
    // App\Http\Middleware\EnsureSessionNotExpired untuk implementasinya.
    public bool $sessionTimeoutEnabled = false;
    public int $sessionTimeoutMasterMinutes = 60;
    public int $sessionTimeoutUnitMinutes = 30;

    // 3e. Retensi & Arsip Log -- berapa hari log login & audit log "hidup"
    // di tabel utama sebelum diarsipkan ke Excel per bulan lalu dihapus dari
    // tabel. Lihat App\Services\LogArchiveService & command `logs:archive`
    // (routes/console.php). Sengaja TIDAK di-type int: input number yang
    // dikosongkan mengirim string '' dan Livewire akan error kalau
    // properti bertipe int; validasi 'integer' di saveFeatures() yang menjaga.
    public $logRetentionDays = LogArchiveService::DEFAULT_RETENTION_DAYS;

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
        $this->existingLogo     = Setting::get('app_logo');
        $this->maintenanceMode  = (bool) Setting::get('maintenance_mode', false);

        $this->defaultCategory     = Setting::get('default_category', 'ritel');
        $this->allowMultiUnitAdmin = (bool) Setting::get('allow_multi_unit_admin', true);

        $this->enableWaNotifications = (bool) Setting::get('enable_wa_notifications', false);
        $this->waProvider            = Setting::get('wa_provider', 'fonnte');
        $this->waSenderNumber        = Setting::get('wa_sender_number', '');
        $this->waApiKey              = Setting::get('wa_api_key', '');

        $this->waNotifyCredentials    = (bool) Setting::get('wa_notify_credentials', true);
        $this->waNotifyAnnouncements  = (bool) Setting::get('wa_notify_announcements', true);

        $this->reportRoutineEnabled      = (bool) Setting::get('report_routine_enabled', false);
        $this->reportRoutineFrequency    = Setting::get('report_routine_frequency', 'daily');
        $this->reportRoutineTime         = Setting::get('report_routine_time', '07:00');
        $this->reportRoutineDayOfWeek    = (int) Setting::get('report_routine_day_of_week', 1);
        $this->reportRoutineDayOfMonth   = (int) Setting::get('report_routine_day_of_month', 1);
        $this->reportRoutineLastSentAt   = Setting::get('report_routine_last_sent_at');

        $storedSections = json_decode(Setting::get('report_routine_sections', ''), true);
        $this->reportRoutineSections = is_array($storedSections) && ! empty($storedSections)
            ? array_values(array_intersect($storedSections, array_keys(RoutineReportService::SECTIONS)))
            : array_keys(RoutineReportService::SECTIONS);

        $this->sessionTimeoutEnabled       = (bool) Setting::get('session_timeout_enabled', false);
        $this->sessionTimeoutMasterMinutes = (int) Setting::get('session_timeout_master_minutes', 60);
        $this->sessionTimeoutUnitMinutes   = (int) Setting::get('session_timeout_unit_minutes', 30);

        $this->logRetentionDays = app(LogArchiveService::class)->retentionDays();
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

    /**
     * Apakah tombol/aksi "Ajukan Reset Password" ditampilkan & boleh dipakai
     * di halaman ini. Master Admin: FALSE (default) -- Master Admin
     * mengubah password sendiri lewat card "Ubah Password" + OTP di atas,
     * tidak perlu mengajukan permintaan ke siapa pun.
     *
     * Override di App\Livewire\Unit\Profile\Index mengembalikan TRUE, tapi
     * HANYA kalau user yang login benar-benar ber-role 'unit-admin' --
     * BUKAN sekadar "halaman ini dibuka lewat prefix /unit/{unit:slug}".
     * Ini penting karena middleware 'unit.access' (lihat routes/web.php)
     * sengaja mengizinkan Master Admin membuka dashboard/menu unit MANA PUN
     * untuk keperluan monitoring -- kalau guard-nya cuma isAccountOnlyView(),
     * Master Admin yang sedang "mengintip" dashboard unit akan ikut melihat
     * & bisa memicu tombol "Ajukan Reset Password" untuk akunnya sendiri,
     * padahal fitur ini memang KHUSUS Admin Unit. Dicek juga di sisi server
     * lewat guard di requestPasswordReset() di bawah, bukan cuma UI.
     */
    public function canRequestPasswordReset(): bool
    {
        return false;
    }

    /**
     * Admin Unit mengajukan permintaan reset password ke Admin Master.
     * BEDA dengan alur "Ubah Password" (requestPasswordChangeOtp) di atas:
     * di sini Admin Unit TIDAK langsung mengubah password sendiri (memang
     * sengaja tidak diberi akses -- lihat App\Livewire\Unit\Profile\Index),
     * melainkan cuma mengirim NOTIFIKASI permintaan ke seluruh Admin Master
     * aktif, lengkap dengan tombol Approve/Reject (lihat
     * App\Notifications\SystemNotification 'actionable' & App\Livewire\
     * NotificationSidebar::approve()/reject() serta App\Livewire\Master\
     * Notifications\Index yang jadi versi "halaman penuh"-nya -- keduanya
     * yang benar-benar mengeksekusi reset password + kirim kredensial baru
     * lewat Fonnte kalau Admin Master menekan Approve).
     */
    public function requestPasswordReset(): void
    {
        if (! $this->canRequestPasswordReset()) {
            abort(403);
        }

        $user = Auth::user();

        // Cooldown sederhana: cegah spam notifikasi ke seluruh Admin Master
        // kalau tombolnya diklik berkali-kali dalam waktu singkat.
        $cooldownKey = "password-reset-request-cooldown:{$user->id}";
        if (Cache::has($cooldownKey)) {
            session()->flash('error', 'Permintaan reset password sudah dikirim. Mohon tunggu beberapa saat sebelum mengajukan lagi.');
            return;
        }

        $masterAdmins = User::role('master-admin')->active()->get();

        if ($masterAdmins->isEmpty()) {
            session()->flash('error', 'Tidak ada Admin Master aktif yang bisa dihubungi. Hubungi pengelola sistem.');
            return;
        }

        $unitLabel = $user->unit?->name ? " ({$user->unit->name})" : '';

        foreach ($masterAdmins as $masterAdmin) {
            $masterAdmin->notify(new SystemNotification(
                title: '🔑 Permintaan Reset Password',
                message: "{$user->name} (username: {$user->username}){$unitLabel} mengajukan permintaan reset password.",
                badge: 'Reset Password',
                actionable: true,
                url: route('master.users.index'),
                extraData: [
                    'type'             => 'password_reset_request',
                    'target_user_id'   => $user->id,
                    'target_user_name' => $user->name,
                ],
            ));
        }

        Cache::put($cooldownKey, true, now()->addMinutes(5));

        AuditLog::record(
            event: 'PASSWORD_RESET_REQUESTED',
            identifier: $user->username,
            description: "Admin unit '{$user->username}' mengajukan permintaan reset password ke Admin Master.",
        );

        session()->flash('success', 'Permintaan reset password telah dikirim ke Admin Master. Anda akan dihubungi setelah permintaan disetujui.');
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

    /**
     * Hapus logo aplikasi yang tersimpan, sidebar Master & Unit otomatis
     * kembali memakai ikon/inisial default (lihat components/layouts/app.blade.php
     * dan unit/app.blade.php).
     */
    public function removeLogo(): void
    {
        if (! $this->canAccessFeaturesTab()) {
            abort(403);
        }

        if ($this->existingLogo && Storage::disk('public')->exists($this->existingLogo)) {
            Storage::disk('public')->delete($this->existingLogo);
        }

        Setting::set('app_logo', null);
        $this->existingLogo = null;

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: Auth::user()->username ?? null,
            description: 'Admin master menghapus logo aplikasi (kembali ke default).',
        );

        session()->flash('success', 'Logo aplikasi berhasil dihapus, sidebar kembali memakai ikon default.');
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
            'logo'    => 'nullable|image|max:2048',

            // Akses Fitur & Otomatisasi
            'defaultCategory' => 'required|in:ritel,jasa',
            'waProvider'      => 'nullable|string|in:fonnte,wablas,twilio,lainnya',
            'waSenderNumber'  => 'required_if:enableWaNotifications,true|nullable|string|max:20',
            'waApiKey'        => 'required_if:enableWaNotifications,true|nullable|string|max:255',

            // Preferensi Notifikasi per Channel
            'waNotifyCredentials'   => 'boolean',
            'waNotifyAnnouncements' => 'boolean',

            // Laporan Rutin Otomatis
            'reportRoutineEnabled'      => 'boolean',
            'reportRoutineFrequency'    => 'required_if:reportRoutineEnabled,true|nullable|in:daily,weekly,monthly',
            'reportRoutineTime'         => 'required_if:reportRoutineEnabled,true|nullable|date_format:H:i',
            'reportRoutineDayOfWeek'    => 'required_if:reportRoutineFrequency,weekly|nullable|integer|min:0|max:6',
            'reportRoutineDayOfMonth'   => 'required_if:reportRoutineFrequency,monthly|nullable|integer|min:1|max:28',
            'reportRoutineSections'     => 'required_if:reportRoutineEnabled,true|array|min:1',
            'reportRoutineSections.*'   => 'string|in:' . implode(',', array_keys(RoutineReportService::SECTIONS)),

            // Sesi & Keamanan
            'sessionTimeoutEnabled'       => 'boolean',
            'sessionTimeoutMasterMinutes' => 'required_if:sessionTimeoutEnabled,true|nullable|integer|min:5|max:1440',
            'sessionTimeoutUnitMinutes'   => 'required_if:sessionTimeoutEnabled,true|nullable|integer|min:5|max:1440',

            // Retensi & Arsip Log
            'logRetentionDays' => 'required|integer|min:' . LogArchiveService::MIN_RETENTION_DAYS . '|max:' . LogArchiveService::MAX_RETENTION_DAYS,
        ], [
            'logRetentionDays.required' => 'Batas retensi log wajib diisi.',
            'logRetentionDays.integer'  => 'Batas retensi log harus berupa angka bulat (hari).',
            'logRetentionDays.min'      => 'Batas retensi log minimal ' . LogArchiveService::MIN_RETENTION_DAYS . ' hari.',
            'logRetentionDays.max'      => 'Batas retensi log maksimal ' . LogArchiveService::MAX_RETENTION_DAYS . ' hari.',
            'reportRoutineSections.required_if' => 'Pilih minimal satu kategori laporan yang ingin dikirim.',
            'reportRoutineSections.min'          => 'Pilih minimal satu kategori laporan yang ingin dikirim.',
        ]);

        $user = Auth::user();

        // Audit log: ambil nilai lama sebelum ditimpa.
        // Catatan: 'wa_api_key' sengaja TIDAK ikut dicatat (data sensitif/kredensial).
        $oldValues = [
            'app_name'                => Setting::get('app_name'),
            'app_logo'                => Setting::get('app_logo'),
            'maintenance_mode'        => (bool) Setting::get('maintenance_mode', false),
            'default_category'       => Setting::get('default_category'),
            'allow_multi_unit_admin' => (bool) Setting::get('allow_multi_unit_admin', true),
            'enable_wa_notifications'=> (bool) Setting::get('enable_wa_notifications', false),
            'wa_provider'            => Setting::get('wa_provider'),
            'wa_sender_number'       => Setting::get('wa_sender_number'),
            'wa_notify_credentials'   => (bool) Setting::get('wa_notify_credentials', true),
            'wa_notify_announcements' => (bool) Setting::get('wa_notify_announcements', true),
            'report_routine_enabled'        => (bool) Setting::get('report_routine_enabled', false),
            'report_routine_frequency'      => Setting::get('report_routine_frequency'),
            'report_routine_time'           => Setting::get('report_routine_time'),
            'report_routine_day_of_week'    => (int) Setting::get('report_routine_day_of_week', 1),
            'report_routine_day_of_month'   => (int) Setting::get('report_routine_day_of_month', 1),
            'report_routine_sections'       => Setting::get('report_routine_sections'),
            'session_timeout_enabled'        => (bool) Setting::get('session_timeout_enabled', false),
            'session_timeout_master_minutes' => (int) Setting::get('session_timeout_master_minutes', 60),
            'session_timeout_unit_minutes'   => (int) Setting::get('session_timeout_unit_minutes', 30),
            'log_retention_days'             => app(LogArchiveService::class)->retentionDays(),
        ];

        // Proses ganti logo (kalau ada file baru diupload). Logo lama
        // dihapus dari disk supaya tidak menumpuk file yatim.
        if ($this->logo) {
            if ($this->existingLogo && Storage::disk('public')->exists($this->existingLogo)) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $this->existingLogo = $this->logo->store('logos', 'public');
            $this->reset('logo');
        }

        // Parameter Aplikasi
        Setting::set('app_name', $this->appName);
        Setting::set('app_logo', $this->existingLogo);
        Setting::set('maintenance_mode', $this->maintenanceMode);

        // Akses Fitur & Otomatisasi
        Setting::set('default_category', $this->defaultCategory);
        Setting::set('allow_multi_unit_admin', $this->allowMultiUnitAdmin);

        Setting::set('enable_wa_notifications', $this->enableWaNotifications);
        Setting::set('wa_provider', $this->waProvider);
        Setting::set('wa_sender_number', $this->waSenderNumber);
        Setting::set('wa_api_key', $this->waApiKey);

        // Preferensi Notifikasi per Channel
        Setting::set('wa_notify_credentials', $this->waNotifyCredentials);
        Setting::set('wa_notify_announcements', $this->waNotifyAnnouncements);

        // Laporan Rutin Otomatis
        Setting::set('report_routine_enabled', $this->reportRoutineEnabled);
        Setting::set('report_routine_frequency', $this->reportRoutineFrequency);
        Setting::set('report_routine_time', $this->reportRoutineTime);
        Setting::set('report_routine_day_of_week', $this->reportRoutineDayOfWeek);
        Setting::set('report_routine_day_of_month', $this->reportRoutineDayOfMonth);
        // Disimpan sebagai JSON string (bukan array PHP mentah) karena
        // Setting::set() men-cast value ke (string) -- array mentah akan
        // rusak jadi literal "Array". Dibaca balik lewat json_decode() di
        // mount() & RoutineReportService::enabledSections().
        Setting::set('report_routine_sections', json_encode(array_values($this->reportRoutineSections)));

        // Sesi & Keamanan
        Setting::set('session_timeout_enabled', $this->sessionTimeoutEnabled);
        Setting::set('session_timeout_master_minutes', $this->sessionTimeoutMasterMinutes ?: 60);
        Setting::set('session_timeout_unit_minutes', $this->sessionTimeoutUnitMinutes ?: 30);

        // Retensi & Arsip Log
        Setting::set('log_retention_days', (int) $this->logRetentionDays);

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: $user->username ?? null,
            description: 'Admin master memperbarui pengaturan Fitur & Modul.',
            oldValues: $oldValues,
            newValues: [
                'app_name'                => $this->appName,
                'app_logo'                => $this->existingLogo,
                'maintenance_mode'        => $this->maintenanceMode,
                'default_category'        => $this->defaultCategory,
                'allow_multi_unit_admin'  => $this->allowMultiUnitAdmin,
                'enable_wa_notifications' => $this->enableWaNotifications,
                'wa_provider'             => $this->waProvider,
                'wa_sender_number'        => $this->waSenderNumber,
                'wa_notify_credentials'   => $this->waNotifyCredentials,
                'wa_notify_announcements' => $this->waNotifyAnnouncements,
                'report_routine_enabled'        => $this->reportRoutineEnabled,
                'report_routine_frequency'      => $this->reportRoutineFrequency,
                'report_routine_time'           => $this->reportRoutineTime,
                'report_routine_day_of_week'    => $this->reportRoutineDayOfWeek,
                'report_routine_day_of_month'   => $this->reportRoutineDayOfMonth,
                'report_routine_sections'       => $this->reportRoutineSections,
                'session_timeout_enabled'        => $this->sessionTimeoutEnabled,
                'session_timeout_master_minutes' => $this->sessionTimeoutMasterMinutes,
                'session_timeout_unit_minutes'   => $this->sessionTimeoutUnitMinutes,
                'log_retention_days'             => (int) $this->logRetentionDays,
            ],
        );

        session()->flash('success', 'Pengaturan fitur & modul berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}