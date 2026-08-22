<?php

namespace App\Exports;

use App\Models\FinanceTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class TransactionExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        return FinanceTransaction::query()
            ->with(['unit', 'category', 'user'])
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['unitFilter'] ?? null, fn ($q, $val) => $q->where('unit_id', $val))
            ->when($this->filters['typeFilter'] ?? null, fn ($q, $val) => $q->where('type', $val))
            ->when($this->filters['statusFilter'] ?? null, fn ($q, $val) => $q->where('status', $val))
            ->when($this->filters['startDate'] ?? null, fn ($q, $val) => $q->whereDate('transaction_date', '>=', $val))
            ->when($this->filters['endDate'] ?? null, fn ($q, $val) => $q->whereDate('transaction_date', '<=', $val))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'No. Referensi',
            'Tanggal',
            'Unit Usaha',
            'Kategori',
            'Tipe',
            'Metode Pembayaran',
            'Status',
            'Nominal',
            'Deskripsi / Catatan',
            'Dicatat Oleh',
            'Ada Bukti?',
        ];
    }

    public function map($tr): array
    {
        return [
            $tr->reference_no ?? '#TX-' . $tr->id,
            optional($tr->transaction_date)->format('Y-m-d'),
            $tr->unit->name ?? '-',
            $tr->category->name ?? 'Umum',
            $tr->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            strtoupper($tr->payment_method ?? 'CASH'),
            match ($tr->status) {
                'completed' => 'Selesai',
                'pending'   => 'Menunggu',
                'cancelled' => 'Dibatalkan',
                default     => $tr->status,
            },
            (float) $tr->amount,
            $tr->description ?? '-',
            $tr->user->name ?? '-',
            $tr->proof_file ? 'Ya' : 'Tidak',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Data Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }
}