<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        return AuditLog::query()
            ->with('user')
            // Samakan dengan logika halaman: audit log "sistem" tidak menampilkan event login/logout,
            // karena itu sudah punya halaman & export sendiri (Monitoring Aktivitas).
            ->whereNotIn('event', ['USER_LOGIN', 'USER_LOGOUT', 'LOGIN_FAILED'])
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('identifier', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->filters['eventFilter'] ?? null, fn ($q, $val) => $q->where('event', $val))
            ->when($this->filters['startDate'] ?? null, fn ($q, $val) => $q->whereDate('created_at', '>=', $val))
            ->when($this->filters['endDate'] ?? null, fn ($q, $val) => $q->whereDate('created_at', '<=', $val))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Waktu',
            'Pengguna',
            'Jenis Event',
            'Identifier',
            'Deskripsi Aktivitas',
            'Data Lama (Sebelum)',
            'Data Baru (Sesudah)',
            'Alamat IP',
            'User Agent',
        ];
    }

    public function map($log): array
    {
        return [
            optional($log->created_at)->format('Y-m-d H:i:s'),
            $log->user->name ?? 'Sistem / Anonim',
            str_replace('_', ' ', $log->event),
            $log->identifier ?? '-',
            $log->description ?? '-',
            !empty($log->old_values) ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '-',
            !empty($log->new_values) ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '-',
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Audit Log Sistem';
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