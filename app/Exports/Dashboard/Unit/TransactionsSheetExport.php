<?php

namespace App\Exports\Dashboard\Unit;

use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected int $unitId;
    protected Carbon $start;
    protected Carbon $end;

    public function __construct(int $unitId, Carbon $start, Carbon $end)
    {
        $this->unitId = $unitId;
        $this->start = $start;
        $this->end = $end;
    }

    public function query(): Builder
    {
        return FinanceTransaction::query()
            ->with(['category', 'user'])
            ->where('unit_id', $this->unitId)
            ->whereBetween('transaction_date', [$this->start, $this->end])
            ->latest('transaction_date')
            ->latest('id');
    }

    public function headings(): array
    {
        return [
            'No. Referensi', 'Tanggal', 'Kategori', 'Tipe',
            'Metode Pembayaran', 'Status', 'Nominal', 'Deskripsi / Catatan',
            'Dicatat Oleh', 'Ada Bukti?',
        ];
    }

    public function map($tr): array
    {
        return [
            $tr->reference_no ?? '#TX-' . $tr->id,
            optional($tr->transaction_date)->format('Y-m-d'),
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
        return ['G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function title(): string
    {
        return 'Transaksi';
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
