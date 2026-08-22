<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AssetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    protected array $filters;
    protected ?array $selectedIds;

    public function __construct(array $filters = [], ?array $selectedIds = null)
    {
        $this->filters = $filters;
        $this->selectedIds = $selectedIds;
    }

    public function query(): Builder
    {
        if (!empty($this->selectedIds)) {
            return Asset::query()->whereIn('id', $this->selectedIds)->latest();
        }

        return Asset::query()
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('assigned_to', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['statusFilter'] ?? null, fn ($q, $val) => $q->where('status', $val))
            ->when($this->filters['categoryFilter'] ?? null, fn ($q, $val) => $q->where('category', $val))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Tag Aset', 'Nama Aset', 'Kategori', 'Nomor Seri', 'Tanggal Pembelian',
            'Harga Beli', 'Status', 'Kondisi', 'Ditugaskan Kepada', 'Lokasi', 'Catatan',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->name,
            $asset->category,
            $asset->serial_number ?? '-',
            optional($asset->purchase_date)->format('Y-m-d') ?? '-',
            (float) ($asset->purchase_cost ?? 0),
            match ($asset->status) {
                'available'   => 'Tersedia',
                'assigned'    => 'Ditugaskan',
                'maintenance' => 'Perawatan',
                'retired'     => 'Pensiun',
                default       => $asset->status,
            },
            match ($asset->condition) {
                'good'    => 'Baik',
                'fair'    => 'Cukup',
                'damaged' => 'Rusak',
                default   => $asset->condition,
            },
            $asset->assigned_to ?? '-',
            $asset->location ?? '-',
            $asset->notes ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return ['F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function title(): string
    {
        return 'Data Aset';
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