<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

        return static::query()->where('type', self::TYPE_IP)->where('value', $ip)->exists();
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

        return static::query()->where('type', self::TYPE_DEVICE)->where('value', $userAgent)->exists();
    }
}
