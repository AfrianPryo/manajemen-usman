<?php

namespace App\Services\Documents\Providers;

use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Services\Documents\Contracts\DocumentDataProviderInterface;
use Carbon\Carbon;

class ConsolidatedReportDataProvider implements DocumentDataProviderInterface
{
    public function build(array $params): array
    {
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        $units = Unit::orderBy('name')->get();

        $rows = $units->values()->map(function ($unit, $i) use ($startDate, $endDate) {
            $baseQuery = fn () => FinanceTransaction::where('unit_id', $unit->id)
                ->where('status', 'completed')
                ->when($startDate, fn ($q, $v) => $q->whereDate('transaction_date', '>=', $v))
                ->when($endDate, fn ($q, $v) => $q->whereDate('transaction_date', '<=', $v));

            $income = (float) $baseQuery()->where('type', 'income')->sum('amount');
            $expense = (float) $baseQuery()->where('type', 'expense')->sum('amount');

            return [
                'no' => $i + 1,
                'unit' => $unit->name,
                'status' => $unit->is_active ? 'Aktif' : 'Nonaktif',
                'pemasukan' => 'Rp ' . number_format($income, 0, ',', '.'),
                'pengeluaran' => 'Rp ' . number_format($expense, 0, ',', '.'),
                'net' => 'Rp ' . number_format($income - $expense, 0, ',', '.'),
            ];
        })->toArray();

        return [
            'periode' => $this->periodLabel($startDate, $endDate),
            'jumlah_unit' => (string) $units->count(),
            'rows' => $rows,
        ];
    }

    protected function periodLabel(?string $start, ?string $end): string
    {
        if (!$start && !$end) {
            return 'Seluruh Periode';
        }

        $s = $start ? Carbon::parse($start)->translatedFormat('d F Y') : '...';
        $e = $end ? Carbon::parse($end)->translatedFormat('d F Y') : '...';

        return "{$s} s/d {$e}";
    }

    public function availablePlaceholders(): array
    {
        return [
            'periode',
            'jumlah_unit',
            'rows (tabel berulang)' => ['no', 'unit', 'status', 'pemasukan', 'pengeluaran', 'net'],
        ];
    }
}
