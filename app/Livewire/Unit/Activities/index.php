<?php

namespace App\Livewire\Unit\Activities;

use App\Livewire\Master\Activities\Index as MasterActivitiesIndex;
use App\Models\AuthLog;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari monitoring aktivitas login.
 *
 * AuthLog tidak punya kolom unit_id langsung, jadi scoping dilakukan
 * lewat relasi: whereHas('user', ...) ke unit yang sedang dibuka
 * (lihat ScopedToUnit::currentUnitId() -- diambil dari route, bukan
 * dari user yang sedang login, supaya tetap benar saat Master Admin
 * memantau unit lain). Kalau nanti dibutuhkan, method exportLog() (diwarisi dari class induk)
 * SEBAIKNYA juga dibatasi filter unit ini agar file Excel yang diunduh
 * admin unit tidak berisi log unit lain — AuthLogExport::class perlu
 * menerima parameter unit_id tambahan untuk itu (belum ada isinya di
 * project ini, jadi saya tandai sebagai TODO, bukan saya asumsikan).
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterActivitiesIndex
{
    use ScopedToUnit;

    public function render()
    {
        $unitId = $this->currentUnitId();

        $logs = AuthLog::with('user')
            ->whereHas('user', fn ($q) => $q->where('unit_id', $unitId))
            ->when($this->search, fn ($q) => $q->where('identifier', 'like', "%{$this->search}%")
                                                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.master.activities.index', [
            'logs' => $logs,
        ]);
    }

    // TODO: override exportLog() setelah AuthLogExport::class mendukung
    // filter unit_id, supaya export Excel di sisi Unit juga ter-scope.
}