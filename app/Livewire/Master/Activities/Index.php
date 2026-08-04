<?php

namespace App\Livewire\Master\Activities;

use App\Models\AuthLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Monitoring Aktivitas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';

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