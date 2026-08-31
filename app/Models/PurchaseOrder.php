<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model "Pembelian" (Purchase Order ke Vendor/Supplier). Lihat komentar
 * lengkap di migration create_purchase_orders_table untuk latar belakang
 * kenapa modul ini dibuat (Vendor & FinanceTransaction sebelumnya tidak
 * saling terhubung sama sekali).
 *
 * Ditulis sejajar gaya Vendor & ServiceOrder: fillable eksplisit, relasi
 * BelongsTo standar, dan beberapa helper method yang dipanggil langsung
 * dari Blade (mirror Customer::recordVisit()).
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'vendor_id',
        'user_id',
        'finance_transaction_id',
        'po_number',
        'status', // 'completed', 'cancelled'
        'payment_method',
        'items',
        'total_amount',
        'notes',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total_amount' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Jumlah baris item yang benar-benar terhubung ke produk bertsok
     * (dipakai Blade untuk menandai baris mana yang memengaruhi stok).
     */
    public function stockAffectingItemsCount(): int
    {
        return collect($this->items)->filter(fn ($item) => !empty($item['product_id']))->count();
    }
}
