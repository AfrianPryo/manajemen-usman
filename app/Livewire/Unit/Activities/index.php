<?php

namespace App\Livewire\Unit\Activities;

use App\Livewire\Master\Activities\Index as MasterActivitiesIndex;
use App\Models\AuthLog;
use Illuminate\Support\Facades\Auth;

/**
 * Versi Unit dari monitoring aktivitas login.
 *
 * AuthLog tidak punya kolom unit_id langsung, jadi scoping dilakukan
 * lewat relasi: whereHas('user', ...) ke unit milik user yang sedang login.
 * Kalau nanti dibutuhkan, method exportLog() (diwarisi dari class induk)
 * SEBAIKNYA juga dibatasi filter unit ini agar file Excel yang diunduh
 * admin unit tidak berisi log unit lain — AuthLogExport::class perlu
 * menerima parameter unit_id tambahan untuk itu (belum ada isinya di
 * project ini, jadi saya tandai sebagai TODO, bukan saya asumsikan).
 */
class Index extends MasterActivitiesIndex
{
    public function render()
    {
        $unitId = Auth::user()->unit_id;

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