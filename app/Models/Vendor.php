<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Vendor -- SEKARANG juga merangkap fungsi "Supplier" lewat kolom
 * 'type' ('vendor' | 'supplier' | 'both'). Menu, judul halaman, dan label
 * di seluruh Livewire/Blade terkait sudah disesuaikan menjadi "Vendor &
 * Supplier" (lihat App\Livewire\Master\Vendor\Index & config/menu.php),
 * tapi nama tabel, model, namespace, dan route TETAP 'vendor(s)' -- sengaja
 * tidak diganti supaya tidak memutus relasi/route/permission yang sudah
 * ada di tempat lain (mirror dari alasan yang sama kenapa route
 * 'unit.service-orders.index' tidak diganti walau labelnya "Pesanan
 * Layanan").
 */
class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'type',
        'contact_name',
        'email',
        'phone',
        'website',
        'address',
        'id_number',
        'contract_start_date',
        'contract_end_date',
    ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];
}
