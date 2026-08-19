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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode Produk (Kosongkan jika Auto-Generate)', // Keterangan di Header
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
        $firstUnit = Unit::first();
        $firstCategory = $firstUnit 
            ? Category::where('unit_id', $firstUnit->id)->first()?->name 
            : null;

        return [
            // Sample Baris 1: Diisi Manual
            [
                'PRD-001',
                'Contoh Produk Kode Manual',
                $firstUnit?->name ?? '',
                $firstCategory ?? '',
                10000,
                15000,
                50,
                5,
                'pcs',
                'Isi kode jika punya SKU sendiri',
            ],
            // Sample Baris 2: Dikosongkan (Sistem akan Auto-Generate)
            [
                '', // Biarkan kosong untuk auto generate
                'Contoh Produk Auto Kode',
                $firstUnit?->name ?? '',
                $firstCategory ?? '',
                20000,
                25000,
                100,
                10,
                'pcs',
                'Kosongkan kode jika ingin sistem membuatkan kode otomatis',
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
                    'startColor' => ['rgb' => 'DC2626'],
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

                $optionsSheet = new Worksheet($spreadsheet, 'LookupData');
                $spreadsheet->addSheet($optionsSheet);
                $optionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                $units = Unit::all();

                if ($units->isEmpty()) {
                    $optionsSheet->setCellValue('A1', '-');
                    $optionsSheet->setCellValue('B1', '-');
                    $spreadsheet->addNamedRange(new NamedRange('UNIT_DEFAULT', $optionsSheet, "'LookupData'!\$B\$1:\$B\$1"));
                    $unitCount = 1;
                } else {
                    foreach ($units as $uIndex => $unit) {
                        $optionsSheet->setCellValue('A' . ($uIndex + 1), $unit->name);

                        $categories = Category::where('unit_id', $unit->id)->pluck('name')->toArray();

                        if (empty($categories)) {
                            $categories = ['-'];
                        }

                        $colLetter = Coordinate::stringFromColumnIndex($uIndex + 2);
                        foreach ($categories as $cIndex => $catName) {
                            $optionsSheet->setCellValue($colLetter . ($cIndex + 1), $catName);
                        }

                        $cleanUnitName = 'UNIT_' . preg_replace('/[^A-Za-z0-9_]/', '_', $unit->name);
                        $catCount = count($categories);
                        $rangeStr = "'LookupData'!\${$colLetter}\$1:\${$colLetter}\${$catCount}";
                        
                        $spreadsheet->addNamedRange(new NamedRange($cleanUnitName, $optionsSheet, $rangeStr));
                    }
                    $unitCount = $units->count();
                }

                // Satuan Unit
                $unitTypes = ['pcs', 'box', 'pack', 'kg', 'liter', 'porsi', 'unit', 'lusin'];
                foreach ($unitTypes as $index => $ut) {
                    $optionsSheet->setCellValue('Z' . ($index + 1), $ut);
                }
                $unitTypeCount = count($unitTypes);

                // Validasi Dropdown Baris 2 - 100
                for ($i = 2; $i <= 100; $i++) {
                    // Dropdown Unit Usaha (Kolom C)
                    $valC = $sheet->getCell("C{$i}")->getDataValidation();
                    $valC->setType(DataValidation::TYPE_LIST);
                    $valC->setErrorStyle(DataValidation::STYLE_STOP);
                    $valC->setAllowBlank(true);
                    $valC->setShowDropDown(true);
                    $valC->setFormula1("=LookupData!\$A\$1:\$A\${$unitCount}");

                    // Dropdown Kategori (Kolom D)
                    $valD = $sheet->getCell("D{$i}")->getDataValidation();
                    $valD->setType(DataValidation::TYPE_LIST);
                    $valD->setErrorStyle(DataValidation::STYLE_STOP);
                    $valD->setAllowBlank(true);
                    $valD->setShowDropDown(true);
                    $valD->setFormula1('=INDIRECT("UNIT_" & SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(C' . $i . '," ","_"),".","_"),"-","_"))');

                    // Dropdown Satuan (Kolom I)
                    $valI = $sheet->getCell("I{$i}")->getDataValidation();
                    $valI->setType(DataValidation::TYPE_LIST);
                    $valI->setErrorStyle(DataValidation::STYLE_STOP);
                    $valI->setAllowBlank(true);
                    $valI->setShowDropDown(true);
                    $valI->setFormula1("=LookupData!\$Z\$1:\$Z\${$unitTypeCount}");
                }
            },
        ];
    }
}