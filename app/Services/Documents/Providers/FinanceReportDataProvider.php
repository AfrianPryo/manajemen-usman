<?php

namespace App\Services\Documents\Providers;

use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Services\Documents\Contracts\DocumentDataProviderInterface;
use Carbon\Carbon;

class FinanceReportDataProvider implements DocumentDataProviderInterface
{
    public function build(array $params): array
    {
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;
        $unitId = $params['unit_id'] ?? null;

        $transactions = FinanceTransaction::query()
            ->with(['unit', 'category'])
            ->where('status', 'completed')
            ->when($startDate, fn ($q, $v) => $q->whereDate('transaction_date', '>=', $v))
            ->when($endDate, fn ($q, $v) => $q->whereDate('transaction_date', '<=', $v))
            ->when($unitId, fn ($q, $v) => $q->where('unit_id', $v))
            ->orderBy('transaction_date')
            ->get();

        $totalIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $totalExpense = (float) $transactions->where('type', 'expense')->sum('amount');

        $rows = $transactions->values()->map(fn ($tr, $i) => [
            'no' => $i + 1,
            'tanggal' => optional($tr->transaction_date)->format('d-m-Y') ?? '-',
            'unit' => $tr->unit->name ?? '-',
            'kategori' => $tr->category->name ?? 'Umum',
            'tipe' => $tr->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            'nominal' => 'Rp ' . number_format($tr->amount, 0, ',', '.'),
            'keterangan' => $tr->description ?? '-',
        ])->toArray();

        $unit = $unitId ? Unit::find($unitId) : null;

        return [
            'unit_usaha' => $unit->name ?? 'Seluruh Unit Usaha',
            'periode' => $this->periodLabel($startDate, $endDate),
            'total_pemasukan' => 'Rp ' . number_format($totalIncome, 0, ',', '.'),
            'total_pengeluaran' => 'Rp ' . number_format($totalExpense, 0, ',', '.'),
            'laba_bersih' => 'Rp ' . number_format($totalIncome - $totalExpense, 0, ',', '.'),
            'jumlah_transaksi' => (string) $transactions->count(),
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
            'unit_usaha',
            'periode',
            'total_pemasukan',
            'total_pengeluaran',
            'laba_bersih',
            'jumlah_transaksi',
            'rows (tabel berulang)' => ['no', 'tanggal', 'unit', 'kategori', 'tipe', 'nominal', 'keterangan'],
        ];
    }
}
