<?php

namespace App\Imports;

use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class TransactionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    /** [nama unit ternormalisasi => id unit] */
    private array $units = [];

    /** ["{unit_id}|{tipe}|{nama_kategori_ternormalisasi}" => id kategori] */
    private array $categories = [];

    /** [nama_kategori_ternormalisasi => ['income' => true, 'expense' => true]] (hanya untuk pesan error) */
    private array $categoryTypes = [];

    /**
     * Hasil resolusi per baris, dikunci dengan nomor baris Excel.
     * Diisi di prepareForValidation(), dibaca oleh aturan di rules().
     * Dikunci per baris (bukan properti tunggal) supaya tetap benar walau
     * suatu saat import dijalankan per-batch (WithBatchInserts).
     */
    private array $context = [];

    /** Header kolom yang berhasil terbaca dari file (untuk pesan error) */
    private array $detectedHeaders = [];

    public function __construct()
    {
        // 1. Cache Unit Usaha: [nama ternormalisasi => id]
        foreach (Unit::pluck('id', 'name') as $name => $id) {
            $this->units[$this->normalizeText($name)] = $id;
        }

        // 2. Cache Kategori. Kategori TIDAK lagi punya unit_id:
        //   - scope 'all'      => berlaku untuk SEMUA unit
        //   - scope 'specific' => hanya unit-unit di pivot finance_category_unit
        // Di-"expand" per unit supaya lookup baris Excel tinggal 1 key.
        FinanceCategory::with('units')->get()->each(function (FinanceCategory $cat) {
            $type = $this->normalizeType($cat->type);
            $name = $this->normalizeText($cat->name);

            $this->categoryTypes[$name][$type] = true;

            $unitIds = $cat->scope === 'all'
                ? array_values($this->units)
                : $cat->units->pluck('id')->all();

            foreach ($unitIds as $unitId) {
                $this->categories[$unitId . '|' . $type . '|' . $name] = $cat->id;
            }
        });
    }

    // ------------------------------------------------------------------
    //  Normalisasi
    // ------------------------------------------------------------------

    /**
     * Huruf kecil + rapikan spasi (termasuk spasi non-breaking dari Excel/Word,
     * zero-width char, spasi ganda) supaya "Penjualan  Produk " == "penjualan produk".
     */
    private function normalizeText(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        $value = (string) $value;
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value) ?? $value;
        $value = preg_replace('/[\p{Z}\s]+/u', ' ', $value) ?? $value;

        return mb_strtolower(trim($value));
    }

    /** Alias tipe transaksi -> nilai baku 'income' / 'expense' ('' bila tidak dikenal). */
    private function normalizeType(mixed $type): string
    {
        return match ($this->normalizeText($type)) {
            'income', 'pemasukan', 'masuk'     => 'income',
            'expense', 'pengeluaran', 'keluar' => 'expense',
            default                            => '',
        };
    }

    /**
     * Alias nama kolom -> nama kolom baku (bentuk slug heading row).
     * Supaya file yang headernya "Kategori" / "Tipe" / "Tanggal" (mis. hasil
     * Export Data Transaksi) tetap terbaca, bukan hanya header template.
     */
    private function headingAliases(): array
    {
        $map = [
            'tanggal_yyyy_mm_dd' => ['Tanggal', 'Tgl', 'Tanggal Transaksi', 'Date'],
            'unit_usaha'         => ['Unit', 'Nama Unit', 'Nama Unit Usaha'],
            'kategori_transaksi' => ['Kategori', 'Nama Kategori', 'Category'],
            'tipe_incomeexpense' => ['Tipe', 'Tipe Transaksi', 'Jenis', 'Jenis Transaksi', 'Type'],
            'metode_pembayaran'  => ['Metode', 'Metode Bayar', 'Payment Method'],
            'nominal'            => ['Jumlah', 'Amount', 'Nilai'],
            'deskripsi_catatan'  => ['Deskripsi', 'Catatan', 'Keterangan'],
        ];

        $aliases = [];
        foreach ($map as $canonical => $labels) {
            $aliases[$canonical] = $canonical;
            foreach ($labels as $label) {
                $aliases[Str::slug($label, '_')] = $canonical;
            }
        }

        return $aliases;
    }

    private function canonicalizeHeadings(array $data): array
    {
        static $aliases = null;
        $aliases ??= $this->headingAliases();

        $out = [];
        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                continue; // kolom tanpa header
            }

            $slug      = Str::slug($key, '_');
            $canonical = $aliases[$slug] ?? $slug;

            // Kalau dua kolom memetakan ke nama baku yang sama, pakai yang terisi.
            if (! array_key_exists($canonical, $out) || $this->normalizeText($out[$canonical]) === '') {
                $out[$canonical] = $value;
            }
        }

        return $out;
    }

    /** "Rp 1.500.000" / "1,500,000.50" / "1500000" -> float (null bila bukan angka). */
    private function parseAmount(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        $s = preg_replace('/(rp\.?|idr|\s)/iu', '', $this->normalizeText($raw)) ?? '';
        if ($s === '') {
            return null;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $s)) {        // 1.500.000,50
            $s = str_replace(['.', ','], ['', '.'], $s);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $s)) {  // 1,500,000.50
            $s = str_replace(',', '', $s);
        } else {
            $s = str_replace(',', '.', $s);                          // 1500000,5
        }

        return is_numeric($s) ? (float) $s : null;
    }

    /** Serial Excel, atau string tanggal umum -> 'Y-m-d' (null bila tidak terbaca). */
    private function parseDate(mixed $raw): ?string
    {
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d');
        }

        if (is_int($raw) || is_float($raw) || (is_string($raw) && is_numeric($raw))) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $s = trim((string) $raw);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d', 'j/n/Y', 'j-n-Y'] as $format) {
            $dt     = \DateTime::createFromFormat('!' . $format, $s);
            $errors = \DateTime::getLastErrors();

            if ($dt && (! $errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $dt->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    // ------------------------------------------------------------------
    //  Tahap 1: normalisasi + resolusi (dipanggil package sebelum validasi)
    // ------------------------------------------------------------------

    public function prepareForValidation(array $data, int $index): array
    {
        $data = $this->canonicalizeHeadings($data);

        if (! $this->detectedHeaders) {
            $this->detectedHeaders = array_keys($data);
        }

        $unitRaw = trim((string) ($data['unit_usaha'] ?? ''));
        $catRaw  = trim((string) ($data['kategori_transaksi'] ?? ''));

        $unitId  = $this->units[$this->normalizeText($unitRaw)] ?? null;
        $type    = $this->normalizeType($data['tipe_incomeexpense'] ?? '');
        $catNorm = $this->normalizeText($catRaw);
        $catId   = ($unitId && $type && $catNorm !== '')
            ? ($this->categories[$unitId . '|' . $type . '|' . $catNorm] ?? null)
            : null;

        $amount = $this->parseAmount($data['nominal'] ?? null);

        $rawDate = $data['tanggal_yyyy_mm_dd'] ?? null;
        $dateBlank = ! ($rawDate instanceof \DateTimeInterface) && $this->normalizeText($rawDate) === '';
        $date = $dateBlank ? null : $this->parseDate($rawDate);

        $filled = $unitRaw !== '' || $catRaw !== '' || $this->normalizeText($data['nominal'] ?? '') !== ''
            || $this->normalizeText($data['tipe_incomeexpense'] ?? '') !== '';

        $this->context[$index] = [
            'filled'          => $filled,
            'unit_id'         => $unitId,
            'type'            => $type,
            'category_id'     => $catId,
            'category_error'  => $catId ? null : $this->explainCategoryMiss($unitRaw, $unitId, $type, $catRaw, $catNorm),
            'has_cat_column'  => array_key_exists('kategori_transaksi', $data),
            'amount'          => $amount,
            'date_blank'      => $dateBlank,
            'date'            => $date,
        ];

        // Nilai mentah (untuk ditampilkan di modal error) TIDAK diubah; hasil
        // normalisasi dititipkan lewat kunci berawalan "_" untuk dipakai model().
        $data['_row']     = $index;
        $data['_unit_id'] = $unitId;
        $data['_cat_id']  = $catId;
        $data['_type']    = $type;
        $data['_amount']  = $amount;
        $data['_date']    = $date;

        return $data;
    }

    /** Alasan spesifik kenapa kategori tidak ketemu (null bila tidak perlu dijelaskan). */
    private function explainCategoryMiss(string $unitRaw, ?int $unitId, string $type, string $catRaw, string $catNorm): ?string
    {
        if ($catNorm === '' || ! $unitId || $type === '') {
            return null; // ditangani aturan lain (kosong / unit salah / tipe salah)
        }

        if (! isset($this->categoryTypes[$catNorm])) {
            return "Kategori \"{$catRaw}\" tidak ada di sistem.";
        }

        if (! isset($this->categoryTypes[$catNorm][$type])) {
            $other = $type === 'income' ? 'expense' : 'income';

            return "Kategori \"{$catRaw}\" ada, tetapi bertipe {$other}, bukan {$type}. Cek kolom Tipe.";
        }

        return "Kategori \"{$catRaw}\" ada, tetapi tidak berlaku untuk unit \"{$unitRaw}\".";
    }

    // ------------------------------------------------------------------
    //  Tahap 2: validasi
    // ------------------------------------------------------------------

    /**
     * Bungkus closure jadi aturan validasi yang IMPLISIT (tetap dijalankan
     * walau nilai kolom kosong -- inilah yang sebelumnya tidak terjadi, jadi
     * baris dengan kategori kosong lolos validasi lalu gagal di model()).
     */
    private function rowRule(Closure $check): ValidationRule
    {
        return new class ($check) implements ValidationRule {
            public bool $implicit = true;

            public function __construct(private Closure $check)
            {
            }

            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                ($this->check)($value, $fail, (int) strtok($attribute, '.'));
            }
        };
    }

    public function rules(): array
    {
        return [
            'tanggal_yyyy_mm_dd' => [$this->rowRule(function ($value, $fail, $i) {
                $c = $this->context[$i] ?? null;
                if (! $c || ! $c['filled'] || $c['date_blank']) {
                    return; // tanggal kosong -> otomatis hari ini
                }
                if ($c['date'] === null) {
                    $fail('Format tanggal tidak dikenali. Gunakan YYYY-MM-DD.');
                }
            })],

            'unit_usaha' => [$this->rowRule(function ($value, $fail, $i) {
                $c = $this->context[$i] ?? null;
                if (! $c || ! $c['filled']) {
                    return;
                }
                if ($this->normalizeText($value) === '') {
                    $fail('Kolom ini wajib diisi.');
                } elseif (! $c['unit_id']) {
                    $fail('Nama Unit Usaha tidak ada dalam sistem / tidak sesuai dropdown.');
                }
            })],

            'tipe_incomeexpense' => [$this->rowRule(function ($value, $fail, $i) {
                $c = $this->context[$i] ?? null;
                if (! $c || ! $c['filled']) {
                    return;
                }
                if ($this->normalizeText($value) === '') {
                    $fail('Kolom ini wajib diisi.');
                } elseif ($c['type'] === '') {
                    $fail('Tipe transaksi harus "income" atau "expense".');
                }
            })],

            'kategori_transaksi' => [$this->rowRule(function ($value, $fail, $i) {
                $c = $this->context[$i] ?? null;
                if (! $c || ! $c['filled']) {
                    return;
                }
                if (! $c['has_cat_column']) {
                    $fail('Kolom "Kategori Transaksi" tidak ditemukan di header berkas. Header terbaca: '
                        . implode(', ', array_filter($this->detectedHeaders, fn ($h) => $h !== '' && ! str_starts_with($h, '_')))
                        . '. Gunakan template terbaru.');
                } elseif ($this->normalizeText($value) === '') {
                    $fail('Kolom ini wajib diisi.');
                } elseif ($c['category_error']) {
                    $fail($c['category_error']);
                }
            })],

            'nominal' => [$this->rowRule(function ($value, $fail, $i) {
                $c = $this->context[$i] ?? null;
                if (! $c || ! $c['filled']) {
                    return;
                }
                if ($this->normalizeText($value) === '') {
                    $fail('Kolom ini wajib diisi.');
                } elseif ($c['amount'] === null) {
                    $fail('Harus berupa angka.');
                } elseif ($c['amount'] <= 0) {
                    $fail('Nominal harus berupa angka lebih dari 0.');
                }
            })],
        ];
    }

    /**
     * Label nama kolom untuk tampilan Modal Popup Error
     */
    public function customValidationAttributes(): array
    {
        return [
            'tanggal_yyyy_mm_dd' => 'Tanggal',
            'unit_usaha'         => 'Unit Usaha',
            'tipe_incomeexpense' => 'Tipe Transaksi',
            'kategori_transaksi' => 'Kategori Transaksi',
            'nominal'            => 'Nominal',
            'metode_pembayaran'  => 'Metode Pembayaran',
            'deskripsi_catatan'  => 'Deskripsi Catatan',
        ];
    }

    // ------------------------------------------------------------------
    //  Tahap 3: buat model (hanya untuk baris yang lolos validasi)
    // ------------------------------------------------------------------

    public function model(array $row)
    {
        $c = $this->context[$row['_row'] ?? -1] ?? null;

        // Baris kosong total (hanya berisi spasi / sisa format)
        if (! $c || ! $c['filled']) {
            return null;
        }

        // Pengaman terakhir: seharusnya sudah disaring rules(). unit_id &
        // finance_category_id NOT NULL, jadi jangan pernah buat model tanpa keduanya.
        if (empty($row['_unit_id']) || empty($row['_cat_id'])) {
            $this->onFailure(new Failure(
                (int) ($row['_row'] ?? 0),
                'kategori_transaksi',
                [$c['category_error'] ?? 'Unit Usaha / Kategori Transaksi tidak dapat dipetakan.'],
                $row
            ));

            return null;
        }

        $payment     = $this->normalizeText($row['metode_pembayaran'] ?? '') ?: 'cash';
        $description = is_scalar($row['deskripsi_catatan'] ?? null) ? trim((string) $row['deskripsi_catatan']) : '';

        return new FinanceTransaction([
            'unit_id'             => $row['_unit_id'],
            'finance_category_id' => $row['_cat_id'],
            'user_id'             => auth()->id(),
            'reference_no'        => 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'type'                => $row['_type'],
            'status'              => 'completed',
            'payment_method'      => $payment,
            'amount'              => $row['_amount'],
            'description'         => $description !== '' ? $description : null,
            'transaction_date'    => $row['_date'] ?? now()->format('Y-m-d'),
        ]);
    }
}