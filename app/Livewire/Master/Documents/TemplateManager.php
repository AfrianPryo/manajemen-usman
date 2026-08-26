<?php

namespace App\Livewire\Master\Documents;

use App\Models\AuditLog;
use App\Models\DocumentTemplate;
use App\Services\Documents\Providers\AssetHandoverDataProvider;
use App\Services\Documents\Providers\ConsolidatedReportDataProvider;
use App\Services\Documents\Providers\FinanceReportDataProvider;
use App\Services\Documents\Providers\SuratKeteranganDataProvider;
use App\Support\DocumentTypes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Template Dokumen Resmi')]
class TemplateManager extends Component
{
    use WithFileUploads;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $type = '';
    public string $name = '';
    public ?string $description = '';
    public $templateFile;
    public string $numbering_format = '{nomor}/UN/TEFA/{bulan_romawi}/{tahun}';
    public string $numbering_reset = 'yearly';

    public function types(): array
    {
        return DocumentTypes::all();
    }

    public function render()
    {
        return view('livewire.master.documents.template-manager', [
            'templates' => DocumentTemplate::latest()->paginate(10),
            'placeholderHelp' => $this->type ? $this->placeholderHelp($this->type) : [],
        ]);
    }

    public function create(): void
    {
        $this->reset(['editingId', 'type', 'name', 'description', 'templateFile']);
        $this->numbering_format = '{nomor}/UN/TEFA/{bulan_romawi}/{tahun}';
        $this->numbering_reset = 'yearly';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $template = DocumentTemplate::findOrFail($id);
        $this->editingId = $template->id;
        $this->type = $template->type;
        $this->name = $template->name;
        $this->description = $template->description;
        $this->numbering_format = $template->numbering_format;
        $this->numbering_reset = $template->numbering_reset;
        $this->templateFile = null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'type' => 'required|string',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'numbering_format' => 'required|string|max:100',
            'numbering_reset' => 'required|in:yearly,monthly,never',
            'templateFile' => ($this->editingId ? 'nullable' : 'required') . '|file|mimes:docx',
        ];

        $this->validate($rules);

        $isUpdate = (bool) $this->editingId;
        $oldValues = $isUpdate ? DocumentTemplate::find($this->editingId)?->toArray() : null;

        $data = [
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'numbering_format' => $this->numbering_format,
            'numbering_reset' => $this->numbering_reset,
            'placeholders' => $this->placeholderHelp($this->type),
        ];

        if ($this->templateFile) {
            $data['file_path'] = $this->templateFile->store('document-templates');
        }

        if ($this->editingId) {
            $template = DocumentTemplate::findOrFail($this->editingId);
            $template->update($data);

            AuditLog::record(
                'DOCUMENT_TEMPLATE_UPDATE',
                $template->name,
                "Template dokumen '{$template->name}' diperbarui.",
                $oldValues,
                $template->fresh()->toArray()
            );

            session()->flash('success', 'Template berhasil diperbarui.');
        } else {
            $data['created_by'] = Auth::id();
            $data['is_active'] = true;
            $template = DocumentTemplate::create($data);

            AuditLog::record(
                'DOCUMENT_TEMPLATE_CREATE',
                $template->name,
                "Template dokumen baru '{$template->name}' ditambahkan.",
                null,
                $template->toArray()
            );

            session()->flash('success', 'Template berhasil ditambahkan.');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'type', 'name', 'description', 'templateFile']);
    }

    public function toggleActive(int $id): void
    {
        $template = DocumentTemplate::findOrFail($id);
        $oldStatus = $template->is_active;
        $template->update(['is_active' => !$template->is_active]);

        AuditLog::record(
            'DOCUMENT_TEMPLATE_TOGGLE_ACTIVE',
            $template->name,
            "Status template '{$template->name}' diubah menjadi " . ($template->is_active ? 'Aktif' : 'Nonaktif') . '.',
            ['is_active' => $oldStatus],
            ['is_active' => $template->is_active]
        );
    }

    public function delete(int $id): void
    {
        $template = DocumentTemplate::findOrFail($id);

        if ($template->officialDocuments()->exists()) {
            session()->flash('error', 'Template tidak bisa dihapus karena sudah pernah dipakai membuat dokumen. Nonaktifkan saja.');
            return;
        }

        $oldValues = $template->toArray();

        if ($template->file_path) {
            Storage::delete($template->file_path);
        }

        $template->delete();

        AuditLog::record(
            'DOCUMENT_TEMPLATE_DELETE',
            $template->name,
            "Template dokumen '{$template->name}' dihapus.",
            $oldValues,
            null
        );

        session()->flash('success', 'Template dihapus.');
    }

    public function placeholderHelp(string $type): array
    {
        return match ($type) {
            DocumentTypes::FINANCE_REPORT => (new FinanceReportDataProvider())->availablePlaceholders(),
            DocumentTypes::BERITA_ACARA_ASET => (new AssetHandoverDataProvider())->availablePlaceholders(),
            DocumentTypes::SURAT_KETERANGAN => (new SuratKeteranganDataProvider())->availablePlaceholders(),
            DocumentTypes::LAPORAN_KONSOLIDASI => (new ConsolidatedReportDataProvider())->availablePlaceholders(),
            default => [],
        };
    }
}