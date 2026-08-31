<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat pengumuman yang dikirim Master Admin ke seluruh Unit Admin.
 * Lihat komentar di migration create_announcements_table.
 */
class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'badge',
        'recipients_count',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
