<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    use HasFactory;

    // 🟢 Matikan penangan updated_at karena migrasi hanya menyediakan created_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'event',
        'identifier',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper statis untuk menyimpan log aktivitas autentikasi.
     */
    public static function log(string $event, ?int $userId = null, ?string $identifier = null, ?string $description = null): self
    {
        return static::create([
            'event'       => $event,
            'user_id'     => $userId,
            'identifier'  => $identifier,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}