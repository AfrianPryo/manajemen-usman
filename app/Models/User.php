<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'unit_id',
        'nip',
        'phone',
        'employee_status',
        'profile_photo_path',
        'is_active',
        'must_change_password',
        'last_login_at',
        'last_login_ip',
        'current_session_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'last_login_at'        => 'datetime',
        'is_active'            => 'boolean',
        'must_change_password' => 'boolean',
        'password'             => 'hashed',
    ];

    /**
     * Accessor: ID Resmi untuk Laporan/PDF
     */
    public function getOfficialIdAttribute(): string
    {
        return ($this->employee_status === 'nip' && !empty($this->nip)) ? $this->nip : '-';
    }

    /**
     * Accessor: Format Identitas Lengkap untuk Audit Log & Navigation
     */
    public function getFormattedIdentityAttribute(): string
    {
        if ($this->employee_status === 'nip' && !empty($this->nip)) {
            return "{$this->name} (NIP: {$this->nip})";
        }

        return "{$this->name} (Non-NIP)";
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isMasterAdmin(): bool
    {
        return $this->hasRole('master-admin');
    }

    public function isUnitAdmin(): bool
    {
        return $this->hasRole('unit-admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}