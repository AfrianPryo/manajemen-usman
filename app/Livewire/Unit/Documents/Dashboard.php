<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\Dashboard as MasterDocumentsDashboard;
use App\Models\OfficialDocument;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari halaman menu "Dokumen Resmi" (kartu Buat/Riwayat/Tanda Tangan).
 *
 * CATATAN PERBAIKAN: file ini sebelumnya ada di lokasi yang salah secara
 * struktur — namespace-nya tertulis `App\Livewire` (bukan
 * `App\Livewire\Unit\Documents`) dan isinya adalah dashboard ringkasan lama
 * yang tidak berhubungan sama sekali dengan menu Dokumen Resmi. Karena
 * namespace tidak cocok dengan path filenya, class itu tidak bisa
 * ter-autoload dengan benar oleh Composer (PSR-4) — jadi route yang
 * mengarah ke sini pasti error. Ditulis ulang total mengikuti pola
 * Unit\Documents\History / Generate yang sudah benar.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Dashboard extends MasterDocumentsDashboard
{
    use ScopedToUnit;

    public function render()
    {
        $unitId = $this->currentUnitId();

        return view('livewire.master.documents.dashboard', [
            'totalDocuments' => OfficialDocument::where('unit_id', $unitId)->count(),
            'recentDocuments' => OfficialDocument::where('unit_id', $unitId)
                ->latest('generated_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
