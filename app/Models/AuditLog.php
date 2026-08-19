<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use MassPrunable;

    protected $fillable = [
        'user_id',
        'event',
        'identifier',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Kriteria log yang akan dihapus otomatis oleh pembersih latar belakang (Cron / Scheduler).
     */
    public function prunable()
    {
        // Otomatis menghapus log yang dibuat >= 90 hari yang lalu
        return static::where('created_at', '<=', now()->subDays(90));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper statis untuk mempermudah pencatatan audit log dari mana saja.
     */
    public static function record(
        string $event,
        ?string $identifier = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'user_id'     => auth()->id(),
            'event'       => strtoupper($event),
            'identifier'  => $identifier,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}