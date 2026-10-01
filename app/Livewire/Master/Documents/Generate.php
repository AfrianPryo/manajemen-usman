<?php

namespace App\Livewire\Master\Documents;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\DocumentTemplate;
use App\Models\OfficialDocument;
use App\Models\SignatureProfile;
use App\Models\Unit;
use App\Services\Documents\OfficialDocumentGenerator;
use App\Support\DocumentTypes;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    /**
     * Satu "sesi" = satu kali membuka halaman ini (klik kartu Buat Dokumen) dan
     * menghasilkan SATU dokumen di riwayat. Menekan Buat Dokumen / Unduh berulang
     * kali dalam sesi yang sama hanya memperbarui dokumen itu, tidak membuat baris
     * baru. Sesi baru dimulai lagi saat halaman dibuka ulang atau saat jenis
     * dokumen / template diganti (itu dokumen yang berbeda).
     *
     * #[Locked]: nilai ini tidak boleh diubah dari browser (mis. lewat devtools)
     * supaya user tidak bisa menunjuk dokumen milik orang lain.
     */
    #[Locked]
    public ?int $lastGeneratedId = null;

    #[Locked]
    public string $sessionKey = '';

    public function mount(): void
    {
        $this->startNewSession();
    }

    protected function startNewSession(): void
    {
        $this->sessionKey = (string) Str::uuid();
        $this->lastGeneratedId = null;
    }

    protected function sessionCacheKey(): string
    {
        return 'official-doc-session:' . Auth::id() . ':' . $this->sessionKey;
    }

    public function types(): array
    {
        return DocumentTypes::all();
    }

    public function updatedType(): void
    {
        $this->templateId = null;
        $this->startNewSession();
    }

    public function updatedTemplateId(): void
    {
        $this->startNewSession();
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
            'signatureId' => ['required', Rule::exists('signature_profiles', 'id')->where('user_id', Auth::id())],
        ], [], [
            'templateId' => 'template',
            'signatureId' => 'tanda tangan',
        ]);

        $template = DocumentTemplate::findOrFail($this->templateId);
        // Hanya boleh memakai tanda tangan milik user yang sedang login (anti-pemalsuan).
        $signature = SignatureProfile::where('user_id', Auth::id())->findOrFail($this->signatureId);

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

        // Kunci per sesi: klik beruntun (double-click / spam / Enter berulang) diproses
        // satu per satu, sehingga tidak ada dua request yang sama-sama membuat baris baru.
        $lock = Cache::lock('official-doc-generate:' . Auth::id() . ':' . $this->sessionKey, 30);

        try {
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            return;
        }

        try {
            $existing = $this->findSessionDocument($template);

            if ($existing) {
                $document = $generator->regenerate($existing, $template, $params, $signature);
                $isUpdate = true;
            } else {
                $document = $generator->generate($template, $params, $signature, Auth::id());
                $isUpdate = false;
            }

            $this->lastGeneratedId = $document->id;
            Cache::put($this->sessionCacheKey(), $document->id, now()->addHours(6));
        } finally {
            $lock->release();
        }

        AuditLog::record(
            $isUpdate ? 'DOCUMENT_UPDATE' : 'DOCUMENT_GENERATE',
            $document->document_number,
            $isUpdate
                ? "Dokumen resmi '{$document->document_number}' ({$this->type}) diperbarui dari template '{$template->name}' pada sesi yang sama."
                : "Dokumen resmi '{$document->document_number}' ({$this->type}) dibuat dari template '{$template->name}'.",
            null,
            $document->toArray()
        );

        session()->flash('success', $isUpdate
            // Jam disertakan agar toast tetap muncul tiap kali diperbarui (kunci toast di
            // view dibuat dari isi pesan; pesan yang persis sama tidak akan ditampilkan ulang).
            ? "Dokumen resmi nomor {$document->document_number} berhasil diperbarui pukul " . now()->format('H:i:s') . '.'
            : "Dokumen resmi nomor {$document->document_number} berhasil dibuat.");
    }

    /**
     * Dokumen yang sudah dibuat pada sesi ini (bila masih ada, milik user ini, dan
     * memakai template yang sama). Cache dipakai sebagai cadangan untuk request yang
     * datang bersamaan dan belum sempat menerima lastGeneratedId terbaru.
     */
    protected function findSessionDocument(DocumentTemplate $template): ?OfficialDocument
    {
        $id = $this->lastGeneratedId ?? Cache::get($this->sessionCacheKey());

        if (!$id) {
            return null;
        }

        return OfficialDocument::where('generated_by', Auth::id())
            ->where('document_template_id', $template->id)
            ->find($id);
    }

    public function download()
    {
        if (!$this->lastGeneratedId) {
            return;
        }

        // Redam spam tombol Unduh: klik ulang dalam beberapa detik diabaikan.
        if (!Cache::add('official-doc-download:' . Auth::id() . ':' . $this->lastGeneratedId, 1, 5)) {
            return;
        }

        $document = OfficialDocument::where('generated_by', Auth::id())->findOrFail($this->lastGeneratedId);

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