<?php

namespace App\Livewire\Finance;

use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $type = 'income';
    public string $finance_category_id = '';
    public string $amount = '';
    public string $description = '';
    public string $transaction_date = '';

    public string $filterMonth = '';

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
    }

    public function create(string $type = 'income'): void
    {
        $this->reset(['editingId', 'finance_category_id', 'amount', 'description']);
        $this->type = $type;
        $this->transaction_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $trx = FinanceTransaction::findOrFail($id);
        $this->editingId = $trx->id;
        $this->type = $trx->type;
        $this->finance_category_id = (string) $trx->finance_category_id;
        $this->amount = (string) $trx->amount;
        $this->description = $trx->description ?? '';
        $this->transaction_date = $trx->transaction_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'finance_category_id' => 'required|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);
        $data['type'] = $this->type;

        FinanceTransaction::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', 'Transaksi berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        FinanceTransaction::findOrFail($id)->delete();
        session()->flash('success', 'Transaksi berhasil dihapus.');
    }

    public function render()
    {
        $query = FinanceTransaction::query()->with('category');

        if ($this->filterMonth) {
            [$year, $month] = explode('-', $this->filterMonth);
            $query->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month);
        }

        return view('livewire.finance.index', [
            'transactions' => $query->latest('transaction_date')->paginate(10),
            'incomeCategories' => FinanceCategory::where('type', 'income')->pluck('name', 'id'),
            'expenseCategories' => FinanceCategory::where('type', 'expense')->pluck('name', 'id'),
        ]);
    }
}
