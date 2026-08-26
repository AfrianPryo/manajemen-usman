<?php

namespace App\Livewire\Master\Documents;

use App\Models\AuditLog;
use App\Models\OfficialDocument;
use App\Support\DocumentTypes;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Riwayat Dokumen Resmi')]
class History extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';

    public function types(): array
    {
        return DocumentTypes::all();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $documents = OfficialDocument::with(['template', 'unit', 'generatedBy'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('document_number', 'like', "%{$this->search}%")
                        ->orWhere('title', 'like', "%{$this->search}%")
                        ->orWhere('recipient', 'like', "%{$this->search}%");
                });
            })
            ->when($this->typeFilter, fn ($q, $val) => $q->where('type', $val))
            ->latest('generated_at')
            ->paginate(15);

        return view('livewire.master.documents.history', compact('documents'));
    }

    public function download(int $id)
    {
        $document = OfficialDocument::findOrFail($id);

        AuditLog::record(
            'DOCUMENT_DOWNLOAD',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' diunduh dari riwayat.",
            null,
            null
        );

        return Storage::download($document->file_path, $document->document_number . '.docx');
    }

    public function delete(int $id): void
    {
        $document = OfficialDocument::findOrFail($id);
        $oldValues = $document->toArray();

        Storage::delete($document->file_path);
        $document->delete();

        AuditLog::record(
            'DOCUMENT_DELETE',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' dihapus dari riwayat.",
            $oldValues,
            null
        );

        session()->flash('success', 'Dokumen dihapus dari riwayat.');
    }
}