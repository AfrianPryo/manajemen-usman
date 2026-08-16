<?php
namespace App\Imports;

use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Models\FinanceCategory;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TransactionsImport implements ToModel, WithHeadingRow
{
    private array $units;
    private array $categories;

    public function __construct()
    {
        // Cache data Unit dan Kategori (Nama => ID)
        $this->units = Unit::pluck('id', 'name')->toArray();
        $this->categories = FinanceCategory::pluck('id', 'name')->toArray();
    }

    public function model(array $row)
    {
        $unitName = trim($row['unit_usaha'] ?? '');
        $catName = trim($row['kategori_transaksi'] ?? '');

        $unitId = $this->units[$unitName] ?? null;
        $catId = $this->categories[$catName] ?? null;

        // Melewati baris jika Unit atau Kategori tidak ditemukan
        if (!$unitId || !$catId) {
            return null;
        }

        // Parsing tanggal Excel
        $rawDate = $row['tanggal_yyyy_mm_dd'] ?? now()->format('Y-m-d');
        $date = is_numeric($rawDate) 
            ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d')
            : Carbon::parse($rawDate)->format('Y-m-d');

        return new FinanceTransaction([
            'unit_id'             => $unitId,
            'finance_category_id' => $catId,
            'user_id'             => auth()->id(),
            'reference_no'        => 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'type'                => strtolower(trim($row['tipe_incomeexpense'] ?? 'income')),
            'status'              => 'completed',
            'payment_method'      => strtolower(trim($row['metode_pembayaran'] ?? 'cash')),
            'amount'              => (float) $row['nominal'],
            'description'         => $row['deskripsi_catatan'] ?? null,
            'transaction_date'    => $date,
        ]);
    }
}