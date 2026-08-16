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
     * Relasi ke User / Admin Unit
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}