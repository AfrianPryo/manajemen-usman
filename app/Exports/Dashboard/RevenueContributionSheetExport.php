<?php

namespace App\Exports\Dashboard;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RevenueContributionSheetExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected array $revenueContribution;
    protected string $periodLabel;

    public function __construct(array $revenueContribution, string $periodLabel)
    {
        $this->revenueContribution = $revenueContribution;
        $this->periodLabel = $periodLabel;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->revenueContribution['labels'] as $index => $label) {
            $count = $this->revenueContribution['counts'][$index] ?? 0;
            $avg   = $this->revenueContribution['averages'][$index] ?? 0;
            $first = $this->revenueContribution['firstDates'][$index] ?? null;
            $last  = $this->revenueContribution['lastDates'][$index] ?? null;

            $rows[] = [
                $index + 1, // Peringkat
                $label,
                (float) $this->revenueContribution['series'][$index],
                (int) $count,
                (float) $avg,
                $first ? Carbon::parse($first)->format('Y-m-d') : '-',
                $last ? Carbon::parse($last)->format('Y-m-d') : '-',
                $this->revenueContribution['percentages'][$index] . '%',
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Peringkat',
            'Unit Usaha',
            'Total Omzet (' . $this->periodLabel . ')',
            'Jumlah Transaksi',
            'Rata-rata Nominal / Transaksi',
            'Transaksi Pertama',
            'Transaksi Terakhir',
            'Kontribusi (%)',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Kontribusi Omzet';
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