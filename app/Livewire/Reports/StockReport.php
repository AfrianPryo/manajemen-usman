<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StockReport extends Component
{
    public function render()
    {
        return view('livewire.reports.stock', [
            'products' => Product::with('category')->orderBy('name')->get(),
        ]);
    }
}
