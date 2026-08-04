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
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): HasMany
    {
        return $this->hasMany(User::class)
                    ->where('is_active', true)
                    ->role('unit-admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}