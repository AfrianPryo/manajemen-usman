<?php

namespace App\Exports\Dashboard;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class UnitSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function query(): Builder
    {
        return Unit::query()
            ->with('users')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('department', 'like', "%{$this->search}%"))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama Unit', 'Slug', 'Jurusan', 'Kategori Usaha',
            'Nama PIC', 'No. Telepon PIC', 'Status',
            'Nama Admin', 'Username Admin', 'Email Admin',
            'Jumlah Admin', 'Deskripsi', 'Dibuat Pada', 'Diperbarui Pada',
        ];
    }

    public function map($unit): array
    {
        $admin = $unit->users->first();

        return [
            $unit->id,
            $unit->name,
            $unit->slug ?? '-',
            $unit->department ?? '-',
            $unit->category ?? '-',
            $unit->pic_name ?? '-',
            $unit->phone ?? '-',
            $unit->is_active ? 'Aktif' : 'Nonaktif',
            $admin->name ?? 'Belum Ada Admin',
            $admin->username ?? '-',
            $admin->email ?? '-',
            $unit->users->count(),
            $unit->description ?? '-',
            optional($unit->created_at)->format('Y-m-d H:i') ?? '-',
            optional($unit->updated_at)->format('Y-m-d H:i') ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Unit Usaha';
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