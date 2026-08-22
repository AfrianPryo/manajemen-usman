<?php

namespace App\Exports\Dashboard;

use App\Models\AuthLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected int $limit = 6;

    public function query(): Builder
    {
        return AuthLog::query()->with('user')->latest()->limit($this->limit);
    }

    public function headings(): array
    {
        return ['Event', 'Oleh', 'Identifier', 'Alamat IP', 'Perangkat / Browser', 'Waktu'];
    }

    public function map($log): array
    {
        return [
            ucwords(str_replace(['.', '_'], ' ', $log->event)),
            $log->user->name ?? $log->identifier ?? 'Sistem',
            $log->identifier ?? '-',
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
            $log->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function title(): string
    {
        return 'Log Aktivitas';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }
}