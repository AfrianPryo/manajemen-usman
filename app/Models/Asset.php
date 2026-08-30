<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'asset_tag',
        'name',
        'category',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'status',
        'condition',
        'assigned_to',
        'location',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    /**
     * Unit Usaha pemilik aset ini. SENGAJA nullable: aset milik Kantor
     * Pusat / dipakai bersama (tidak terikat ke satu Unit Usaha tertentu)
     * tetap valid dengan unit_id NULL -- lihat migrasi
     * add_unit_id_to_assets_table dan komentar di
     * App\Livewire\Master\Asset\Index.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}