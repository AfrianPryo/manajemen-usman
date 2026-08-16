<?php

namespace App\Livewire\Master\Settings;

use Livewire\Component;
use App\Models\Setting;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $activeTab = 'profile';

    // 1. Profil Sekolah
    public string $schoolName = '';
    public string $npsn = '';
    public string $principalName = '';
    public string $schoolEmail = '';
    public string $schoolPhone = '';
    public string $schoolAddress = '';
    public $logo;
    public ?string $existingLogo = null;

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

    public function mount(): void
    {
        // Load data dari database (dengan fallback default jika data masih kosong)
        $this->schoolName     = Setting::get('school_name', 'SMK Negeri 1 Surabaya');
        $this->npsn           = Setting::get('school_npsn', '20500001');
        $this->principalName  = Setting::get('school_principal', 'Drs. H. Ahmad Sudrajat, M.Pd.');
        $this->schoolEmail    = Setting::get('school_email', 'info@smkn1surabaya.sch.id');
        $this->schoolPhone    = Setting::get('school_phone', '031-8410234');
        $this->schoolAddress  = Setting::get('school_address', 'Jl. A. Yani No. 2, Surabaya, Jawa Timur');
        $this->existingLogo   = Setting::get('school_logo');

        $this->appName         = Setting::get('app_name', 'USMAN - Usaha Mandiri Sekolah');
        $this->currencySymbol  = Setting::get('currency_symbol', 'Rp');
        $this->sessionTimeout  = (int) Setting::get('session_timeout', 120);
        $this->itemsPerPage    = (int) Setting::get('items_per_page', 10);
        $this->timezone        = Setting::get('timezone', 'Asia/Jakarta');
        $this->maintenanceMode = (bool) Setting::get('maintenance_mode', false);

        $this->defaultCategory    = Setting::get('default_category', 'ritel');
        $this->allowMultiUnitAdmin = (bool) Setting::get('allow_multi_unit_admin', true);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveProfile(): void
    {
        $this->validate([
            'schoolName'    => 'required|string|max:100',
            'npsn'          => 'required|numeric|digits:8',
            'principalName' => 'required|string|max:100',
            'schoolEmail'   => 'required|email|max:100',
            'schoolPhone'   => 'nullable|string|max:20',
            'schoolAddress' => 'nullable|string|max:255',
            'logo'          => 'nullable|image|max:2048',
        ]);

        if ($this->logo) {
            if ($this->existingLogo && Storage::disk('public')->exists($this->existingLogo)) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $this->existingLogo = $this->logo->store('settings', 'public');
            Setting::set('school_logo', $this->existingLogo);
            $this->reset('logo');
        }

        Setting::set('school_name', $this->schoolName);
        Setting::set('school_npsn', $this->npsn);
        Setting::set('school_principal', $this->principalName);
        Setting::set('school_email', $this->schoolEmail);
        Setting::set('school_phone', $this->schoolPhone);
        Setting::set('school_address', $this->schoolAddress);

        session()->flash('success', 'Profil sekolah berhasil disimpan ke database.');
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
        ]);

        Setting::set('default_category', $this->defaultCategory);
        Setting::set('allow_multi_unit_admin', $this->allowMultiUnitAdmin);

        session()->flash('success', 'Pengaturan fitur berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}