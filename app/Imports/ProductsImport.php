<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    private array $units;
    private array $categories;
    private array $validCatNames;

    public function __construct()
    {
        // 1. Cache Unit: [Nama Unit => ID Unit]
        $this->units = Unit::pluck('id', 'name')->toArray();

        // 2. Cache Kategori Akurat: ["{unit_id}_{nama_kategori}" => ID Kategori]
        // Ini mencegah tertukarnya kategori yang memiliki nama sama antar Unit Usaha berlainan
        $this->categories = Category::all()->pluck('id', function ($item) {
            return $item->unit_id . '_' . trim($item->name);
        })->toArray();

        // 3. Simpan daftar nama kategori unik untuk validasi
        $this->validCatNames = Category::pluck('name')->unique()->toArray();
    }

    /**
     * Pre-processing data sebelum validasi dijalankan (Normalisasi Input)
     */
    public function prepareForValidation(array $data, int $index): array
    {
        // Normalisasi Kode Produk:
        // Menangani nama header dari Excel baik 'kode_produk' maupun 'kode_produk_kosongkan_jika_auto_generate'
        $data['kode_produk'] = trim((string) (
            $data['kode_produk'] 
            ?? $data['kode_produk_kosongkan_jika_auto_generate'] 
            ?? ''
        ));

        // Normalisasi Satuan Unit ke huruf kecil (lowercase) agar tidak gagal pada Rule::in
        if (isset($data['satuan_unit'])) {
            $data['satuan_unit'] = strtolower(trim((string) $data['satuan_unit']));
        }

        return $data;
    }

    /**
     * Mengecek apakah baris dianggap kosong total
     */
    public function isEmpty(array $row): bool
    {
        $nama     = trim((string) ($row['nama_produk'] ?? ''));
        $unit     = trim((string) ($row['unit_usaha'] ?? ''));
        $kategori = trim((string) ($row['kategori_produk'] ?? ''));
        $harga    = trim((string) ($row['harga_jual'] ?? ''));

        return empty($nama) && empty($unit) && empty($kategori) && empty($harga);
    }

    public function model(array $row)
    {
        // Abaikan baris jika kosong total
        if ($this->isEmpty($row)) {
            return null;
        }

        $name     = trim((string) ($row['nama_produk'] ?? ''));
        $unitName = trim((string) ($row['unit_usaha'] ?? ''));
        $catName  = trim((string) ($row['kategori_produk'] ?? ''));

        $unitId = $this->units[$unitName] ?? null;

        // Ambil ID Kategori yang MURNI cocok dengan Unit Usaha yang dipilih
        $catKey = $unitId . '_' . $catName;
        $catId  = $this->categories[$catKey] ?? null;

        // Auto-Generate Kode Produk jika dikosongkan di Excel
        $code = trim((string) ($row['kode_produk'] ?? ''));
        if (empty($code)) {
            do {
                $code = 'PRD-' . strtoupper(Str::random(6));
            } while (Product::where('code', $code)->where('unit_id', $unitId)->exists());
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
                'unit_type'      => strtolower(trim((string) ($row['satuan_unit'] ?? 'pcs'))) ?: 'pcs',
                'description'    => $row['deskripsi_catatan'] ?? null,
            ]
        );
    }

    /**
     * Aturan Validasi untuk setiap baris di file Excel
     */
    public function rules(): array
    {
        $validUnitNames = array_keys($this->units);
        $validUnitTypes = ['pcs', 'box', 'pack', 'kg', 'liter', 'porsi', 'unit', 'lusin'];

        // Daftar kolom untuk pengecekan required_with
        $fields = 'kode_produk,nama_produk,unit_usaha,kategori_produk,harga_beli,harga_jual,stok_initial,stok_minimal,satuan_unit,deskripsi_catatan';

        return [
            'nama_produk'     => ['nullable', "required_with:{$fields}", 'string'],
            'unit_usaha'      => ['nullable', "required_with:{$fields}", Rule::in($validUnitNames)],
            'kategori_produk' => ['nullable', "required_with:{$fields}", Rule::in($this->validCatNames)],
            'harga_beli'      => ['nullable', 'numeric', 'min:0'],
            'harga_jual'      => ['nullable', 'numeric', 'min:0'],
            'stok_initial'    => ['nullable', 'numeric', 'min:0'],
            'stok_minimal'    => ['nullable', 'numeric', 'min:0'],
            'satuan_unit'     => ['nullable', "required_with:{$fields}", Rule::in($validUnitTypes)],
        ];
    }

    /**
     * Pesan kesalahan Kustom agar ramah dibaca pengguna di Modal Popup
     */
    public function customValidationMessages(): array
    {
        return [
            'required'           => 'Kolom ini wajib diisi.',
            'required_with'      => 'Kolom ini wajib diisi jika data produk dimasukkan.',
            'numeric'            => 'Harus berupa angka.',
            'min'                => 'Nilai minimal tidak boleh kurang dari 0.',
            'unit_usaha.in'      => 'Nama Unit Usaha tidak ada dalam sistem / tidak sesuai dropdown.',
            'kategori_produk.in' => 'Kategori Produk tidak ditemukan dalam sistem / tidak sesuai unit usaha.',
            'satuan_unit.in'     => 'Satuan Unit tidak valid / tidak sesuai opsi dropdown.',
        ];
    }

    /**
     * Label nama kolom untuk tampilan Modal Popup
     */
    public function customValidationAttributes(): array
    {
        return [
            'kode_produk'       => 'Kode Produk',
            'nama_produk'       => 'Nama Produk',
            'unit_usaha'        => 'Unit Usaha',
            'kategori_produk'   => 'Kategori Produk',
            'harga_beli'        => 'Harga Beli',
            'harga_jual'        => 'Harga Jual',
            'stok_initial'      => 'Stok Initial',
            'stok_minimal'      => 'Stok Minimal',
            'satuan_unit'       => 'Satuan Unit',
            'deskripsi_catatan' => 'Deskripsi Catatan',
        ];
    }
}