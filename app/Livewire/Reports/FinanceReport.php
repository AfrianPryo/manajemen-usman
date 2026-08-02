<?php

namespace App\Livewire\Reports;

use App\Models\FinanceTransaction;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class FinanceReport extends Component
{
    public string $month;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        [$year, $monthNum] = explode('-', $this->month);

        $transactions = FinanceTransaction::with('category')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $monthNum)
            ->orderBy('transaction_date')
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');

        return view('livewire.reports.finance', [
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ]);
    }
}
