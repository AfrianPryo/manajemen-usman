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

    // Konteks baris yang sedang divalidasi, diisi di prepareForValidation()
    private ?int $currentUnitId = null;
    private string $currentType = '';

    public function __construct()
    {
        // 1. Cache Unit Usaha: [Nama Unit => ID Unit]
        $this->units = Unit::pluck('id', 'name')->toArray();

        // 2. Cache Kategori Akurat: ["{unit_id}_{tipe}_{nama_kategori}" => ID Kategori]
        //
        // PENTING: sejak migration create_finance_categories_table, kolom
        // `unit_id` di tabel finance_categories SUDAH DIHAPUS. Kategori
        // sekarang punya `scope`:
        //   - 'all'      => berlaku untuk SEMUA unit (termasuk unit yang
        //                   dibuat belakangan), tidak lewat pivot sama sekali.
        //   - 'specific' => hanya berlaku untuk unit-unit yang tercatat di
        //                   tabel pivot finance_category_unit.
        //
        // Cache di bawah ini di-"expand" manual per unit supaya key-nya
        // ("{unit_id}_{type}_{name}") tetap sama persis dengan yang dipakai
        // saat validasi baris Excel (lihat rules() & model() di bawah).
        $this->categories = [];

        FinanceCategory::with('units')->get()->each(function (FinanceCategory $cat) {
            $typeNorm = strtolower(trim($cat->type));
            $nameNorm = strtolower(trim($cat->name));

            if ($cat->scope === 'all') {
                // Berlaku untuk semua unit yang ada saat ini
                foreach ($this->units as $unitId) {
                    $key = $unitId . '_' . $typeNorm . '_' . $nameNorm;
                    $this->categories[$key] = $cat->id;
                }
            } else {
                // scope 'specific' -> hanya unit-unit yang terhubung lewat pivot
                foreach ($cat->units as $unit) {
                    $key = $unit->id . '_' . $typeNorm . '_' . $nameNorm;
                    $this->categories[$key] = $cat->id;
                }
            }
        });

        // 3. Simpan daftar nama kategori unik untuk aturan validasi (fallback pesan umum)
        $this->validCatNames = FinanceCategory::pluck('name')->map(fn($n) => strtolower(trim($n)))->unique()->toArray();
    }

    /**
     * Menyamakan alias tipe transaksi ("pemasukan"/"pengeluaran") ke nilai baku
     * yang dipakai di kolom `type` & di composite key kategori ("income"/"expense").
     */
    private function normalizeType(string $type): string
    {
        return match ($type) {
            'pemasukan'   => 'income',
            'pengeluaran' => 'expense',
            default       => $type,
        };
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

        // Simpan konteks unit & tipe baris ini supaya rules() bisa memvalidasi
        // kategori terhadap kombinasi unit_id + type yang SAMA persis dengan
        // yang dipakai model() saat insert (bukan hanya cek nama kategori global).
        $unitName = trim((string) ($data['unit_usaha'] ?? ''));
        $this->currentUnitId = $this->units[$unitName] ?? null;
        $this->currentType   = $this->normalizeType($data['tipe_incomeexpense'] ?? '');

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
        $type     = $this->normalizeType(strtolower(trim((string) ($row['tipe_incomeexpense'] ?? 'income'))));

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
            'kategori_transaksi_norm' => [
                'nullable',
                "required_with:{$fields}",
                Rule::in($this->validCatNames), // cek dasar: nama kategori dikenal sistem
                function ($attribute, $value, $fail) {
                    // Cek lanjutan: kategori ini harus benar-benar terdaftar untuk
                    // kombinasi Unit Usaha + Tipe Transaksi pada baris ini — inilah
                    // kombinasi yang dipakai model() untuk mengisi finance_category_id.
                    if (blank($value)) {
                        return;
                    }

                    $key = $this->currentUnitId . '_' . $this->currentType . '_' . $value;

                    if (! isset($this->categories[$key])) {
                        $fail('Kategori Transaksi tidak terdaftar untuk Unit Usaha dan Tipe Transaksi (income/expense) yang dipilih pada baris ini.');
                    }
                },
            ],
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