<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SignatureProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'position',
        'signature_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Gambar tanda tangan disimpan di disk PRIVAT 'local' (tidak ada di
     * public/storage), jadi tidak bisa dipanggil lewat Storage::url().
     * Untuk pratinjau di UI, sajikan sebagai data URI.
     */
    public function getSignatureDataUriAttribute(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($this->signature_path)) {
            return null;
        }

        $mime = $disk->mimeType($this->signature_path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($disk->get($this->signature_path));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
