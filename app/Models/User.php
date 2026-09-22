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
        'onboarding_completed_at',
        'last_login_at',
        'last_login_ip',
        'current_session_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'last_login_at'           => 'datetime',
        'is_active'               => 'boolean',
        'must_change_password'    => 'boolean',
        'onboarding_completed_at' => 'datetime',
        'password'                => 'hashed',
    ];

    /**
     * Apakah akun ini masih perlu melihat tur/tutorial setup awal (mis. di
     * Dashboard Master Admin): menambahkan Unit Usaha pertama, Admin
     * pertama, integrasi Fonnte, dll. Hanya bernilai true untuk akun yang
     * 'onboarding_completed_at'-nya masih kosong -- pada praktiknya ini
     * hanya akun Master Admin awal hasil MasterAdminSeeder (kredensial
     * "dari dev"), karena akun lain dibuat dengan kolom ini otomatis
     * ter-isi (lihat migrasi 2026_09_22_000002).
     */
    public function needsOnboarding(): bool
    {
        return is_null($this->onboarding_completed_at);
    }

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