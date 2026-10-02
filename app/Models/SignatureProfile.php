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
     * Batas ukuran file tanda tangan yang boleh di-encode ke base64 untuk
     * data URI. File lebih besar dari ini tidak di-encode supaya tidak
     * menyebabkan memory spike, terutama saat accessor dipanggil dalam loop.
     * Nilai 2 MB sudah lebih dari cukup untuk file tanda tangan (gambar kecil).
     */
    private const MAX_SIGNATURE_BYTES = 2 * 1024 * 1024; // 2 MB

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

        // Cegah memory spike: jangan encode file terlalu besar
        if ($disk->size($this->signature_path) > self::MAX_SIGNATURE_BYTES) {
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
