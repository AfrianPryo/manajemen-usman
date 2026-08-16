<?php

namespace App\Providers;

use App\Models\Setting;
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
        if (Schema::hasTable('settings')) {
            // Gunakan Setting::get() langsung agar aman dari urutan Autoload Composer
            View::share('schoolName', Setting::get('school_name', 'SMK Negeri 1 Surabaya'));
            View::share('appName', Setting::get('app_name', 'USMAN'));
            View::share('currencySymbol', Setting::get('currency_symbol', 'Rp'));
            View::share('schoolLogo', Setting::get('school_logo'));
        }
    }
}