<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\Generate as MasterGenerate;
use App\Models\Asset;
use App\Services\Documents\OfficialDocumentGenerator;
use App\Support\DocumentTypes;
use App\Models\SignatureProfile;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Buat Dokumen Resmi".
 *
 * Reuse: semua property form, method save/validate, dan proses generate
 * dokumen dari class induk (Master\Documents\Generate) dipakai apa adanya —
 * TIDAK di-override, supaya logic penomoran & pembuatan dokumen tetap
 * satu sumber kebenaran.
 *
 * Yang diubah:
 * 1. unit_id dikunci saat mount(), tidak bisa diganti user dari form.
 * 2. render() tidak mengirim daftar seluruh unit (props 'units' dihapus).
 * 3. Daftar aset untuk jenis dokumen "Berita Acara Aset" difilter ke aset
 *    milik unit sendiri (assets.unit_id), dan asset_ids disaring ulang di
 *    generate() sebelum dokumen dibuat.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Generate extends MasterGenerate
{
    use ScopedToUnit;

    public function mount(): void
    {
        // Kunci unit_id ke unit milik user yang sedang login.
        // Form tidak menampilkan dropdown pemilihan unit untuk role ini
        // (lihat guard @role di blade generate.blade.php).
        $this->unit_id = $this->currentUnitId();
    }

    public function render()
    {
        return view('livewire.master.documents.generate', [
            'templates'  => $this->templatesForType(),
            // Hanya aset milik unit ini (assets.unit_id sudah tersedia).
            'assets'     => $this->type === DocumentTypes::BERITA_ACARA_ASET
                ? Asset::where('unit_id', $this->currentUnitId())->orderBy('name')->get()
                : collect(),
            'signatures' => SignatureProfile::where('user_id', Auth::id())->get(),
            // 'units' sengaja tidak dikirim: blade menyembunyikan
            // dropdown unit untuk role unit-admin (lihat catatan blade).
        ]);
    }

    /**
     * Kunci ulang unit_id & saring asset_ids ke aset milik unit ini sebelum
     * dokumen dibuat, supaya request yang dimanipulasi tidak bisa memasukkan
     * aset unit lain ke Berita Acara.
     */
    public function generate(OfficialDocumentGenerator $generator)
    {
        $this->unit_id = $this->currentUnitId();

        $this->asset_ids = Asset::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->asset_ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return parent::generate($generator);
    }

    /**
     * Jaga-jaga: kalau ada percobaan mengubah unit_id lewat request
     * manipulation (mis. lewat browser devtools), paksa balik ke unit
     * milik user login sebelum tervalidasi & tersimpan ke dokumen.
     */
    public function updatedUnitId(): void
    {
        $this->unit_id = $this->currentUnitId();
    }
}