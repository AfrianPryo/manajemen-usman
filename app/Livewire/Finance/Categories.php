<?php

namespace App\Livewire\Finance;

use App\Models\FinanceCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Categories extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'income';

    public function create(): void
    {
        $this->reset(['editingId', 'name']);
        $this->type = 'income';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = FinanceCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
        ]);

        FinanceCategory::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', 'Kategori keuangan berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        FinanceCategory::findOrFail($id)->delete();
        session()->flash('success', 'Kategori keuangan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.finance.categories', [
            'categories' => FinanceCategory::latest()->get(),
        ]);
    }
}
