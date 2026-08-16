<?php
namespace App\Exports;

use App\Models\Category;
use App\Models\Product;
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

class ProductTemplateExport implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Unit Usaha',
            'Kategori Produk',
            'Harga Beli',
            'Harga Jual',
            'Stok Initial',
            'Stok Minimal',
            'Satuan Unit',
            'Deskripsi Catatan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'PRD-001',
                'Contoh Produk A',
                Unit::first()?->name ?? 'TEFA',
                Category::first()?->name ?? 'Umum',
                10000,
                15000,
                50,
                5,
                'pcs',
                'Contoh deskripsi produk sampel',
            ]
        ];
    }

    public function title(): string
    {
        return 'Template Import Produk';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'], // Merah (Tema Produk)
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

                // Populate Unit Usaha (Kolom A)
                $units = Unit::pluck('name')->toArray();
                if (empty($units)) { $units = ['TEFA']; }
                foreach ($units as $index => $unit) {
                    $optionsSheet->setCellValue('A' . ($index + 1), $unit);
                }
                $unitCount = count($units);

                // Populate Kategori Produk (Kolom B)
                $categories = Category::pluck('name')->toArray();
                if (empty($categories)) { $categories = ['Umum']; }
                foreach ($categories as $index => $cat) {
                    $optionsSheet->setCellValue('B' . ($index + 1), $cat);
                }
                $catCount = count($categories);

                // Populate Satuan Unit (Kolom C)
                $unitTypes = ['pcs', 'box', 'pack', 'kg', 'liter', 'porsi', 'unit', 'lusin'];
                foreach ($unitTypes as $index => $ut) {
                    $optionsSheet->setCellValue('C' . ($index + 1), $ut);
                }
                $unitTypeCount = count($unitTypes);

                // 2. Terapkan Validasi Dropdown ke Kolom C, D, I (Baris 2 - 100)
                for ($i = 2; $i <= 100; $i++) {
                    // Dropdown Unit Usaha (Kolom C)
                    $valC = $sheet->getCell("C{$i}")->getDataValidation();
                    $valC->setType(DataValidation::TYPE_LIST);
                    $valC->setErrorStyle(DataValidation::STYLE_STOP);
                    $valC->setAllowBlank(true);
                    $valC->setShowDropDown(true);
                    $valC->setFormula1("=LookupData!\$A\$1:\$A\${$unitCount}");

                    // Dropdown Kategori Produk (Kolom D)
                    $valD = $sheet->getCell("D{$i}")->getDataValidation();
                    $valD->setType(DataValidation::TYPE_LIST);
                    $valD->setErrorStyle(DataValidation::STYLE_STOP);
                    $valD->setAllowBlank(true);
                    $valD->setShowDropDown(true);
                    $valD->setFormula1("=LookupData!\$B\$1:\$B\${$catCount}");

                    // Dropdown Satuan Unit (Kolom I)
                    $valI = $sheet->getCell("I{$i}")->getDataValidation();
                    $valI->setType(DataValidation::TYPE_LIST);
                    $valI->setErrorStyle(DataValidation::STYLE_STOP);
                    $valI->setAllowBlank(true);
                    $valI->setShowDropDown(true);
                    $valI->setFormula1("=LookupData!\$C\$1:\$C\${$unitTypeCount}");
                }
            },
        ];
    }
}