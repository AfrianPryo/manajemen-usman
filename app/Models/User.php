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
        'completed_tours',
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
        'completed_tours'         => 'array',
        'password'                => 'hashed',
    ];

    /**
     * Kunci tutorial setup awal Dashboard Master Admin di users.completed_tours
     * (satu tempat penyimpanan dengan tutorial kontekstual di App\Support\PageTours).
     */
    public const DASHBOARD_TOUR = 'dashboard.setup';

    /**
     * Penanda bahwa tutorial setup awal Dashboard SUDAH PERNAH tampil otomatis
     * untuk akun ini (disimpan di users.completed_tours yang sama). Berbeda dari
     * DASHBOARD_TOUR yang baru terisi saat user menekan "Selesai"/"Lewati":
     * penanda ini terisi begitu tutorial pertama kali muncul, sehingga tutorial
     * tidak muncul lagi di login berikutnya walau tab ditutup di tengah jalan.
     */
    public const DASHBOARD_TOUR_SHOWN = 'dashboard.setup.shown';

    /**
     * Apakah akun ini masih perlu melihat tur/tutorial setup awal Dashboard
     * (menambahkan Unit Usaha pertama, Admin pertama, integrasi Fonnte, dll).
     *
     * Ditentukan SEMATA-MATA oleh apakah akun ini sudah pernah menyelesaikan/
     * melewati tutorial itu (kunci DASHBOARD_TOUR di completed_tours) -- sama
     * seperti tutorial per-menu. Sengaja TIDAK lagi memakai
     * 'onboarding_completed_at': kolom itu bisa sudah terisi sejak akun dibuat
     * (default migrasi / backfill / seeder), sehingga tutorial tidak pernah
     * tampil untuk akun hasil seeder. Isi database lain (Unit, Admin, Fonnte)
     * juga tidak ikut dipertimbangkan.
     */
    public function needsOnboarding(): bool
    {
        return ! $this->hasCompletedTour(self::DASHBOARD_TOUR);
    }

    /**
     * Tandai tutorial setup awal Dashboard selesai/dilewati. 'onboarding_completed_at'
     * tetap diisi (bila masih kosong) demi kompatibilitas dengan kode lama yang
     * mungkin masih membacanya.
     */
    public function markOnboardingCompleted(): void
    {
        $tours = $this->completed_tours ?? [];
        $tours[] = self::DASHBOARD_TOUR;

        $this->forceFill([
            'completed_tours'         => array_values(array_unique($tours)),
            'onboarding_completed_at' => $this->onboarding_completed_at ?? now(),
        ])->save();
    }

    /**
     * Apakah tutorial kontekstual halaman/aksi tertentu (lihat
     * App\Support\PageTours) sudah pernah diselesaikan atau dilewati akun ini.
     */
    public function hasCompletedTour(string $key): bool
    {
        return in_array($key, $this->completed_tours ?? [], true);
    }

    /**
     * Tandai tutorial kontekstual selesai/dilewati supaya tidak muncul otomatis
     * lagi (tetap bisa diputar ulang manual lewat tombol "Panduan").
     */
    public function markTourCompleted(string $key): void
    {
        if ($this->hasCompletedTour($key)) {
            return;
        }

        // Baca ulang dari database (bukan dari atribut model di memori) supaya dua
        // tutorial yang dicatat hampir bersamaan tidak saling menimpa.
        $fresh = static::query()->whereKey($this->getKey())->value('completed_tours');
        $tours = is_array($fresh) ? $fresh : ($this->completed_tours ?? []);

        $this->forceFill([
            'completed_tours' => array_values(array_unique([...$tours, $key])),
        ])->save();
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