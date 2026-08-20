<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'title',
        'type',
        'amount',
        'frequency',
        'start_date',
        'end_date',
        'next_run_date',
        'auto_approve',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_date' => 'date',
        'auto_approve' => 'boolean',
    ];

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Relasi ke unit bisnis sekolah
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }


}