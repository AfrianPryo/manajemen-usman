<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\History as MasterHistory;
use App\Models\AuditLog;
use App\Models\OfficialDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Versi Unit dari riwayat dokumen resmi.
 *
 * PENTING: download() dan delete() di-override bukan cuma soal tampilan,
 * tapi soal keamanan. Versi Master pakai findOrFail(id) tanpa filter unit,
 * yang berarti kalau class Master dipakai langsung oleh Unit, seorang
 * admin unit bisa saja mengunduh/menghapus dokumen unit LAIN cuma dengan
 * menebak/mengubah ID di request (IDOR). Karena itu setiap query di sini
 * WAJIB ->where('unit_id', ...) sebelum findOrFail().
 */
class History extends MasterHistory
{
    public function render()
    {
        $unitId = Auth::user()->unit_id;

        $documents = OfficialDocument::with(['template', 'unit', 'generatedBy'])
            ->where('unit_id', $unitId)
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
        $document = OfficialDocument::where('unit_id', Auth::user()->unit_id)
            ->findOrFail($id);

        AuditLog::record(
            'DOCUMENT_DOWNLOAD',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' diunduh dari riwayat unit.",
            null,
            null
        );

        return Storage::download($document->file_path, $document->document_number . '.docx');
    }

    public function delete(int $id): void
    {
        $document = OfficialDocument::where('unit_id', Auth::user()->unit_id)
            ->findOrFail($id);

        $oldValues = $document->toArray();

        Storage::delete($document->file_path);
        $document->delete();

        AuditLog::record(
            'DOCUMENT_DELETE',
            $document->document_number,
            "Dokumen resmi '{$document->document_number}' dihapus dari riwayat unit.",
            $oldValues,
            null
        );

        session()->flash('success', 'Dokumen dihapus dari riwayat.');
    }
}