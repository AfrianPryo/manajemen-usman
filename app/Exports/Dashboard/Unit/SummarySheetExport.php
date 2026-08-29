<?php

namespace App\Exports\Dashboard\Unit;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SummarySheetExport implements WithTitle, WithEvents, ShouldAutoSize
{
    protected array $summary;
    protected string $periodLabel;

    public function __construct(array $summary, string $periodLabel)
    {
        $this->summary = $summary;
        $this->periodLabel = $periodLabel;
    }

    protected function num(string $key): int|float
    {
        return $this->summary[$key] ?? 0;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'RINGKASAN DASHBOARD UNIT: ' . strtoupper($this->summary['unitName'] ?? '-'));

                $sheet->setCellValue('A2', 'Periode Omzet');
                $sheet->setCellValue('B2', $this->periodLabel);

                $sheet->setCellValue('A3', 'Tanggal Export');
                $sheet->setCellValue('B3', now()->translatedFormat('d F Y H:i'));

                $sheet->setCellValue('A5', 'Metrik');
                $sheet->setCellValue('B5', 'Nilai');

                $sheet->setCellValue('A6', 'Total Pemasukan (' . $this->periodLabel . ')');
                $sheet->setCellValue('B6', 'Rp ' . number_format($this->num('totalIncome'), 0, ',', '.'));

                $sheet->setCellValue('A7', 'Total Pengeluaran (' . $this->periodLabel . ')');
                $sheet->setCellValue('B7', 'Rp ' . number_format($this->num('totalExpense'), 0, ',', '.'));

                $sheet->setCellValue('A8', 'Omzet Bersih');
                $sheet->setCellValue('B8', 'Rp ' . number_format($this->num('netRevenue'), 0, ',', '.'));

                $sheet->setCellValue('A9', 'Jumlah Transaksi');
                $sheet->setCellValueExplicit('B9', $this->num('trxCount'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A10', 'Total Produk');
                $sheet->setCellValueExplicit('B10', $this->num('totalProducts'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A11', 'Produk Stok Menipis');
                $sheet->setCellValueExplicit('B11', $this->num('lowStockCount'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A13', 'Catatan');
                $sheet->setCellValue('A14', 'Rincian transaksi dan produk stok menipis tersedia pada sheet/tab masing-masing di bagian bawah file ini.');
                $sheet->mergeCells('A14:H14');
                $sheet->getStyle('A14')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A13')->getFont()->setBold(true)->setItalic(true);

                // Styling
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $sheet->getStyle('A5:B5')->getFont()->setBold(true);
                $sheet->getStyle('A5:B5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('2563EB');
                $sheet->getStyle('A5:B5')->getFont()->getColor()->setARGB('FFFFFFFF');

                $sheet->getStyle('A2:A3')->getFont()->setItalic(true);
            },
        ];
    }
}
