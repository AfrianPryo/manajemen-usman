<?php

namespace App\Exports\Dashboard;

use App\Models\FinanceTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class RecentTransactionsSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected int $limit = 6;

    public function query(): Builder
    {
        return FinanceTransaction::query()
            ->with(['unit', 'category', 'user'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit($this->limit);
    }

    public function headings(): array
    {
        return [
            'No. Referensi', 'Tanggal', 'Unit Usaha', 'Kategori', 'Tipe',
            'Metode Pembayaran', 'Status', 'Nominal', 'Deskripsi / Catatan',
            'Dicatat Oleh', 'Ada Bukti?',
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
        return ['H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function title(): string
    {
        return 'Transaksi Terkini';
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