<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public string $type = 'in';
    public string $product_id = '';
    public string $quantity = '';
    public string $note = '';

    public function create(string $type): void
    {
        $this->reset(['product_id', 'quantity', 'note']);
        $this->type = $type;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($this->type === 'out' && $product->stock < $data['quantity']) {
            $this->addError('quantity', 'Stok tidak mencukupi. Sisa stok saat ini: ' . $product->stock);
            return;
        }

        DB::transaction(function () use ($product, $data) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $this->type,
                'quantity' => $data['quantity'],
                'note' => $data['note'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $product->increment('stock', $this->type === 'in' ? $data['quantity'] : -$data['quantity']);
        });

        $this->showModal = false;
        session()->flash('success', $this->type === 'in' ? 'Stok masuk berhasil dicatat.' : 'Stok keluar berhasil dicatat.');
    }

    public function render()
    {
        return view('livewire.stock.index', [
            'products' => Product::orderBy('name')->paginate(10),
            'productOptions' => Product::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
