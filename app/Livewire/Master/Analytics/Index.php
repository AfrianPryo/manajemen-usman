<?php

namespace App\Livewire\Master\Analytics;

use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Analytics & Statistik')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.master.analytics.index', [
            'totalUnits' => Unit::count(),
            'totalProducts' => Product::count(),
            'totalTransactions' => FinanceTransaction::count(),
            'totalRevenue' => FinanceTransaction::where('type', 'income')->sum('amount'),
            'totalExpense' => FinanceTransaction::where('type', 'expense')->sum('amount'),
        ]);
    }
}