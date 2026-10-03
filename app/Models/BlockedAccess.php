<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Daftar blokir akses (IP/perangkat) yang dikelola Master Admin dari menu
 * "Keamanan" (lihat App\Livewire\Master\Activities\Index). Lihat komentar
 * lengkap pada migrasi `create_blocked_accesses_table` untuk alasan
 * desain (kenapa baris dihapus saat dibuka blokirnya, bukan di-flag).
 */
class BlockedAccess extends Model
{
    public const TYPE_IP = 'ip';
    public const TYPE_DEVICE = 'device';

    protected $fillable = [
        'type',
        'value',
        'reason',
        'blocked_by',
    ];

    /**
     * Cache hasil cek blokir (dipanggil di SETIAP request pengguna login,
     * jadi sebelumnya = 2 query/request). TTL pendek sebagai pengaman bila
     * invalidasi terlewat; invalidasi utama lewat flushBlockedCache() &
     * event model di bawah.
     */
    protected const CACHE_TTL_SECONDS = 600;

    protected static function booted(): void
    {
        static::saved(fn (self $m) => static::flushBlockedCache($m->type, $m->value));
        static::deleted(fn (self $m) => static::flushBlockedCache($m->type, $m->value));
    }

    protected static function cacheKeyFor(string $type, string $value): string
    {
        return 'blocked_access:' . $type . ':' . md5($value);
    }

    /**
     * Hapus cache status blokir untuk satu IP/perangkat. Dipanggil dari
     * App\Livewire\Master\Activities\Index saat blokir dibuat/dibuka.
     */
    public static function flushBlockedCache(string $type, string $value): void
    {
        Cache::forget(static::cacheKeyFor($type, $value));
    }

    protected static function isBlocked(string $type, string $value): bool
    {
        return (bool) Cache::remember(
            static::cacheKeyFor($type, $value),
            static::CACHE_TTL_SECONDS,
            fn () => static::query()->where('type', $type)->where('value', $value)->exists()
        );
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * True kalau alamat IP yang diberikan sedang diblokir. Dipakai saat
     * login (App\Livewire\Auth\Login) maupun pada tiap request pengguna
     * yang sudah login (App\Http\Middleware\EnsureUserIsActive).
     */
    public static function isIpBlocked(?string $ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        return static::isBlocked(self::TYPE_IP, $ip);
    }

    /**
     * True kalau string User-Agent perangkat yang diberikan sedang
     * diblokir. User-Agent dipakai sebagai identitas "perangkat" karena
     * aplikasi ini tidak punya mekanisme device-fingerprinting terpisah.
     */
    public static function isDeviceBlocked(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        return static::isBlocked(self::TYPE_DEVICE, $userAgent);
    }
}
