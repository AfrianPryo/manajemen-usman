<?php

namespace App\Exports\Dashboard\Unit;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockAlertSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected int $unitId;

    public function __construct(int $unitId)
    {
        $this->unitId = $unitId;
    }

    public function query(): Builder
    {
        return Product::query()
            ->with('category')
            ->where('unit_id', $this->unitId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock');
    }

    public function headings(): array
    {
        return ['Kode', 'Nama Produk', 'Kategori', 'Stok Saat Ini', 'Stok Minimum', 'Satuan'];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->name,
            $product->category->name ?? '-',
            $product->stock,
            $product->min_stock,
            $product->unit_type ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Stok Menipis';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }
}
