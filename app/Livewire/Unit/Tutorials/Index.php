<?php

namespace App\Livewire\Unit\Tutorials;

use App\Livewire\Master\Tutorials\Index as MasterTutorialsIndex;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

/**
 * Versi Unit dari halaman "Tutorial". Tampilan & logika pemutar dipakai
 * bersama (view livewire.master.tutorials.index); yang berbeda hanya
 * penyaringan materi:
 *
 *   - Hanya kelompok ber-scope 'unit'. Kelompok 'master' tidak pernah sampai
 *     ke halaman ini.
 *   - Kelompok ber-category (mis. 'jasa') hanya muncul bila unit yang SEDANG
 *     DIBUKA berkategori sama. Unit ritel tidak melihat tutorial jasa.
 *   - Materi ber-'categories' (mis. Inventaris -> ['ritel']) ikut disaring
 *     dengan kategori yang sama.
 *
 * Kategori diambil dari unit di ROUTE (trait ScopedToUnit), bukan dari unit
 * milik user login, supaya Master Admin yang memantau unit lain melihat
 * tutorial yang sesuai dengan unit itu -- sama seperti menu sidebar-nya.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Tutorial')]
class Index extends MasterTutorialsIndex
{
    use ScopedToUnit;

    /** Unit yang sedang dibuka, di-cache per request agar tidak query berulang. */
    protected ?Unit $unitMemo = null;

    protected function openedUnit(): ?Unit
    {
        return $this->unitMemo ??= $this->currentUnit();
    }

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless($user && ($user->isMasterAdmin() || $user->isUnitAdmin()), 403);
    }

    protected function sectionAllowed(array $section): bool
    {
        if (($section['scope'] ?? null) !== 'unit') {
            return false;
        }

        $category = $section['category'] ?? null;

        return $category === null || $category === $this->openedUnit()?->category;
    }

    protected function lessonAllowed(array $lesson): bool
    {
        $categories = $lesson['categories'] ?? [];

        return $categories === [] || in_array($this->openedUnit()?->category, $categories, true);
    }

    protected function unitSlug(): ?string
    {
        return $this->openedUnit()?->slug;
    }

    protected function isMasterView(): bool
    {
        return false;
    }
}
