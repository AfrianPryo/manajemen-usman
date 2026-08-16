<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'category_id',
        'code',
        'name',
        'unit_type',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'description',
        'image',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    // Accessors untuk kompatibilitas tampilan Blade
    protected function sku(): Attribute
    {
        return Attribute::make(get: fn () => $this->code);
    }

    protected function price(): Attribute
    {
        return Attribute::make(get: fn () => $this->selling_price);
    }

    protected function costPrice(): Attribute
    {
        return Attribute::make(get: fn () => $this->purchase_price);
    }
}