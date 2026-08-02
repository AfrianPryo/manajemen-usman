<?php

namespace App\Livewire\Stock;

use App\Models\StockMovement;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class History extends Component
{
    use WithPagination;

    public string $filterType = '';

    public function render()
    {
        return view('livewire.stock.history', [
            'movements' => StockMovement::query()
                ->with(['product', 'user'])
                ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
                ->latest()
                ->paginate(15),
        ]);
    }
}
