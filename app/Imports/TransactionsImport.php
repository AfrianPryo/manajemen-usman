<?php

namespace App\Imports;

use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TransactionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    private array $units;
    private array $categories;
    private array $validCatNames;

    public function __construct()
    {
        // 1. Cache Unit Usaha: [Nama Unit => ID Unit]
        $this->units = Unit::pluck('id', 'name')->toArray();

        // 2. Cache Kategori Akurat: ["{unit_id}_{tipe}_{nama_kategori}" => ID Kategori]
        // Mencegah bentrok kategori dengan nama sama antar unit & tipe (income/expense)
        $this->categories = FinanceCategory::all()->pluck('id', function ($item) {
            return $item->unit_id . '_' . strtolower(trim($item->type)) . '_' . strtolower(trim($item->name));
        })->toArray();

        // 3. Simpan daftar nama kategori unik untuk aturan validasi
        $this->validCatNames = FinanceCategory::pluck('name')->map(fn($n) => strtolower(trim($n)))->unique()->toArray();
    }

    /**
     * Pre-processing data sebelum validasi dijalankan (Normalisasi Input)
     */
    public function prepareForValidation(array $data, int $index): array
    {
        // Normalisasi Tipe Transaksi
        if (isset($data['tipe_incomeexpense'])) {
            $data['tipe_incomeexpense'] = strtolower(trim((string) $data['tipe_incomeexpense']));
        }

        // Normalisasi Metode Pembayaran
        if (isset($data['metode_pembayaran'])) {
            $data['metode_pembayaran'] = strtolower(trim((string) $data['metode_pembayaran']));
        }

        // Normalisasi Nama Kategori
        if (isset($data['kategori_transaksi'])) {
            $data['kategori_transaksi_norm'] = strtolower(trim((string) $data['kategori_transaksi']));
        } else {
            $data['kategori_transaksi_norm'] = '';
        }

        return $data;
    }

    /**
     * Mengecek apakah baris dianggap kosong total
     */
    public function isEmpty(array $row): bool
    {
        $unit     = trim((string) ($row['unit_usaha'] ?? ''));
        $kategori = trim((string) ($row['kategori_transaksi'] ?? ''));
        $nominal  = trim((string) ($row['nominal'] ?? ''));
        $tipe     = trim((string) ($row['tipe_incomeexpense'] ?? ''));

        return empty($unit) && empty($kategori) && empty($nominal) && empty($tipe);
    }

    public function model(array $row)
    {
        // Abaikan baris jika kosong total
        if ($this->isEmpty($row)) {
            return null;
        }

        $unitName = trim((string) ($row['unit_usaha'] ?? ''));
        $catName  = strtolower(trim((string) ($row['kategori_transaksi'] ?? '')));
        $type     = strtolower(trim((string) ($row['tipe_incomeexpense'] ?? 'income')));

        $unitId = $this->units[$unitName] ?? null;

        // Ambil ID Kategori yang MURNI cocok dengan Unit Usaha & Tipe Transaksi
        $catKey = $unitId . '_' . $type . '_' . $catName;
        $catId  = $this->categories[$catKey] ?? null;

        // Parsing tanggal Excel (Mendukung Serial Number Excel & Format String)
        $rawDate = $row['tanggal_yyyy_mm_dd'] ?? now()->format('Y-m-d');
        try {
            $date = is_numeric($rawDate) 
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d')
                : Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = now()->format('Y-m-d');
        }

        return new FinanceTransaction([
            'unit_id'             => $unitId,
            'finance_category_id' => $catId,
            'user_id'             => auth()->id(),
            'reference_no'        => 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'type'                => $type,
            'status'              => 'completed',
            'payment_method'      => strtolower(trim((string) ($row['metode_pembayaran'] ?? 'cash'))) ?: 'cash',
            'amount'              => (float) ($row['nominal'] ?? 0),
            'description'         => $row['deskripsi_catatan'] ?? null,
            'transaction_date'    => $date,
        ]);
    }

    /**
     * Aturan Validasi untuk setiap baris di file Excel
     */
    public function rules(): array
    {
        $validUnitNames = array_keys($this->units);
        $validTypes     = ['income', 'expense', 'pemasukan', 'pengeluaran'];

        // Daftar kolom untuk pengecekan required_with
        $fields = 'tanggal_yyyy_mm_dd,unit_usaha,tipe_incomeexpense,kategori_transaksi,nominal,metode_pembayaran,deskripsi_catatan';

        return [
            'tanggal_yyyy_mm_dd'      => ['nullable', "required_with:{$fields}"],
            'unit_usaha'              => ['nullable', "required_with:{$fields}", Rule::in($validUnitNames)],
            'tipe_incomeexpense'      => ['nullable', "required_with:{$fields}", Rule::in($validTypes)],
            'kategori_transaksi_norm' => ['nullable', "required_with:{$fields}", Rule::in($this->validCatNames)],
            'nominal'                 => ['nullable', "required_with:{$fields}", 'numeric', 'gt:0'],
        ];
    }

    /**
     * Pesan kesalahan kustom yang ramah pengguna
     */
    public function customValidationMessages(): array
    {
        return [
            'required_with'              => 'Kolom ini wajib diisi.',
            'numeric'                    => 'Harus berupa angka.',
            'gt'                         => 'Nominal harus berupa angka lebih dari 0.',
            'unit_usaha.in'              => 'Nama Unit Usaha tidak ada dalam sistem / tidak sesuai dropdown.',
            'tipe_incomeexpense.in'      => 'Tipe transaksi harus "income" atau "expense".',
            'kategori_transaksi_norm.in' => 'Kategori Transaksi tidak ditemukan dalam sistem.',
        ];
    }

    /**
     * Label nama kolom untuk tampilan Modal Popup Error
     */
    public function customValidationAttributes(): array
    {
        return [
            'tanggal_yyyy_mm_dd'      => 'Tanggal',
            'unit_usaha'              => 'Unit Usaha',
            'tipe_incomeexpense'      => 'Tipe Transaksi',
            'kategori_transaksi'      => 'Kategori Transaksi',
            'kategori_transaksi_norm' => 'Kategori Transaksi',
            'nominal'                 => 'Nominal',
            'metode_pembayaran'       => 'Metode Pembayaran',
            'deskripsi_catatan'       => 'Deskripsi Catatan',
        ];
    }
}