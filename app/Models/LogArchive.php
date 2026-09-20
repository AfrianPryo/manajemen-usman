<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogArchive extends Model
{
    public const TYPE_AUTH  = 'auth';
    public const TYPE_AUDIT = 'audit';

    protected $fillable = [
        'type',
        'period',
        'file_path',
        'file_name',
        'row_count',
        'file_size',
    ];

    protected $casts = [
        'row_count' => 'integer',
        'file_size' => 'integer',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_AUTH  => 'Log Aktivitas Login',
            self::TYPE_AUDIT => 'Audit Log Sistem',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    /**
     * 'YYYY-MM' -> 'Juni 2026' (ikut locale Carbon aplikasi).
     * Format '!Y-m' me-reset hari ke tanggal 1, supaya tidak "overflow"
     * kalau hari ini tanggal 31.
     */
    public function getPeriodLabelAttribute(): string
    {
        try {
            return Carbon::createFromFormat('!Y-m', $this->period)->translatedFormat('F Y');
        } catch (\Throwable) {
            return $this->period;
        }
    }

    public function getHumanSizeAttribute(): string
    {
        return self::formatBytes($this->file_size);
    }

    public function fileExists(): bool
    {
        return Storage::disk(\App\Services\LogArchiveService::DISK)->exists($this->file_path);
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size  = $bytes / 1024;
        $i     = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return number_format($size, 1, ',', '.') . ' ' . $units[$i];
    }
}
