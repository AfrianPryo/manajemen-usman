<?php

namespace App\Livewire\Master\Inventory;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Monitoring Inventaris')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $unitFilter = null;
    public string $stockFilter = ''; // low, out, normal

    public function render()
    {
        $products = Product::with(['unit', 'category'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', 0))
            ->when($this->stockFilter === 'low', fn ($q) => $q->where('stock', '>', 0)->where('stock', '<=', 10))
            ->when($this->stockFilter === 'normal', fn ($q) => $q->where('stock', '>', 10))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.master.inventory.index', [
            'products' => $products,
            'units' => \App\Models\Unit::orderBy('name')->get(),
        ]);
    }
}