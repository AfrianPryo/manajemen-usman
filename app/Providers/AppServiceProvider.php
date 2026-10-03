<?php

namespace App\Providers;

use App\Http\Middleware\EnsureSessionNotExpired;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SingleActiveSession;
use App\Models\Setting;
use App\Support\ValidationMessages;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pesan validasi/error bawaan framework (lang/id/validation.php) tampil
        // dalam bahasa Indonesia, apa pun nilai APP_LOCALE di .env.
        $this->app->setLocale('id');

        // Request aksi Livewire (/livewire/update) hanya menjalankan ulang
        // middleware yang didaftarkan sebagai "persistent". Tanpa ini, cek
        // akun nonaktif, blokir IP/perangkat, sesi tunggal & idle timeout
        // hanya berlaku saat halaman penuh dimuat -- tab yang sudah terbuka
        // tetap bisa menjalankan aksi.
        Livewire::addPersistentMiddleware([
            EnsureUserIsActive::class,
            SingleActiveSession::class,
            EnsureSessionNotExpired::class,
        ]);

        // Pesan error validasi form yang seragam & profesional (Bahasa
        // Indonesia) beserta label field yang ramah pengguna. Dipasang sebagai
        // nilai DEFAULT: pesan/atribut yang ditulis eksplisit di komponen
        // tetap menang (array_merge menaruh milik komponen terakhir), dan rule
        // yang tidak tercantum tetap memakai lang/id/validation.php.
        Validator::resolver(function ($translator, $data, $rules, $messages, $attributes) {
            return new \Illuminate\Validation\Validator(
                $translator,
                $data,
                $rules,
                array_merge(ValidationMessages::messages(), $messages),
                array_merge(ValidationMessages::attributes(), $attributes)
            );
        });

        if ($this->settingsTableExists()) {
            // OPTIMASI: 4 lookup cache terpisah -> 1 panggilan Cache::many
            // lewat Setting::getMany() (nilai & default tetap sama persis).
            // Setting dipakai langsung (bukan helper) agar aman dari urutan
            // Autoload Composer.
            $settings = Setting::getMany([
                'school_name'     => 'SMK Negeri 1 Surabaya',
                'app_name'        => 'USMAN',
                'currency_symbol' => 'Rp',
                'school_logo'     => null,
            ]);

            View::share('schoolName', $settings['school_name']);
            View::share('appName', $settings['app_name']);
            View::share('currencySymbol', $settings['currency_symbol']);
            View::share('schoolLogo', $settings['school_logo']);
        }
    }

    /**
     * Schema::hasTable() adalah query ke information_schema di SETIAP
     * request. Hasil "true" di-cache; hasil "false" tidak pernah di-cache
     * (supaya instalasi baru / sebelum migrate tetap benar), dan di console
     * (artisan migrate, dsb.) selalu dicek langsung. Jika cache sendiri
     * bermasalah (mis. tabel cache belum ada), jatuh ke pengecekan langsung.
     */
    private function settingsTableExists(): bool
    {
        if ($this->app->runningInConsole()) {
            return Schema::hasTable('settings');
        }

        try {
            if (Cache::get('schema:settings_table_exists') === true) {
                return true;
            }

            $exists = Schema::hasTable('settings');

            if ($exists) {
                Cache::forever('schema:settings_table_exists', true);
            }

            return $exists;
        } catch (\Throwable $e) {
            return Schema::hasTable('settings');
        }
    }
}