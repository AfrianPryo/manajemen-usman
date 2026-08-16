<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Helper global untuk mengakses nilai pengaturan dari mana saja (Blade/Controller)
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}