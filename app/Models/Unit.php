<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'department',
        'category',
        'pic_name',
        'phone',
        'description',
        'is_active',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Aset Unit Usaha (App\Models\Asset::unit_id). Nullable di sisi Asset,
     * jadi relasi ini hanya mengembalikan aset yang memang sudah ditautkan
     * ke unit ini -- aset "Pusat / Tanpa Unit" tidak akan muncul di sini.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Relasi ke User / Admin Unit
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}