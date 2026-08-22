<?php

namespace App\Exports\Finance;

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

class CategoryBreakdownSheetExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        return FinanceTransaction::query()
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->where('finance_transactions.status', 'completed')
            ->when($this->filters['startDate'] ?? null, fn ($q, $val) => $q->whereDate('finance_transactions.transaction_date', '>=', $val))
            ->when($this->filters['endDate'] ?? null, fn ($q, $val) => $q->whereDate('finance_transactions.transaction_date', '<=', $val))
            ->when($this->filters['unitFilter'] ?? null, fn ($q, $val) => $q->where('finance_transactions.unit_id', $val))
            ->selectRaw('
                finance_categories.name as category_name,
                finance_transactions.type,
                SUM(finance_transactions.amount) as total_amount,
                COUNT(finance_transactions.id) as trx_count
            ')
            ->groupBy('finance_categories.name', 'finance_transactions.type')
            ->orderBy('finance_transactions.type')
            ->orderByDesc('total_amount');
    }

    public function headings(): array
    {
        return ['Kategori', 'Tipe', 'Total Nominal', 'Jumlah Transaksi'];
    }

    public function map($row): array
    {
        return [
            $row->category_name,
            $row->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            (float) $row->total_amount,
            (int) $row->trx_count,
        ];
    }

    public function columnFormats(): array
    {
        return ['C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function title(): string
    {
        return 'Rincian per Kategori';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
        ];
    }
}