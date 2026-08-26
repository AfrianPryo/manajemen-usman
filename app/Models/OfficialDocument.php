<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_template_id',
        'type',
        'document_number',
        'title',
        'subject',
        'recipient',
        'unit_id',
        'period_start',
        'period_end',
        'data_snapshot',
        'file_path',
        'signed_by_name',
        'signed_by_position',
        'signature_path',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
