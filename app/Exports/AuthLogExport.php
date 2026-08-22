<?php

namespace App\Exports;

use App\Models\AuthLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AuthLogExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        return AuthLog::query()
            ->with('user')
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('identifier', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->filters['eventFilter'] ?? null, fn ($q, $val) => $q->where('event', $val))
            ->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Waktu Aktivitas',
            'Nama Pengguna',
            'Email',
            'Identifier',
            'Jenis Event',
            'Alamat IP',
            'Perangkat / Browser',
        ];
    }

    public function map($log): array
    {
        return [
            optional($log->created_at)->format('Y-m-d H:i:s'),
            $log->user->name ?? 'Sistem / Guest',
            $log->user->email ?? '-',
            $log->identifier ?? '-',
            match ($log->event) {
                'login.success'    => 'Login Berhasil',
                'login.failed'     => 'Login Gagal',
                'logout'           => 'Logout',
                'password.changed' => 'Password Diubah',
                default            => ucwords(str_replace(['.', '_'], ' ', $log->event)),
            },
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Log Aktivitas Login';
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