<?php

namespace App\Livewire\Master\Transactions;

use App\Models\FinanceTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Monitoring Transaksi')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $unitFilter = null;
    public string $typeFilter = '';

    public function render()
    {
        $transactions = FinanceTransaction::with(['unit', 'category', 'user'])
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest('date')
            ->paginate(15);

        return view('livewire.master.transactions.index', [
            'transactions' => $transactions,
            'units' => \App\Models\Unit::orderBy('name')->get(),
        ]);
    }
}