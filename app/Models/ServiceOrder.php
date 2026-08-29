<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk fitur "Pesanan Layanan" -- khusus unit usaha berkategori
 * 'jasa' (lihat Unit::$category). Ditulis sejajar dengan gaya Product /
 * FinanceTransaction: fillable eksplisit, relasi BelongsTo ke Unit & User,
 * dan helper status yang dipakai langsung dari Blade (mirip
 * Product::isLowStock()).
 */
class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'user_id',
        'customer_name',
        'customer_phone',
        'service_name',
        'description',
        'assigned_to',
        'price',
        'scheduled_at',
        'status', // 'pending', 'in_progress', 'completed', 'cancelled'
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'price' => 'decimal:2',
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

    public function isUpcoming(): bool
    {
        return in_array($this->status, ['pending', 'in_progress'], true)
            && $this->scheduled_at
            && $this->scheduled_at->isFuture();
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pending', 'in_progress'], true)
            && $this->scheduled_at
            && $this->scheduled_at->isPast();
    }
}
