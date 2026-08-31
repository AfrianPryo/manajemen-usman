<?php

namespace App\Livewire\Master\Purchasing;

use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Pembelian Lintas-Unit" -- pasangan Master dari App\Livewire\Unit\Purchasing\Index.
 * BERBEDA dengan pasangan lintas-unit lain (mis. Master\ServiceOrder\Index)
 * yang punya form Tambah/Edit sendiri, modul ini SENGAJA read-only: setiap
 * pembelian tetap dicatat dari sisi Unit yang benar-benar berbelanja
 * (transaksi & stok memang harus terkunci ke satu unit), Master Admin di
 * sini hanya MEREKAP -- total belanja per vendor dari SELURUH unit,
 * berguna untuk negosiasi kontrak vendor terpusat.
 *
 * Tidak ada middleware 'unit.category:...' (sama seperti master.customers.index
 * & master.analytics.index): Pembelian berlaku untuk SEMUA kategori unit,
 * bukan cuma 'jasa' seperti Pesanan Layanan.
 */
#[Layout('components.layouts.app')]
#[Title('Pembelian Lintas-Unit')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $unitFilter = '';
    public string $vendorFilter = '';
    public string $statusFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingUnitFilter(): void { $this->resetPage(); }
    public function updatingVendorFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    private function getFilteredQuery()
    {
        return PurchaseOrder::query()
            ->with(['unit', 'vendor'])
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('po_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('unit', fn ($u) => $u->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->unitFilter, fn ($query) => $query->where('unit_id', $this->unitFilter))
            ->when($this->vendorFilter, fn ($query) => $query->where('vendor_id', $this->vendorFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter));
    }

    /**
     * Rekap total belanja per vendor (status 'completed' saja), dipakai
     * untuk kartu "Top Vendor" -- landasan data untuk negosiasi kontrak
     * terpusat yang disebut di deskripsi fitur.
     */
    private function vendorRecap()
    {
        return PurchaseOrder::query()
            ->where('status', 'completed')
            ->select('vendor_id', DB::raw('SUM(total_amount) as total_belanja'), DB::raw('COUNT(*) as jumlah_po'))
            ->groupBy('vendor_id')
            ->orderByDesc('total_belanja')
            ->with('vendor')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $purchases = $this->getFilteredQuery()
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10);

        $completedQuery = PurchaseOrder::where('status', 'completed');

        return view('livewire.master.purchasing.index', [
            'purchases'      => $purchases,
            'units'          => Unit::orderBy('name')->get(),
            'vendors'        => Vendor::orderBy('name')->get(),
            'vendorRecap'    => $this->vendorRecap(),
            'totalBelanja'   => (clone $completedQuery)->sum('total_amount'),
            'totalPo'        => (clone $completedQuery)->count(),
            'totalThisMonth' => (clone $completedQuery)->whereMonth('purchased_at', now()->month)->whereYear('purchased_at', now()->year)->sum('total_amount'),
        ]);
    }
}
