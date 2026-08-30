<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model untuk fitur "Manajemen Pelanggan" (gaya buku klien seperti Fresha).
 * Berbeda dengan ServiceOrder yang HANYA relevan untuk unit berkategori
 * 'jasa', Customer sengaja dibuat BERLAKU UNTUK SEMUA KATEGORI unit
 * (ritel maupun jasa) -- lihat routes/web.php & config/menu.php, tidak ada
 * middleware/key 'unit.category' yang dipasang untuk modul ini.
 *
 * Ditulis sejajar dengan gaya Vendor (fillable eksplisit, SoftDeletes,
 * kolom 'category' untuk segmentasi) sekaligus ServiceOrder (relasi
 * BelongsTo ke Unit & User, helper method dipakai langsung dari Blade).
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'user_id',
        'name',
        'phone',
        'email',
        'gender', // 'L' (Laki-laki) | 'P' (Perempuan)
        'birth_date',
        'category', // 'baru', 'reguler', 'member', 'vip' -- segmentasi pelanggan
        'address',
        'notes',
        'total_visits',
        'last_visit_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'    => 'date',
            'last_visit_at' => 'datetime',
            'total_visits'  => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Aksi cepat "Catat Kunjungan" (dipakai dari tabel, mirip
     * ServiceOrder::updateStatus() yang dipanggil langsung tanpa modal).
     */
    public function recordVisit(): void
    {
        $this->increment('total_visits');
        $this->forceFill(['last_visit_at' => now()])->save();
    }
}
