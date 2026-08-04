<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'event',
        'identifier',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $event, ?int $userId = null, ?string $identifier = null, ?string $description = null): void
    {
        static::create([
            'event' => $event,
            'user_id' => $userId,
            'identifier' => $identifier,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}