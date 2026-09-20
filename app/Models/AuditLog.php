<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
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

    // CATATAN: model ini SENGAJA tidak lagi memakai MassPrunable. Trait itu
    // menghapus baris >= 90 hari lewat `model:prune` TANPA menyimpan apa pun,
    // sehingga riwayatnya hilang. Pembersihan sekarang ditangani
    // App\Services\LogArchiveService (command `logs:archive`): diekspor ke
    // Excel per bulan dulu, baru dihapus. Batas retensinya diatur di
    // Pengaturan > Fitur & Modul (bukan hard-coded 90 hari lagi).

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