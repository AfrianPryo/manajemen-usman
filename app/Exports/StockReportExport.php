<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('category')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama Produk', 'Kategori', 'Stok', 'Satuan', 'Harga Beli', 'Harga Jual', 'Status'];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->name,
            $product->category->name,
            $product->stock,
            $product->unit,
            $product->purchase_price,
            $product->selling_price,
            $product->isLowStock() ? 'Menipis' : 'Aman',
        ];
    }
}
