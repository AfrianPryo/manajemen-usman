<?php

namespace App\Imports;

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AssetsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    private array $validCategories;
    private array $validStatuses = ['available', 'assigned', 'maintenance', 'retired'];
    private array $validConditions = ['good', 'fair', 'damaged'];

    public function __construct()
    {
        // Kategori valid: gabungan dari data eksisting + daftar default (agar fleksibel)
        $existing = Asset::query()->whereNotNull('category')->distinct()->pluck('category')->toArray();
        $default  = ['Elektronik', 'Furniture', 'Kendaraan', 'Peralatan', 'Lainnya'];
        $this->validCategories = array_values(array_unique(array_merge($existing, $default)));
    }

    /**
     * Pre-processing data sebelum validasi (Normalisasi Input)
     */
    public function prepareForValidation(array $data, int $index): array
    {
        // Normalisasi Tag Aset: tangani nama header baik 'tag_aset' maupun versi panjangnya
        $data['tag_aset'] = trim((string) (
            $data['tag_aset']
            ?? $data['tag_aset_kosongkan_jika_auto_generate']
            ?? ''
        ));

        if (isset($data['status'])) {
            $data['status'] = strtolower(trim((string) $data['status'])) ?: 'available';
        }

        if (isset($data['kondisi'])) {
            $data['kondisi'] = strtolower(trim((string) $data['kondisi'])) ?: 'good';
        }

        return $data;
    }

    /**
     * Mengecek apakah baris dianggap kosong total
     */
    public function isEmpty(array $row): bool
    {
        $nama     = trim((string) ($row['nama_aset'] ?? ''));
        $kategori = trim((string) ($row['kategori'] ?? ''));

        return empty($nama) && empty($kategori);
    }

    public function model(array $row)
    {
        if ($this->isEmpty($row)) {
            return null;
        }

        // Auto-Generate Tag Aset jika dikosongkan
        $tag = trim((string) ($row['tag_aset'] ?? ''));
        if (empty($tag)) {
            do {
                $tag = 'AST-' . strtoupper(Str::random(6));
            } while (Asset::where('asset_tag', $tag)->exists());
        }

        // Parsing tanggal (mendukung serial number Excel & format string)
        $rawDate = $row['tanggal_pembelian_yyyy_mm_dd'] ?? null;
        $date = null;
        if (!empty($rawDate)) {
            try {
                $date = is_numeric($rawDate)
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d')
                    : Carbon::parse($rawDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $date = null;
            }
        }

        // Simpan (Update jika tag sudah ada, Create jika baru)
        return Asset::updateOrCreate(
            ['asset_tag' => $tag],
            [
                'name'           => trim((string) ($row['nama_aset'] ?? '')),
                'category'       => trim((string) ($row['kategori'] ?? '')),
                'serial_number'  => trim((string) ($row['nomor_seri'] ?? '')) ?: null,
                'purchase_date'  => $date,
                'purchase_cost'  => (float) ($row['harga_beli'] ?? 0),
                'status'         => $row['status'] ?? 'available',
                'condition'      => $row['kondisi'] ?? 'good',
                'assigned_to'    => trim((string) ($row['ditugaskan_kepada'] ?? '')) ?: null,
                'location'       => trim((string) ($row['lokasi'] ?? '')) ?: null,
                'notes'          => $row['catatan'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        $fields = 'tag_aset_kosongkan_jika_auto_generate,nama_aset,kategori,nomor_seri,tanggal_pembelian_yyyy_mm_dd,harga_beli,status,kondisi,ditugaskan_kepada,lokasi,catatan';

        return [
            'nama_aset'      => ['nullable', "required_with:{$fields}", 'string'],
            'kategori'       => ['nullable', "required_with:{$fields}", Rule::in($this->validCategories)],
            'harga_beli'     => ['nullable', 'numeric', 'min:0'],
            'status'         => ['nullable', Rule::in($this->validStatuses)],
            'kondisi'        => ['nullable', Rule::in($this->validConditions)],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'required_with' => 'Kolom ini wajib diisi jika data aset dimasukkan.',
            'numeric'       => 'Harus berupa angka.',
            'min'           => 'Nilai minimal tidak boleh kurang dari 0.',
            'kategori.in'   => 'Kategori tidak dikenali / tidak sesuai opsi dropdown.',
            'status.in'     => 'Status harus salah satu dari: available, assigned, maintenance, retired.',
            'kondisi.in'    => 'Kondisi harus salah satu dari: good, fair, damaged.',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'tag_aset'                     => 'Tag Aset',
            'nama_aset'                    => 'Nama Aset',
            'kategori'                     => 'Kategori',
            'nomor_seri'                   => 'Nomor Seri',
            'tanggal_pembelian_yyyy_mm_dd' => 'Tanggal Pembelian',
            'harga_beli'                   => 'Harga Beli',
            'status'                       => 'Status',
            'kondisi'                      => 'Kondisi',
            'ditugaskan_kepada'            => 'Ditugaskan Kepada',
            'lokasi'                       => 'Lokasi',
            'catatan'                      => 'Catatan',
        ];
    }
}