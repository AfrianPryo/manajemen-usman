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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class StockReportExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        return Product::with(['unit', 'category'])
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
            'Kode Produk', 'Nama Produk', 'Unit Usaha', 'Kategori',
            'Stok Saat Ini', 'Stok Minimal', 'Satuan', 'Status Stok',
            'Harga Beli', 'Nilai Total Stok (HPP x Qty)',
        ];
    }

    public function map($product): array
    {
        $minStock = $product->min_stock ?? 5;
        $status = $product->stock <= 0
            ? 'Habis'
            : ($product->stock <= $minStock ? 'Menipis' : 'Aman');

        return [
            $product->code ?? 'KODE-' . $product->id,
            $product->name,
            $product->unit->name ?? '-',
            $product->category->name ?? 'Umum',
            (int) $product->stock,
            (int) $minStock,
            $product->unit_type ?? 'pcs',
            $status,
            (float) ($product->purchase_price ?? 0),
            (float) ($product->stock * ($product->purchase_price ?? 0)),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
        ];
    }
}