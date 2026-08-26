<?php

namespace App\Livewire\Master\Documents;

use App\Models\OfficialDocument;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dokumen Resmi')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.master.documents.dashboard', [
            'totalDocuments' => OfficialDocument::count(),
            'recentDocuments' => OfficialDocument::latest('generated_at')->limit(5)->get(),
        ]);
    }
}
