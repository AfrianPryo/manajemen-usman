<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    private array $units;
    private array $categories;

    public function __construct()
    {
        // Cache data Unit dan Kategori (Nama => ID)
        $this->units = Unit::pluck('id', 'name')->toArray();
        $this->categories = Category::pluck('id', 'name')->toArray();
    }

    public function model(array $row)
    {
        $name     = trim($row['nama_produk'] ?? '');
        $unitName = trim($row['unit_usaha'] ?? '');
        $catName  = trim($row['kategori_produk'] ?? '');

        // Abaikan baris jika Nama Produk kosong
        if (empty($name)) {
            return null;
        }

        $unitId = $this->units[$unitName] ?? null;
        $catId  = $this->categories[$catName] ?? null;

        // Melewati baris jika Unit atau Kategori tidak valid
        if (!$unitId || !$catId) {
            return null;
        }

        // Generate kode produk otomatis jika kosong
        $code = trim($row['kode_produk'] ?? '');
        if (empty($code)) {
            $code = 'PRD-' . strtoupper(Str::random(6));
        }

        // Simpan data produk (Update jika kode & unit sama, Create jika baru)
        return Product::updateOrCreate(
            [
                'code'    => $code,
                'unit_id' => $unitId,
            ],
            [
                'name'           => $name,
                'category_id'    => $catId,
                'purchase_price' => (float) ($row['harga_beli'] ?? 0),
                'selling_price'  => (float) ($row['harga_jual'] ?? 0),
                'stock'          => (int) ($row['stok_initial'] ?? 0),
                'min_stock'      => (int) ($row['stok_minimal'] ?? 5),
                'unit_type'      => strtolower(trim($row['satuan_unit'] ?? 'pcs')) ?: 'pcs',
                'description'    => $row['deskripsi_catatan'] ?? null,
            ]
        );
    }
}