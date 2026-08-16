<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai pengaturan berdasarkan key (dengan Cache)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $item = static::find($key);
            return $item !== null ? $item->value : $default;
        });
    }

    /**
     * Simpan atau perbarui nilai pengaturan
     */
    public static function set(string $key, mixed $value): void
    {
        // Konversi boolean ke string jika diperlukan
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("setting_{$key}");
    }
}