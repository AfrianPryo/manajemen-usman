<?php

namespace App\Exports\Dashboard;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    protected function val(string $key): int|float
    {
        return (int) ($this->summary[$key] ?? 0);
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

                $sheet->setCellValue('A1', 'RINGKASAN DASHBOARD MASTER ADMIN');

                $sheet->setCellValue('A2', 'Periode Omzet');
                $sheet->setCellValue('B2', $this->periodLabel);

                $sheet->setCellValue('A3', 'Tanggal Export');
                $sheet->setCellValue('B3', now()->translatedFormat('d F Y H:i'));

                $sheet->setCellValue('A5', 'Metrik');
                $sheet->setCellValue('B5', 'Nilai');

                $sheet->setCellValue('A6', 'Total Omzet (' . $this->periodLabel . ')');
                $sheet->setCellValue('B6', 'Rp ' . number_format($this->val('totalRevenue'), 0, ',', '.'));

                $sheet->setCellValue('A7', 'Total Unit Usaha');
                $sheet->setCellValueExplicit('B7', $this->val('totalUnits'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A8', 'Unit Usaha Aktif');
                $sheet->setCellValueExplicit('B8', $this->val('activeUnits'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A9', 'Unit Usaha Nonaktif');
                $sheet->setCellValueExplicit('B9', $this->val('inactiveUnits'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A10', 'Total Admin');
                $sheet->setCellValueExplicit('B10', $this->val('totalAdmins'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A11', 'Total Transaksi Periode Ini');
                $sheet->setCellValueExplicit('B11', $this->val('totalTransactions'), DataType::TYPE_NUMERIC);

                $sheet->setCellValue('A12', 'Rata-rata Nominal per Transaksi');
                $sheet->setCellValue('B12', 'Rp ' . number_format($this->summary['avgTransactionValue'] ?? 0, 0, ',', '.'));

                $sheet->setCellValue('A13', 'Status Sistem');
                $sheet->setCellValue('B13', $this->val('inactiveUnits') == 0 ? 'Optimal' : 'Perlu Perhatian');

                $sheet->setCellValue('A15', 'Catatan');
                $sheet->setCellValue('A16', 'Rincian lengkap per item (Unit Usaha, Admin, Transaksi, Kontribusi Omzet) tersedia pada sheet/tab masing-masing di bagian bawah file ini.');
                $sheet->mergeCells('A16:H16');
                $sheet->getStyle('A16')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A15')->getFont()->setBold(true)->setItalic(true);

                // Styling
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $sheet->getStyle('A5:B5')->getFont()->setBold(true);
                $sheet->getStyle('A5:B5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('DC2626');
                $sheet->getStyle('A5:B5')->getFont()->getColor()->setARGB('FFFFFFFF');

                $sheet->getStyle('A2:A3')->getFont()->setItalic(true);
            },
        ];
    }
}