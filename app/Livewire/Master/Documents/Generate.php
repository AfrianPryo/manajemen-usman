<?php

namespace App\Livewire\Master\Documents;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\DocumentTemplate;
use App\Models\SignatureProfile;
use App\Models\Unit;
use App\Services\Documents\OfficialDocumentGenerator;
use App\Support\DocumentTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Buat Dokumen Resmi')]
class Generate extends Component
{
    public string $type = '';
    public ?int $templateId = null;
    public ?int $signatureId = null;

    // Parameter umum
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?int $unit_id = null;
    public ?string $title = null;
    public ?string $subject = null;
    public ?string $recipient = null;

    // Khusus Surat Keterangan
    public ?string $nama_penerima = null;
    public ?string $jabatan_penerima = null;
    public ?string $nip_penerima = null;
    public ?string $keperluan = null;
    public ?string $isi_keterangan = null;

    // Khusus Berita Acara Serah Terima Aset
    public array $asset_ids = [];
    public ?string $pihak_pertama_nama = null;
    public ?string $pihak_pertama_jabatan = null;
    public ?string $pihak_kedua_nama = null;
    public ?string $pihak_kedua_jabatan = null;

    public ?int $lastGeneratedId = null;

    public function types(): array
    {
        return DocumentTypes::all();
    }

    public function updatedType(): void
    {
        $this->templateId = null;
        $this->lastGeneratedId = null;
    }

    protected function templatesForType(): Collection
    {
        if (!$this->type) {
            return collect();
        }

        return DocumentTemplate::where('type', $this->type)->where('is_active', true)->get();
    }

    public function render()
    {
        return view('livewire.master.documents.generate', [
            'templates' => $this->templatesForType(),
            'units' => Unit::orderBy('name')->get(),
            'assets' => $this->type === DocumentTypes::BERITA_ACARA_ASET ? Asset::orderBy('name')->get() : collect(),
            'signatures' => SignatureProfile::where('user_id', Auth::id())->get(),
        ]);
    }

    public function generate(OfficialDocumentGenerator $generator)
    {
        $this->validate([
            'type' => 'required|string',
            'templateId' => 'required|exists:document_templates,id',
            'signatureId' => 'required|exists:signature_profiles,id',
        ], [], [
            'templateId' => 'template',
            'signatureId' => 'tanda tangan',
        ]);

        $template = DocumentTemplate::findOrFail($this->templateId);
        $signature = SignatureProfile::findOrFail($this->signatureId);

        $params = array_filter([
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'unit_id' => $this->unit_id,
            'title' => $this->title,
            'subject' => $this->subject,
            'recipient' => $this->recipient,
            'nama_penerima' => $this->nama_penerima,
            'jabatan_penerima' => $this->jabatan_penerima,
            'nip_penerima' => $this->nip_penerima,
            'keperluan' => $this->keperluan,
            'isi_keterangan' => $this->isi_keterangan,
            'asset_ids' => $this->asset_ids,
            'pihak_pertama_nama' => $this->pihak_pertama_nama,
            'pihak_pertama_jabatan' => $this->pihak_pertama_jabatan,
            'pihak_kedua_nama' => $this->pihak_kedua_nama,
            'pihak_kedua_jabatan' => $this->pihak_kedua_jabatan,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);

        $document = $generator->generate($template, $params, $signature, Auth::id());

        $this->lastGeneratedId = $document->id;

        AuditLog::record(
            'DOCUMENT_GENERATE',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' ({$this->type}) dibuat dari template '{$template->name}'.",
            null,
            $document->toArray()
        );

        session()->flash('success', "Dokumen resmi nomor {$document->document_number} berhasil dibuat.");
    }

    public function download()
    {
        if (!$this->lastGeneratedId) {
            return;
        }

        $document = \App\Models\OfficialDocument::findOrFail($this->lastGeneratedId);

        $safeFilename = str_replace(['/', '\\'], '-', $document->document_number) . '.docx';

        AuditLog::record(
            'DOCUMENT_DOWNLOAD',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' diunduh.",
            null,
            null
        );

        return Storage::download($document->file_path, $safeFilename);
    }
}