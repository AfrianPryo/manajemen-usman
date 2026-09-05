<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'scope'];

    /**
     * Unit-unit yang memakai kategori ini. HANYA relevan/diisi kalau
     * scope = 'specific' -- kategori berscope 'all' tidak punya baris
     * pivot sama sekali (lihat scopeForUnit() di bawah).
     */
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'finance_category_unit');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'finance_category_id');
    }

    /**
     * Query scope: kategori yang BERLAKU untuk satu Unit Usaha tertentu --
     * yaitu kategori berscope 'all' (berlaku ke semua unit), ATAU kategori
     * berscope 'specific' yang unit_id-nya ada di daftar pivot.
     *
     * Pengganti pola lama `FinanceCategory::where('unit_id', $unitId)`
     * yang sudah tidak berlaku sejak kategori tidak lagi terikat ke satu
     * unit_id saja.
     */
    public function scopeForUnit(Builder $query, ?int $unitId): Builder
    {
        return $query->where(function (Builder $q) use ($unitId) {
            $q->where('scope', 'all');

            if ($unitId) {
                $q->orWhereHas('units', fn (Builder $u) => $u->where('units.id', $unitId));
            }
        });
    }

    /**
     * Cek apakah kategori ini berlaku untuk satu Unit Usaha tertentu.
     * Butuh relasi 'units' sudah di-load (atau akan di-lazy-load) untuk
     * menghindari query berulang saat dipanggil di loop.
     */
    public function appliesToUnit(?int $unitId): bool
    {
        if (! $unitId) {
            return false;
        }

        if ($this->scope === 'all') {
            return true;
        }

        return $this->units->contains('id', $unitId);
    }

    /**
     * Cari kategori (nama + tipe) yang SUDAH berlaku untuk unit ini
     * (baik berscope 'all' maupun 'specific' yang mencakup unit ini),
     * atau buat kategori baru berscope 'specific' khusus unit ini kalau
     * belum ada. Dipakai modul yang butuh kategori otomatis tanpa
     * campur tangan admin, mis. App\Livewire\Unit\Purchasing\Index
     * (transaksi pengeluaran "Pembelian").
     */
    public static function firstOrCreateForUnit(string $name, string $type, int $unitId): self
    {
        $existing = static::query()
            ->where('name', $name)
            ->whereRaw('LOWER(type) = ?', [strtolower($type)])
            ->forUnit($unitId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $category = static::create([
            'name'  => $name,
            'type'  => $type,
            'scope' => 'specific',
        ]);
        $category->units()->attach($unitId);

        return $category;
    }
}
