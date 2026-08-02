<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $category_id = '';
    public string $code = '';
    public string $name = '';
    public string $unit = 'pcs';
    public string $purchase_price = '';
    public string $selling_price = '';
    public string $stock = '0';
    public string $min_stock = '5';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset([
            'editingId', 'category_id', 'code', 'name', 'unit',
            'purchase_price', 'selling_price', 'stock', 'min_stock',
        ]);
        $this->unit = 'pcs';
        $this->stock = '0';
        $this->min_stock = '5';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $product->id;
        $this->category_id = (string) $product->category_id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->unit = $product->unit;
        $this->purchase_price = (string) $product->purchase_price;
        $this->selling_price = (string) $product->selling_price;
        $this->stock = (string) $product->stock;
        $this->min_stock = (string) $product->min_stock;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:50|unique:products,code,' . $this->editingId,
            'name' => 'required|string|max:150',
            'unit' => 'required|string|max:20',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        Product::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', $this->editingId ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.');
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('success', 'Produk berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.products.index', [
            'products' => Product::query()
                ->with('category')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
