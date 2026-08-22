<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetTemplateExport implements FromArray, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Tag Aset (Kosongkan jika Auto-Generate)',
            'Nama Aset',
            'Kategori',
            'Nomor Seri',
            'Tanggal Pembelian (YYYY-MM-DD)',
            'Harga Beli',
            'Status',
            'Kondisi',
            'Ditugaskan Kepada',
            'Lokasi',
            'Catatan',
        ];
    }

    public function array(): array
    {
        return [
            // Sample Baris 1: Tag diisi manual
            [
                'AST-001',
                'Contoh Laptop Kantor',
                'Elektronik',
                'SN-XXXX-0001',
                date('Y-m-d'),
                7500000,
                'available',
                'good',
                '',
                'Kantor Pusat',
                'Isi tag jika punya kode aset sendiri',
            ],
            // Sample Baris 2: Tag dikosongkan (Sistem Auto-Generate)
            [
                '',
                'Contoh Meja Kerja',
                'Furniture',
                '',
                date('Y-m-d'),
                1200000,
                'assigned',
                'good',
                'Budi Santoso',
                'Ruang HRD',
                'Kosongkan tag jika ingin sistem membuatkan otomatis',
            ],
        ];
    }

    public function title(): string
    {
        return 'Template Import Aset';
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $spreadsheet = $event->sheet->getParent();
                $sheet = $event->sheet->getDelegate();

                // 1. Sheet bantuan untuk data dropdown
                $optionsSheet = new Worksheet($spreadsheet, 'LookupData');
                $spreadsheet->addSheet($optionsSheet);
                $optionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Populate Kategori (gabungan data eksisting + daftar default)
                $categories = Asset::query()->whereNotNull('category')->distinct()->pluck('category')->toArray();
                $defaultCategories = ['Elektronik', 'Furniture', 'Kendaraan', 'Peralatan', 'Lainnya'];
                $categories = array_values(array_unique(array_merge($categories, $defaultCategories)));
                if (empty($categories)) { $categories = ['Elektronik']; }
                foreach ($categories as $index => $cat) {
                    $optionsSheet->setCellValue('A' . ($index + 1), $cat);
                }
                $catCount = count($categories);

                // Populate Status
                $statuses = ['available', 'assigned', 'maintenance', 'retired'];
                foreach ($statuses as $index => $s) {
                    $optionsSheet->setCellValue('B' . ($index + 1), $s);
                }
                $statusCount = count($statuses);

                // Populate Kondisi
                $conditions = ['good', 'fair', 'damaged'];
                foreach ($conditions as $index => $c) {
                    $optionsSheet->setCellValue('C' . ($index + 1), $c);
                }
                $conditionCount = count($conditions);

                // 2. Validasi Dropdown Kolom C (Kategori), G (Status), H (Kondisi) — Baris 2-100
                for ($i = 2; $i <= 100; $i++) {
                    $valC = $sheet->getCell("C{$i}")->getDataValidation();
                    $valC->setType(DataValidation::TYPE_LIST);
                    $valC->setErrorStyle(DataValidation::STYLE_STOP);
                    $valC->setAllowBlank(true);
                    $valC->setShowDropDown(true);
                    $valC->setFormula1("=LookupData!\$A\$1:\$A\${$catCount}");

                    $valG = $sheet->getCell("G{$i}")->getDataValidation();
                    $valG->setType(DataValidation::TYPE_LIST);
                    $valG->setErrorStyle(DataValidation::STYLE_STOP);
                    $valG->setAllowBlank(true);
                    $valG->setShowDropDown(true);
                    $valG->setFormula1("=LookupData!\$B\$1:\$B\${$statusCount}");

                    $valH = $sheet->getCell("H{$i}")->getDataValidation();
                    $valH->setType(DataValidation::TYPE_LIST);
                    $valH->setErrorStyle(DataValidation::STYLE_STOP);
                    $valH->setAllowBlank(true);
                    $valH->setShowDropDown(true);
                    $valH->setFormula1("=LookupData!\$C\$1:\$C\${$conditionCount}");
                }
            },
        ];
    }
}