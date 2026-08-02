<?php

namespace App\Exports;

use App\Models\FinanceTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinanceReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected string $month)
    {
    }

    public function collection()
    {
        [$year, $monthNum] = explode('-', $this->month);

        return FinanceTransaction::with('category')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $monthNum)
            ->orderBy('transaction_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kategori', 'Tipe', 'Jumlah', 'Keterangan'];
    }

    public function map($trx): array
    {
        return [
            $trx->transaction_date->format('d-m-Y'),
            $trx->category->name,
            $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            $trx->amount,
            $trx->description ?? '-',
        ];
    }
}
