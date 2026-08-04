<?php

namespace App\Livewire\Master\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Manajemen Admin')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::with(['unit', 'roles'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('nip', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.master.users.index', [
            'users' => $users,
        ]);
    }
}