<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
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
}