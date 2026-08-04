<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'department',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke seluruh User yang terikat pada unit ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi khusus ke 1 Admin Unit yang aktif pada unit ini.
     */
    public function admin(): HasOne
    {
        return $this->hasOne(User::class)
                    ->where('is_active', true)
                    ->role('unit-admin');
    }

    /**
     * Scope untuk menyaring unit yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}