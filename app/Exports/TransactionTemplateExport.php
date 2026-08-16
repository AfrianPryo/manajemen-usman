<?php
namespace App\Exports;

use App\Models\FinanceCategory;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionTemplateExport implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Tanggal (YYYY-MM-DD)',
            'Unit Usaha',
            'Kategori Transaksi',
            'Tipe (income/expense)',
            'Metode Pembayaran',
            'Nominal',
            'Deskripsi / Catatan',
        ];
    }

    public function array(): array
    {
        return [
            [
                date('Y-m-d'),
                Unit::first()?->name ?? 'TEFA',
                FinanceCategory::first()?->name ?? 'Penjualan Produk',
                'income',
                'cash',
                150000,
                'Contoh: Penjualan Produk A',
            ]
        ];
    }

    public function title(): string
    {
        return 'Template Import Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $spreadsheet = $event->sheet->getParent();
                $sheet = $event->sheet->getDelegate();

                // 1. Buat sheet bantuan khusus untuk data dropdown
                $optionsSheet = new Worksheet($spreadsheet, 'LookupData');
                $spreadsheet->addSheet($optionsSheet);
                $optionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Populate Unit Usaha
                $units = Unit::pluck('name')->toArray();
                if (empty($units)) { $units = ['TEFA']; }
                foreach ($units as $index => $unit) {
                    $optionsSheet->setCellValue('A' . ($index + 1), $unit);
                }
                $unitCount = count($units);

                // Populate Kategori
                $categories = FinanceCategory::pluck('name')->toArray();
                if (empty($categories)) { $categories = ['Penjualan Produk']; }
                foreach ($categories as $index => $cat) {
                    $optionsSheet->setCellValue('B' . ($index + 1), $cat);
                }
                $catCount = count($categories);

                // Populate Tipe
                $types = ['income', 'expense'];
                foreach ($types as $index => $type) {
                    $optionsSheet->setCellValue('C' . ($index + 1), $type);
                }

                // Populate Metode Pembayaran
                $payments = ['cash', 'transfer', 'qris'];
                foreach ($payments as $index => $pm) {
                    $optionsSheet->setCellValue('D' . ($index + 1), $pm);
                }

                // 2. Terapkan Validasi Dropdown ke Kolom B, C, D, E (Baris 2 - 100)
                for ($i = 2; $i <= 100; $i++) {
                    // Dropdown Unit Usaha (Kolom B)
                    $valB = $sheet->getCell("B{$i}")->getDataValidation();
                    $valB->setType(DataValidation::TYPE_LIST);
                    $valB->setErrorStyle(DataValidation::STYLE_STOP);
                    $valB->setAllowBlank(true);
                    $valB->setShowDropDown(true);
                    $valB->setFormula1("=LookupData!\$A\$1:\$A\${$unitCount}"); // Ditambahkan tanda '='

                    // Dropdown Kategori Transaksi (Kolom C)
                    $valC = $sheet->getCell("C{$i}")->getDataValidation();
                    $valC->setType(DataValidation::TYPE_LIST);
                    $valC->setErrorStyle(DataValidation::STYLE_STOP);
                    $valC->setAllowBlank(true);
                    $valC->setShowDropDown(true);
                    $valC->setFormula1("=LookupData!\$B\$1:\$B\${$catCount}"); // Ditambahkan tanda '='

                    // Dropdown Tipe (Kolom D)
                    $valD = $sheet->getCell("D{$i}")->getDataValidation();
                    $valD->setType(DataValidation::TYPE_LIST);
                    $valD->setErrorStyle(DataValidation::STYLE_STOP);
                    $valD->setShowDropDown(true);
                    $valD->setFormula1("=LookupData!\$C\$1:\$C\$2"); // Ditambahkan tanda '='

                    // Dropdown Metode Pembayaran (Kolom E)
                    $valE = $sheet->getCell("E{$i}")->getDataValidation();
                    $valE->setType(DataValidation::TYPE_LIST);
                    $valE->setErrorStyle(DataValidation::STYLE_STOP);
                    $valE->setShowDropDown(true);
                    $valE->setFormula1("=LookupData!\$D\$1:\$D\$3"); // Ditambahkan tanda '='
                }
            },
        ];
    }
}