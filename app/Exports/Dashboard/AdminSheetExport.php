<?php

namespace App\Exports\Dashboard;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdminSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function query(): Builder
    {
        return User::query()
            ->with(['unit', 'roles'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama', 'Username', 'Email', 'NIP', 'Status Kepegawaian',
            'Role', 'Unit Kerja', 'Status Akun', 'Wajib Ganti Password',
            'Login Terakhir', 'Bergabung Sejak',
        ];
    }

    public function map($user): array
    {
        $isMaster = method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin();
        $roleLabel = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->implode(', ')
            : ($isMaster ? 'Master Admin' : 'Admin Unit');

        return [
            $user->id,
            $user->name,
            $user->username ?? explode('@', $user->email)[0],
            $user->email ?? '-',
            $user->nip ?? '-',
            match ($user->employee_status ?? null) {
                'nip'     => 'Pegawai NIP',
                'non_nip' => 'Pegawai Non-NIP',
                default   => '-',
            },
            $roleLabel ?: ($isMaster ? 'Master Admin' : 'Admin Unit'),
            $isMaster ? 'Semua Unit' : ($user->unit->name ?? '-'),
            $user->is_active ? 'Aktif' : 'Nonaktif',
            ($user->must_change_password ?? false) ? 'Ya' : 'Tidak',
            $user->last_login_at ? Carbon::parse($user->last_login_at)->format('Y-m-d H:i') : 'Belum pernah',
            optional($user->created_at)->format('Y-m-d H:i') ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Admin & Hak Akses';
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