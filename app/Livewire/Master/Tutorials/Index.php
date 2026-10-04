<?php

namespace App\Livewire\Master\Tutorials;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman "Tutorial" -- daftar materi video bergaya kursus: daftar materi di
 * kiri, pemutar video di kanan, tombol Sebelumnya/Berikutnya, dan penanda
 * progres.
 *
 * Isi tutorial dibaca dari config/tutorials.php (tanpa tabel database baru).
 *
 * HAK AKSES -- diputuskan di SERVER (bukan cuma disembunyikan di tampilan):
 *   - Master Admin : melihat SEMUA kelompok (Master, Unit, Tambahan Jasa).
 *   - Unit Admin   : lihat Unit\Tutorials\Index -- hanya kelompok scope 'unit'
 *                    yang cocok dengan kategori unitnya. Materi lain tidak ikut
 *                    dikirim ke browser, dan ?materi=<id lain> otomatis jatuh
 *                    ke materi pertama yang boleh dilihat.
 *
 * Unit\Tutorials\Index mewarisi class ini dan hanya mengganti aturan
 * penyaringan -- pola yang sama dengan Unit\Documents\Dashboard.
 */
#[Layout('components.layouts.app')]
#[Title('Tutorial')]
class Index extends Component
{
    /** Id materi yang sedang dibuka (?materi=...). Id tak dikenal diabaikan. */
    #[Url(as: 'materi', history: true)]
    public string $lesson = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);
    }

    public function select(string $id): void
    {
        $this->lesson = $id;
    }

    // ------------------------------------------------------------------
    // Aturan penyaringan (di-override oleh Unit\Tutorials\Index)
    // ------------------------------------------------------------------

    protected function sectionAllowed(array $section): bool
    {
        return true;
    }

    protected function lessonAllowed(array $lesson): bool
    {
        return true;
    }

    /** Slug unit untuk tombol "Buka halaman" ke route unit.* (null = tidak ada). */
    protected function unitSlug(): ?string
    {
        return null;
    }

    protected function isMasterView(): bool
    {
        return true;
    }

    // ------------------------------------------------------------------

    /** @return array<int, array> kelompok + materi yang boleh dilihat user ini */
    protected function visibleSections(): array
    {
        $sections = [];

        foreach ((array) config('tutorials.sections', []) as $section) {
            if (! $this->sectionAllowed($section)) {
                continue;
            }

            $lessons = array_values(array_filter(
                $section['lessons'] ?? [],
                fn ($lesson) => $this->lessonAllowed($lesson)
            ));

            if ($lessons === []) {
                continue;
            }

            $section['lessons'] = $lessons;
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * Ubah link video jadi sumber yang aman diputar. Hanya YouTube, Google
     * Drive, atau file video langsung yang diterima; selain itu dianggap
     * belum ada video.
     *
     * @return array{kind: string, src: string}|null
     */
    protected function embed(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^(www\.|m\.)/', '', $host);
        $path = (string) parse_url($url, PHP_URL_PATH);

        // YouTube
        if (in_array($host, ['youtube.com', 'youtube-nocookie.com', 'youtu.be'], true)) {
            $id = null;
            if ($host === 'youtu.be') {
                $id = ltrim($path, '/');
            } elseif (preg_match('#^/(embed|shorts|live)/([\w-]+)#', $path, $m)) {
                $id = $m[2];
            } else {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
                $id = $q['v'] ?? null;
            }

            if ($id && preg_match('/^[\w-]{6,20}$/', $id)) {
                return [
                    'kind' => 'iframe',
                    'src'  => 'https://www.youtube-nocookie.com/embed/' . $id . '?rel=0&modestbranding=1',
                ];
            }

            return null;
        }

        // Google Drive
        if ($host === 'drive.google.com' && preg_match('#/file/d/([\w-]+)#', $path, $m)) {
            return ['kind' => 'iframe', 'src' => 'https://drive.google.com/file/d/' . $m[1] . '/preview'];
        }

        // File video langsung (URL penuh atau path di storage publik)
        if (preg_match('/\.(mp4|webm|ogg)$/i', $path)) {
            $src = preg_match('#^https?://#i', $url) ? $url : asset('storage/' . ltrim($url, '/'));

            return ['kind' => 'video', 'src' => $src];
        }

        return null;
    }

    /** URL tombol "Buka halaman" (null bila route tidak ada / tidak bisa dibuka dari sini). */
    protected function pageUrl(?string $routeName): ?string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return null;
        }

        if (str_starts_with($routeName, 'unit.')) {
            $slug = $this->unitSlug();

            return $slug ? route($routeName, ['unit' => $slug]) : null;
        }

        return route($routeName);
    }

    public function render()
    {
        $sections = $this->visibleSections();

        $flat = collect($sections)
            ->flatMap(fn ($s) => collect($s['lessons'])->map(fn ($l) => $l + ['section_id' => $s['id']]))
            ->values();

        $index = $flat->search(fn ($l) => $l['id'] === $this->lesson);
        $index = $index === false ? 0 : $index;

        $current = $flat->get($index);

        return view('livewire.master.tutorials.index', [
            'sections'    => $sections,
            'total'       => $flat->count(),
            'allIds'      => $flat->pluck('id')->all(),
            'index'       => $index,
            'current'     => $current,
            'prevId'      => $flat->get($index - 1)['id'] ?? null,
            'nextId'      => $flat->get($index + 1)['id'] ?? null,
            'video'       => $current ? $this->embed($current['video'] ?? null) : null,
            'pageUrl'     => $current ? $this->pageUrl($current['route'] ?? null) : null,
            'isMaster'    => $this->isMasterView(),
            'storageKey'  => 'usman-tutorial-' . (Auth::id() ?? 0) . '-' . ($this->isMasterView() ? 'master' : 'unit'),
        ]);
    }
}
