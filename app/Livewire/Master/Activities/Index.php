<?php

namespace App\Livewire\Master\Activities;

use App\Exports\AuthLogExport;
use App\Models\AuthLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
#[Title('Monitoring Aktivitas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }

    /**
     * Export seluruh log aktivitas login (mengikuti filter yang sedang aktif).
     */
    public function exportLog()
    {
        return Excel::download(
            new AuthLogExport([
                'search'      => $this->search,
                'eventFilter' => $this->eventFilter,
            ]),
            'log-aktivitas-login-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function render()
    {
        $logs = AuthLog::with('user')
            ->when($this->search, fn ($q) => $q->where('identifier', 'like', "%{$this->search}%")
                                                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.master.activities.index', [
            'logs' => $logs,
        ]);
    }
}