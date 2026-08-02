<?php

namespace App\Livewire;

use App\Models\FinanceTransaction;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $income = FinanceTransaction::where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $expense = FinanceTransaction::where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        return view('livewire.dashboard', [
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'lowStockProducts' => Product::whereColumn('stock', '<=', 'min_stock')->limit(5)->get(),
        ]);
    }
}
