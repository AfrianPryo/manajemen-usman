<?php

namespace App\Exports;

use App\Models\Product;
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

class ProductExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
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
        // Mode "Export Terpilih": abaikan filter, cukup pakai daftar ID yang dicentang
        if (!empty($this->selectedIds)) {
            return Product::with(['unit', 'category'])
                ->whereIn('id', $this->selectedIds)
                ->orderBy('name');
        }

        // Mode "Export Sesuai Filter Aktif"
        return Product::with(['unit', 'category'])
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['unitFilter'] ?? null, fn ($q, $val) => $q->where('unit_id', $val))
            ->when($this->filters['categoryFilter'] ?? null, fn ($q, $val) => $q->where('category_id', $val))
            ->when(($this->filters['stockFilter'] ?? null) === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->when(($this->filters['stockFilter'] ?? null) === 'low', fn ($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock'))
            ->when(($this->filters['stockFilter'] ?? null) === 'normal', fn ($q) => $q->whereColumn('stock', '>', 'min_stock'))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Unit Usaha',
            'Kategori',
            'Harga Beli (HPP)',
            'Harga Jual',
            'Stok Saat Ini',
            'Stok Minimal',
            'Satuan',
            'Status Stok',
            'Est. Nilai Inventaris',
            'Deskripsi',
        ];
    }

    public function map($p): array
    {
        $minStock = $p->min_stock ?? 5;
        $status = $p->stock <= 0 ? 'Habis' : ($p->stock <= $minStock ? 'Menipis' : 'Tersedia');

        return [
            $p->code ?? 'KODE-' . $p->id,
            $p->name,
            $p->unit->name ?? '-',
            $p->category->name ?? 'Umum',
            (float) ($p->purchase_price ?? 0),
            (float) $p->selling_price,
            (int) $p->stock,
            (int) $minStock,
            $p->unit_type ?? 'pcs',
            $status,
            (float) ($p->stock * ($p->purchase_price ?? 0)),
            $p->description ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Data Produk';
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