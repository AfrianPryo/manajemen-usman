<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan impor ini

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_id',
    ];

    /**
     * Relasi ke model Product (Menghilangkan error undefined method)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi ke model Unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}