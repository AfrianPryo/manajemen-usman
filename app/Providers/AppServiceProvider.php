<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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