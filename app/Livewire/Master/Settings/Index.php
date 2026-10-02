<?php

namespace App\Livewire\Master\Settings;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Unit;
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

    // 3a-1. Tes Koneksi Fonnte -- hasil tes terakhir (null = belum pernah
    // dites di sesi form ini). Ditampilkan sebagai banner di bawah tombol
    // "Tes Koneksi". Lihat testWaConnection() & App\Services\FonnteOtpService::testConnection().
    public ?array $waTestResult = null;

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

    /*
    |--------------------------------------------------------------------------
    | 4. LANDING PAGE — konten halaman depan publik (resources/views/landing.blade.php)
    |--------------------------------------------------------------------------
    | Nama & logo aplikasi (tab "Fitur & Modul") otomatis dipakai juga di
    | landing page. Tab ini khusus mengatur judul/deskripsi tiap section
    | serta show/hide-nya per section, supaya admin tidak perlu sentuh kode
    | untuk mengubah copy landing page. Teks bawaan (SELAMA admin belum
    | pernah menyimpan) ada di Setting::LANDING_DEFAULTS -- satu-satunya
    | sumber supaya form ini & tampilan publik selalu konsisten.
    |
    | "Mitra Unit Usaha" (show_units_on_landing) sengaja mempertahankan key
    | lama dari tab "Fitur & Modul" -- hanya pindah tempat tampil di UI --
    | supaya nilai yang sudah tersimpan admin sebelumnya tidak hilang.
    */

    // 4a. Hero (halaman utama) -- judul besar paling atas landing page.
    // Section ini selalu tampil (tidak ada toggle enabled/disabled) karena
    // menjadi halaman pembuka; hanya teksnya yang bisa diubah admin.
    public string $landingHeroTitleTop = '';
    public string $landingHeroTitleBottom = '';
    public string $landingHeroScrollText = '';
    // Jarak vertikal judul atas-bawah hero (satuan rem). Sengaja tanpa tipe
    // (seperti $logRetentionDays): input form mengirim string dan validasi
    // 'numeric' di saveLanding() yang menjaga nilainya.
    public $landingHeroTitleGap = '2';
    // Lebar jarak antar kurung (rem) & pergeseran elemen ASCII (px) -- juga
    // tanpa tipe karena input number mengirim string; divalidasi 'numeric'.
    public $landingHeroBracketWidth = '14';
    public $landingHeroAsciiX = '0';
    public $landingHeroAsciiY = '0';
    // Logo/foto elemen ASCII di tengah judul hero. Selama admin belum
    // mengunggah apa pun, hero tetap memakai model 3D bawaan (hero.glb).
    // Lihat removeHeroLogo() & resources/js/ascii-3d-hero.js (opsi imageUrl).
    public $landingHeroLogo;
    public ?string $existingLandingHeroLogo = null;
    // Logo/ikon di tengah loading screen landing page (di atas counter [0]).
    // Selama admin belum mengunggah, loading screen memakai gambar bawaan
    // (images/LogoLoading.png). Lihat removeHeroLoaderLogo() & landing.blade.php
    // bagian Loading Screen Layer. Nama diawali "landingHero" supaya error
    // validasinya ikut terhitung di section Hero pada navigasi tab.
    public $landingHeroLoaderLogo;
    public ?string $existingLandingHeroLoaderLogo = null;

    public bool $showUnitsOnLanding = true;
    public string $landingMitraTitle = '';
    public string $landingMitraDescription = '';

    // "all"      = semua unit usaha berstatus aktif tampil otomatis (perilaku lama).
    // "selected" = hanya unit dari landingSelectedUnitIds yang tampil, apa pun
    //              status aktifnya, supaya admin bisa mengatur satu per satu
    //              unit mana yang ingin ditonjolkan di landing page publik.
    public string $landingUnitsMode = 'all';
    public array $landingSelectedUnitIds = [];
    /** @var array<int, array{id:int,name:string,is_active:bool}> daftar unit untuk pilihan checkbox di form, diisi di mount() */
    public array $availableUnits = [];

    public bool $landingFiturEnabled = true;
    public string $landingFiturEyebrow = '';
    public string $landingFiturTitle = '';
    /** @var array<int, array{title:string,description:string}> */
    public array $landingFiturItems = [];

    public bool $landingCaraKerjaEnabled = true;
    public string $landingCaraKerjaTitle = '';
    public string $landingCaraKerjaDescription = '';
    /** @var array<int, array{badge:string,icon:string,title:string,description:string}> */
    public array $landingCaraKerjaItems = [];

    public bool $landingTentangEnabled = true;
    public string $landingTentangTitle = '';
    public string $landingTentangDescription = '';
    // 4b. Foto section "Tentang" -- kustomisasi foto sisi kiri section ini.
    // Fallback ke asset bawaan (images/images (1).jpg) SELAMA admin belum
    // pernah mengunggah foto sendiri. Lihat removeTentangPhoto() & blade.
    public $landingTentangPhoto;
    public ?string $existingLandingTentangPhoto = null;

    public bool $landingFaqEnabled = true;
    public string $landingFaqTitle = '';
    /** @var array<int, array{question:string,answer:string}> */
    public array $landingFaqItems = [];

    // Footer selalu tampil (berisi navigasi & kontak inti situs), jadi
    // sengaja tidak diberi toggle enabled -- hanya judul yang bisa diubah.
    public string $landingFooterTitle = '';

    // Ikon garis bawaan yang tersedia untuk tiap kartu "Cara Kerja" --
    // dipetakan ke markup SVG aslinya di resources/views/landing.blade.php
    // ($__caraKerjaIcons). Dipakai sebagai daftar pilihan <select> ikon di
    // form supaya admin tidak perlu (dan tidak bisa) menempel markup SVG
    // bebas dari form.
    public const CARA_KERJA_ICONS = [
        'akun'       => 'Orang / Akun',
        'setup'      => 'Formulir / Setup',
        'unit'       => 'Bangunan / Unit Usaha',
        'transaksi'  => 'Nota / Transaksi',
        'laporan'    => 'Grafik / Laporan',
        'keamanan'   => 'Perisai / Keamanan',
        'notifikasi' => 'Lonceng / Notifikasi',
        'dukungan'   => 'Headset / Dukungan',
        'waktu'      => 'Jam / Real-time',
    ];

    // Batas jumlah butir per daftar dinamis landing page, sekadar menjaga
    // tata letak tetap wajar & form tetap ringan -- bukan batas teknis.
    private const LANDING_LIST_MIN = 1;
    private const LANDING_FITUR_MAX = 8;
    private const LANDING_CARA_KERJA_MAX = 8;
    private const LANDING_FAQ_MAX = 12;

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

        // Tab "Landing Page"
        $defaults = Setting::LANDING_DEFAULTS;

        $this->landingHeroTitleTop    = Setting::get('landing_hero_title_top', $defaults['landing_hero_title_top']);
        $this->landingHeroTitleBottom = Setting::get('landing_hero_title_bottom', $defaults['landing_hero_title_bottom']);
        $this->landingHeroScrollText  = Setting::get('landing_hero_scroll_text', $defaults['landing_hero_scroll_text']);
        $this->landingHeroTitleGap    = Setting::get('landing_hero_title_gap', $defaults['landing_hero_title_gap']);
        $this->landingHeroBracketWidth = Setting::get('landing_hero_bracket_width', $defaults['landing_hero_bracket_width']);
        $this->landingHeroAsciiX      = Setting::get('landing_hero_ascii_x', $defaults['landing_hero_ascii_x']);
        $this->landingHeroAsciiY      = Setting::get('landing_hero_ascii_y', $defaults['landing_hero_ascii_y']);
        $this->existingLandingHeroLogo = Setting::get('landing_hero_logo');
        $this->existingLandingHeroLoaderLogo = Setting::get('landing_loader_logo');

        $this->showUnitsOnLanding     = (bool) Setting::get('show_units_on_landing', true);
        $this->landingMitraTitle       = Setting::get('landing_mitra_title', $defaults['landing_mitra_title']);
        $this->landingMitraDescription = Setting::get('landing_mitra_description', $defaults['landing_mitra_description']);

        $this->landingUnitsMode = Setting::get('landing_units_mode', 'all') === 'selected' ? 'selected' : 'all';
        $storedUnitIds = json_decode(Setting::get('landing_selected_unit_ids', '[]'), true);
        $this->landingSelectedUnitIds = is_array($storedUnitIds) ? array_values(array_map('intval', $storedUnitIds)) : [];
        $this->availableUnits = Unit::orderBy('name')->get(['id', 'name', 'is_active'])
            ->map(fn (Unit $unit) => ['id' => $unit->id, 'name' => $unit->name, 'is_active' => (bool) $unit->is_active])
            ->all();

        $this->landingFiturEnabled = (bool) Setting::get('landing_fitur_enabled', true);
        $this->landingFiturEyebrow = Setting::get('landing_fitur_eyebrow', $defaults['landing_fitur_eyebrow']);
        $this->landingFiturTitle   = Setting::get('landing_fitur_title', $defaults['landing_fitur_title']);
        $this->landingFiturItems   = Setting::getList('landing_fitur_items');

        $this->landingCaraKerjaEnabled    = (bool) Setting::get('landing_cara_kerja_enabled', true);
        $this->landingCaraKerjaTitle       = Setting::get('landing_cara_kerja_title', $defaults['landing_cara_kerja_title']);
        $this->landingCaraKerjaDescription = Setting::get('landing_cara_kerja_description', $defaults['landing_cara_kerja_description']);
        $this->landingCaraKerjaItems       = Setting::getList('landing_cara_kerja_items');

        $this->landingTentangEnabled    = (bool) Setting::get('landing_tentang_enabled', true);
        $this->landingTentangTitle       = Setting::get('landing_tentang_title', $defaults['landing_tentang_title']);
        $this->landingTentangDescription = Setting::get('landing_tentang_description', $defaults['landing_tentang_description']);
        $this->existingLandingTentangPhoto = Setting::get('landing_tentang_photo');

        $this->landingFaqEnabled = (bool) Setting::get('landing_faq_enabled', true);
        $this->landingFaqTitle   = Setting::get('landing_faq_title', $defaults['landing_faq_title']);
        $this->landingFaqItems   = Setting::getList('landing_faq_items');

        $this->landingFooterTitle = Setting::get('landing_footer_title', $defaults['landing_footer_title']);
    }

    /*
    |--------------------------------------------------------------------------
    | LANDING PAGE — daftar dinamis (Fitur Unggulan, Cara Kerja, FAQ)
    |--------------------------------------------------------------------------
    | Tambah/hapus/geser satu butir dalam salah satu daftar landing page.
    | Ketiga daftar berbentuk array of array dengan bentuk berbeda-beda,
    | jadi method tambah/hapus ditulis per-daftar (supaya nilai bawaan
    | tiap butir baru jelas & IDE-friendly), tapi geser urutan dipakaikan
    | satu helper generik lewat reorderLandingList().
    */
    public function addFiturItem(): void
    {
        if (! $this->canAccessLandingTab() || count($this->landingFiturItems) >= self::LANDING_FITUR_MAX) {
            return;
        }

        $this->landingFiturItems[] = ['title' => '', 'description' => ''];
    }

    public function removeFiturItem(int $index): void
    {
        $this->removeLandingListItem($this->landingFiturItems, $index);
    }

    public function moveFiturItem(int $index, string $direction): void
    {
        $this->reorderLandingList($this->landingFiturItems, $index, $direction);
    }

    public function addCaraKerjaItem(): void
    {
        if (! $this->canAccessLandingTab() || count($this->landingCaraKerjaItems) >= self::LANDING_CARA_KERJA_MAX) {
            return;
        }

        $this->landingCaraKerjaItems[] = ['badge' => '', 'icon' => 'unit', 'title' => '', 'description' => ''];
    }

    public function removeCaraKerjaItem(int $index): void
    {
        $this->removeLandingListItem($this->landingCaraKerjaItems, $index);
    }

    public function moveCaraKerjaItem(int $index, string $direction): void
    {
        $this->reorderLandingList($this->landingCaraKerjaItems, $index, $direction);
    }

    public function addFaqItem(): void
    {
        if (! $this->canAccessLandingTab() || count($this->landingFaqItems) >= self::LANDING_FAQ_MAX) {
            return;
        }

        $this->landingFaqItems[] = ['question' => '', 'answer' => ''];
    }

    public function removeFaqItem(int $index): void
    {
        $this->removeLandingListItem($this->landingFaqItems, $index);
    }

    public function moveFaqItem(int $index, string $direction): void
    {
        $this->reorderLandingList($this->landingFaqItems, $index, $direction);
    }

    /**
     * Pindahkan satu butir ke posisi tertentu (drag-and-drop di UI) --
     * pelengkap moveFiturItem()/moveCaraKerjaItem()/moveFaqItem() yang
     * hanya menukar dengan tetangga (dipakai tombol naik/turun).
     */
    public function moveFiturItemTo(int $from, int $to): void
    {
        $this->moveLandingListItem($this->landingFiturItems, $from, $to);
    }

    public function moveCaraKerjaItemTo(int $from, int $to): void
    {
        $this->moveLandingListItem($this->landingCaraKerjaItems, $from, $to);
    }

    public function moveFaqItemTo(int $from, int $to): void
    {
        $this->moveLandingListItem($this->landingFaqItems, $from, $to);
    }

    /**
     * Hapus satu butir dari daftar landing page, minimal menyisakan
     * LANDING_LIST_MIN butir supaya section terkait tidak pernah tampil
     * kosong total di publik selama section-nya masih diaktifkan.
     */
    private function removeLandingListItem(array &$items, int $index): void
    {
        if (! $this->canAccessLandingTab() || count($items) <= self::LANDING_LIST_MIN || ! array_key_exists($index, $items)) {
            return;
        }

        unset($items[$index]);
        $items = array_values($items);
    }

    /**
     * Tukar posisi butir ke-$index dengan tetangganya ('up' = ke atas,
     * selain itu dianggap 'down' = ke bawah). Dipakai untuk mengatur
     * urutan tampil Fitur Unggulan, Cara Kerja, dan FAQ tanpa drag-and-drop.
     */
    private function reorderLandingList(array &$items, int $index, string $direction): void
    {
        if (! $this->canAccessLandingTab() || ! array_key_exists($index, $items)) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! array_key_exists($target, $items)) {
            return;
        }

        [$items[$index], $items[$target]] = [$items[$target], $items[$index]];
    }

    /**
     * Pindahkan satu butir dari posisi $from ke posisi $to (drag-and-drop),
     * menggeser butir-butir di antaranya -- beda dengan reorderLandingList()
     * di atas yang hanya menukar dua butir bertetangga (dipakai tombol naik/turun).
     */
    private function moveLandingListItem(array &$items, int $from, int $to): void
    {
        if (! $this->canAccessLandingTab() || ! array_key_exists($from, $items) || ! array_key_exists($to, $items) || $from === $to) {
            return;
        }

        $item = array_splice($items, $from, 1)[0];
        array_splice($items, $to, 0, [$item]);
    }

    /**
     * Reset hasil tes koneksi sebelumnya begitu admin mengubah API Key,
     * supaya banner hasil tes yang tampil tidak "menyesatkan" (mis. masih
     * menunjukkan hasil sukses dari token lama padahal token di form sudah
     * berubah dan belum tentu valid).
     */
    public function updatedWaApiKey(): void
    {
        $this->waTestResult = null;
    }

    /**
     * Tes Koneksi Fonnte -- dipanggil dari tombol "Tes Koneksi" di card
     * Notifikasi WhatsApp (tab Fitur & Modul). Memakai nilai API Key yang
     * SEDANG diketik di form (belum tentu sudah disimpan lewat
     * saveFeatures()), supaya admin bisa memvalidasi token baru sebelum
     * menyimpannya. Kalau field API Key di form kosong, fallback ke token
     * yang sudah tersimpan di Setting (lihat FonnteOtpService::testConnection()).
     *
     * Tidak mengirim pesan WhatsApp apa pun -- hanya membaca status
     * perangkat lewat endpoint "Device Profile" Fonnte, jadi aman diklik
     * berkali-kali dan TIDAK memicu OTP atau rate limit pengiriman OTP.
     */
    public function testWaConnection(): void
    {
        $this->waTestResult = app(FonnteOtpService::class)->testConnection(
            trim($this->waApiKey) !== '' ? $this->waApiKey : null
        );
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

        if ($tab === 'landing' && ! $this->canAccessLandingTab()) {
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
     * Apakah tab "Landing Page" (konten halaman depan publik) ditampilkan &
     * boleh dipakai di halaman ini. Sama seperti canAccessFeaturesTab() --
     * ini pengaturan GLOBAL, bukan per-unit, jadi mengikuti guard yang
     * sama: Master Admin ya, Admin Unit tidak (lihat override
     * canAccessFeaturesTab() di App\Livewire\Unit\Profile\Index, yang
     * otomatis membuat method ini juga bernilai false di sana).
     */
    public function canAccessLandingTab(): bool
    {
        return $this->canAccessFeaturesTab();
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

    /**
     * Hapus logo/foto custom elemen ASCII di hero landing page, kembali
     * memakai model 3D bawaan -- lihat resources/views/landing.blade.php
     * bagian HERO SECTION.
     */
    public function removeHeroLogo(): void
    {
        if (! $this->canAccessLandingTab()) {
            abort(403);
        }

        if ($this->existingLandingHeroLogo && Storage::disk('public')->exists($this->existingLandingHeroLogo)) {
            Storage::disk('public')->delete($this->existingLandingHeroLogo);
        }

        Setting::set('landing_hero_logo', null);
        $this->existingLandingHeroLogo = null;
        $this->reset('landingHeroLogo');

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: Auth::user()->username ?? null,
            description: 'Admin master menghapus logo/foto ASCII hero di landing page (kembali ke model 3D default).',
        );

        session()->flash('success', 'Logo hero berhasil dihapus, kembali memakai model 3D default.');
    }

    /**
     * Hapus logo/ikon custom loading screen landing page, kembali memakai
     * gambar bawaan (images/LogoLoading.png) -- lihat
     * resources/views/landing.blade.php bagian Loading Screen Layer.
     */
    public function removeHeroLoaderLogo(): void
    {
        if (! $this->canAccessLandingTab()) {
            abort(403);
        }

        if ($this->existingLandingHeroLoaderLogo && Storage::disk('public')->exists($this->existingLandingHeroLoaderLogo)) {
            Storage::disk('public')->delete($this->existingLandingHeroLoaderLogo);
        }

        Setting::set('landing_loader_logo', null);
        $this->existingLandingHeroLoaderLogo = null;
        $this->reset('landingHeroLoaderLogo');

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: Auth::user()->username ?? null,
            description: 'Admin master menghapus logo loading screen landing page (kembali ke default).',
        );

        session()->flash('success', 'Logo loading screen berhasil dihapus, kembali memakai logo default.');
    }

    /**
     * Hapus foto custom section "Tentang" di landing page, kembali memakai
     * foto bawaan (images/images (1).jpg) -- lihat resources/views/landing.blade.php
     * bagian ABOUT SECTION.
     */
    public function removeTentangPhoto(): void
    {
        if (! $this->canAccessLandingTab()) {
            abort(403);
        }

        if ($this->existingLandingTentangPhoto && Storage::disk('public')->exists($this->existingLandingTentangPhoto)) {
            Storage::disk('public')->delete($this->existingLandingTentangPhoto);
        }

        Setting::set('landing_tentang_photo', null);
        $this->existingLandingTentangPhoto = null;

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: Auth::user()->username ?? null,
            description: 'Admin master menghapus foto custom section Tentang di landing page (kembali ke default).',
        );

        session()->flash('success', 'Foto section Tentang berhasil dihapus, kembali memakai foto default.');
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

    /*
    |--------------------------------------------------------------------------
    | LANDING PAGE — simpan judul/deskripsi & show/hide tiap section landing
    | page publik. Lihat blok properti "4. LANDING PAGE" di atas untuk
    | penjelasan tiap field & resources/views/landing.blade.php untuk
    | tempat nilainya dipakai.
    |--------------------------------------------------------------------------
    */
    public function saveLanding(): void
    {
        // Guard sisi server, sama seperti saveFeatures(): pengaturan ini
        // global (bukan per-unit), jadi hanya boleh disimpan lewat tab
        // yang memang boleh diakses (lihat canAccessLandingTab()).
        if (! $this->canAccessLandingTab()) {
            abort(403);
        }

        $this->validate([
            // Hero
            'landingHeroTitleTop'    => 'required|string|max:60',
            'landingHeroTitleBottom' => 'required|string|max:60',
            'landingHeroScrollText'  => 'required|string|max:20',
            'landingHeroTitleGap'    => 'required|numeric|min:0|max:8',
            'landingHeroBracketWidth' => 'required|numeric|min:4|max:40',
            'landingHeroAsciiX'      => 'required|numeric|min:-300|max:300',
            'landingHeroAsciiY'      => 'required|numeric|min:-300|max:300',
            'landingHeroLogo'        => 'nullable|image|max:2048',
            'landingHeroLoaderLogo'  => 'nullable|image|max:2048',

            'showUnitsOnLanding' => 'boolean',
            'landingMitraTitle'       => 'required|string|max:150',
            'landingMitraDescription' => 'required|string|max:500',

            'landingUnitsMode'                => 'in:all,selected',
            'landingSelectedUnitIds'          => 'array',
            'landingSelectedUnitIds.*'        => 'integer|exists:units,id',

            'landingFiturEnabled' => 'boolean',
            'landingFiturEyebrow' => 'required|string|max:30',
            'landingFiturTitle'   => 'required|string|max:150',
            'landingFiturItems'              => 'array|min:'.self::LANDING_LIST_MIN.'|max:'.self::LANDING_FITUR_MAX,
            'landingFiturItems.*.title'       => 'required|string|max:100',
            'landingFiturItems.*.description' => 'required|string|max:400',

            'landingCaraKerjaEnabled'    => 'boolean',
            'landingCaraKerjaTitle'       => 'required|string|max:100',
            'landingCaraKerjaDescription' => 'required|string|max:300',
            'landingCaraKerjaItems'                => 'array|min:'.self::LANDING_LIST_MIN.'|max:'.self::LANDING_CARA_KERJA_MAX,
            'landingCaraKerjaItems.*.badge'         => 'required|string|max:20',
            'landingCaraKerjaItems.*.icon'          => ['required', Rule::in(array_keys(self::CARA_KERJA_ICONS))],
            'landingCaraKerjaItems.*.title'         => 'required|string|max:80',
            'landingCaraKerjaItems.*.description'   => 'required|string|max:250',

            'landingTentangEnabled'    => 'boolean',
            'landingTentangTitle'       => 'required|string|max:150',
            'landingTentangDescription' => 'required|string|max:500',
            'landingTentangPhoto'       => 'nullable|image|max:2048',

            'landingFaqEnabled' => 'boolean',
            'landingFaqTitle'   => 'required|string|max:150',
            'landingFaqItems'            => 'array|min:'.self::LANDING_LIST_MIN.'|max:'.self::LANDING_FAQ_MAX,
            'landingFaqItems.*.question'  => 'required|string|max:200',
            'landingFaqItems.*.answer'    => 'required|string|max:800',

            'landingFooterTitle' => 'required|string|max:100',
        ]);

        $user = Auth::user();

        // Proses ganti logo/foto ASCII hero (kalau ada file baru diupload).
        // File lama dihapus dari disk -- pola sama dengan foto Tentang.
        if ($this->landingHeroLogo) {
            if ($this->existingLandingHeroLogo && Storage::disk('public')->exists($this->existingLandingHeroLogo)) {
                Storage::disk('public')->delete($this->existingLandingHeroLogo);
            }
            $this->existingLandingHeroLogo = $this->landingHeroLogo->store('landing', 'public');
            $this->reset('landingHeroLogo');
        }

        // Proses ganti logo/ikon loading screen (kalau ada file baru diupload).
        // File lama dihapus dari disk -- pola sama dengan logo hero.
        if ($this->landingHeroLoaderLogo) {
            if ($this->existingLandingHeroLoaderLogo && Storage::disk('public')->exists($this->existingLandingHeroLoaderLogo)) {
                Storage::disk('public')->delete($this->existingLandingHeroLoaderLogo);
            }
            $this->existingLandingHeroLoaderLogo = $this->landingHeroLoaderLogo->store('landing', 'public');
            $this->reset('landingHeroLoaderLogo');
        }

        // Proses ganti foto section "Tentang" (kalau ada file baru
        // diupload). Foto lama dihapus dari disk supaya tidak menumpuk
        // file yatim -- pola sama dengan removeLogo()/logo di saveFeatures().
        if ($this->landingTentangPhoto) {
            if ($this->existingLandingTentangPhoto && Storage::disk('public')->exists($this->existingLandingTentangPhoto)) {
                Storage::disk('public')->delete($this->existingLandingTentangPhoto);
            }
            $this->existingLandingTentangPhoto = $this->landingTentangPhoto->store('landing', 'public');
            $this->reset('landingTentangPhoto');
        }

        // Nilai butir daftar (array) disimpan sebagai JSON string -- pola
        // yang sama dipakai 'report_routine_sections' di saveFeatures() --
        // supaya cocok dengan Setting::set() yang men-cast value ke string,
        // dan bisa dibaca balik lewat Setting::getList() di mount() &
        // landing.blade.php. Diproses terpisah dari landingSettingsMap()
        // karena field skalar (string/bool) di sana melewati trim(), yang
        // akan error kalau dipaksakan ke array.
        $listSettings = [
            'landing_fitur_items'      => array_values($this->landingFiturItems),
            'landing_cara_kerja_items' => array_values($this->landingCaraKerjaItems),
            'landing_faq_items'        => array_values($this->landingFaqItems),
        ];
        $selectedUnitIds = array_values(array_unique(array_map('intval', $this->landingSelectedUnitIds)));

        $oldValues = [];
        $newValues = [];
        foreach ($this->landingSettingsMap() as $settingKey => $property) {
            $oldValues[$settingKey] = Setting::get($settingKey);
            $newValues[$settingKey] = is_bool($this->{$property}) ? $this->{$property} : trim($this->{$property});
        }

        $oldValues['landing_units_mode'] = Setting::get('landing_units_mode', 'all');
        $newValues['landing_units_mode'] = $this->landingUnitsMode;

        $oldValues['landing_selected_unit_ids'] = json_decode(Setting::get('landing_selected_unit_ids', '[]'), true) ?: [];
        $newValues['landing_selected_unit_ids'] = $selectedUnitIds;

        // Logo hero -- disimpan terpisah dari landingSettingsMap() dengan
        // alasan yang sama seperti foto Tentang di bawah.
        $oldValues['landing_hero_logo'] = Setting::get('landing_hero_logo');
        $newValues['landing_hero_logo'] = $this->existingLandingHeroLogo;

        // Logo loading screen -- disimpan terpisah dari landingSettingsMap()
        // dengan alasan yang sama seperti logo hero di atas.
        $oldValues['landing_loader_logo'] = Setting::get('landing_loader_logo');
        $newValues['landing_loader_logo'] = $this->existingLandingHeroLoaderLogo;

        // Foto Tentang -- disimpan terpisah dari landingSettingsMap() karena
        // path file bukan properti bertipe string biasa yang bisa di-trim()
        // (properti $landingTentangPhoto adalah objek upload, sedangkan
        // yang disimpan ke Setting adalah $existingLandingTentangPhoto).
        $oldValues['landing_tentang_photo'] = Setting::get('landing_tentang_photo');
        $newValues['landing_tentang_photo'] = $this->existingLandingTentangPhoto;

        foreach ($listSettings as $settingKey => $items) {
            $oldValues[$settingKey] = Setting::getList($settingKey);
            $newValues[$settingKey] = $items;
        }

        foreach ($newValues as $settingKey => $value) {
            Setting::set($settingKey, is_array($value) ? json_encode($value) : $value);
        }

        AuditLog::record(
            event: 'SETTINGS_UPDATED',
            identifier: $user->username ?? null,
            description: 'Admin master memperbarui konten Landing Page.',
            oldValues: $oldValues,
            newValues: $newValues,
        );

        session()->flash('success', 'Konten landing page berhasil diperbarui.');
    }

    /**
     * Peta key Setting <-> nama properti Livewire untuk tab "Landing
     * Page" -- dipakai bersama oleh saveLanding() (simpan + audit log)
     * supaya kedua sisi tidak perlu ditulis berulang dan gampang keliru
     * urutannya kalau ada field baru ditambahkan di kemudian hari.
     *
     * @return array<string, string>
     */
    private function landingSettingsMap(): array
    {
        return [
            'landing_hero_title_top'    => 'landingHeroTitleTop',
            'landing_hero_title_bottom' => 'landingHeroTitleBottom',
            'landing_hero_scroll_text'  => 'landingHeroScrollText',
            'landing_hero_title_gap'    => 'landingHeroTitleGap',
            'landing_hero_bracket_width' => 'landingHeroBracketWidth',
            'landing_hero_ascii_x'      => 'landingHeroAsciiX',
            'landing_hero_ascii_y'      => 'landingHeroAsciiY',

            'show_units_on_landing'          => 'showUnitsOnLanding',
            'landing_mitra_title'             => 'landingMitraTitle',
            'landing_mitra_description'       => 'landingMitraDescription',

            'landing_fitur_enabled'           => 'landingFiturEnabled',
            'landing_fitur_eyebrow'           => 'landingFiturEyebrow',
            'landing_fitur_title'             => 'landingFiturTitle',

            'landing_cara_kerja_enabled'      => 'landingCaraKerjaEnabled',
            'landing_cara_kerja_title'        => 'landingCaraKerjaTitle',
            'landing_cara_kerja_description'  => 'landingCaraKerjaDescription',

            'landing_tentang_enabled'         => 'landingTentangEnabled',
            'landing_tentang_title'           => 'landingTentangTitle',
            'landing_tentang_description'     => 'landingTentangDescription',

            'landing_faq_enabled'             => 'landingFaqEnabled',
            'landing_faq_title'               => 'landingFaqTitle',

            'landing_footer_title'            => 'landingFooterTitle',
        ];
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}