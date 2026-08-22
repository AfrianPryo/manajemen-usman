<?php

namespace App\Exports\Finance;

use App\Models\FinanceTransaction;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinanceSummarySheetExport implements WithTitle, WithEvents, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Ringkasan Keuangan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $baseQuery = FinanceTransaction::query()
                    ->where('status', 'completed')
                    ->when($this->filters['startDate'] ?? null, fn ($q, $val) => $q->whereDate('transaction_date', '>=', $val))
                    ->when($this->filters['endDate'] ?? null, fn ($q, $val) => $q->whereDate('transaction_date', '<=', $val))
                    ->when($this->filters['unitFilter'] ?? null, fn ($q, $val) => $q->where('unit_id', $val));

                $totalIncome  = (clone $baseQuery)->where('type', 'income')->sum('amount');
                $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
                $netBalance   = $totalIncome - $totalExpense;

                $periodText = ($this->filters['startDate'] ?? null) || ($this->filters['endDate'] ?? null)
                    ? ($this->filters['startDate'] ?? '...') . ' s/d ' . ($this->filters['endDate'] ?? '...')
                    : 'Seluruh Periode';

                $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $sheet->setCellValue('A2', 'Periode');
                $sheet->setCellValue('B2', $periodText);
                $sheet->setCellValue('A3', 'Tanggal Export');
                $sheet->setCellValue('B3', now()->translatedFormat('d F Y H:i'));
                $sheet->getStyle('A2:A3')->getFont()->setItalic(true);

                $sheet->setCellValue('A5', 'Metrik');
                $sheet->setCellValue('B5', 'Nilai');
                $sheet->getStyle('A5:B5')->getFont()->setBold(true);
                $sheet->getStyle('A5:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('059669');
                $sheet->getStyle('A5:B5')->getFont()->getColor()->setARGB('FFFFFFFF');

                $sheet->setCellValue('A6', 'Total Pemasukan');
                $sheet->setCellValue('B6', 'Rp ' . number_format($totalIncome, 0, ',', '.'));

                $sheet->setCellValue('A7', 'Total Pengeluaran');
                $sheet->setCellValue('B7', 'Rp ' . number_format($totalExpense, 0, ',', '.'));

                $sheet->setCellValue('A8', 'Arus Kas Bersih (Net)');
                $sheet->setCellValue('B8', 'Rp ' . number_format($netBalance, 0, ',', '.'));

                $sheet->setCellValue('A9', 'Jumlah Transaksi Selesai');
                $sheet->setCellValueExplicit('B9', (int) (clone $baseQuery)->count(), DataType::TYPE_NUMERIC);
            },
        ];
    }
}