<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'description',
        'file_path',
        'placeholders',
        'numbering_format',
        'numbering_reset',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function officialDocuments(): HasMany
    {
        return $this->hasMany(OfficialDocument::class);
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(DocumentNumberSequence::class);
    }
}
