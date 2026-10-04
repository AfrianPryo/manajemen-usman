{{--
    Halaman Tutorial -- view BERSAMA untuk Master & Unit
    (App\Livewire\Master\Tutorials\Index & App\Livewire\Unit\Tutorials\Index),
    pola yang sama dengan view Dokumen Resmi.

    Progres "materi selesai" disimpan di localStorage browser (per akun), jadi
    tidak butuh tabel database. Data materi sudah disaring di server sesuai
    hak akses; view ini hanya menampilkan apa yang dikirim.
--}}
<div class="w-full max-w-[1670px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans"
     x-data="{
        key: @js($storageKey),
        ids: @js($allIds),
        done: [],
        init() {
            try { this.done = JSON.parse(localStorage.getItem(this.key) || '[]'); } catch (e) { this.done = []; }
        },
        mark(id) {
            if (this.done.includes(id)) return;
            this.done.push(id);
            try { localStorage.setItem(this.key, JSON.stringify(this.done)); } catch (e) {}
        },
        unmark(id) {
            this.done = this.done.filter(i => i !== id);
            try { localStorage.setItem(this.key, JSON.stringify(this.done)); } catch (e) {}
        },
        countDone(list) { return list.filter(i => this.done.includes(i)).length; },
        get doneCount() { return this.ids.filter(i => this.done.includes(i)).length; },
        get pct() { return this.ids.length ? Math.round(this.doneCount / this.ids.length * 100) : 0; },
        toTop() { this.$nextTick(() => document.getElementById('tutorial-player')?.scrollIntoView({ behavior: 'smooth', block: 'start' })); },
     }">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Tutorial</h1>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">
                @if ($isMaster)
                    Video panduan untuk Admin Master, Admin Unit, dan dashboard Jasa.
                @else
                    Video panduan singkat untuk mengelola unit usaha Anda.
                @endif
            </p>
        </div>

        @if ($total > 0)
            <div class="w-full md:w-64 shrink-0">
                <div class="flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400 mb-1.5">
                    <span>Progres belajar</span>
                    <span class="font-semibold text-neutral-700 dark:text-neutral-200"><span x-text="doneCount"></span> dari {{ $total }} materi</span>
                </div>
                <div class="h-1.5 bg-neutral-100 dark:bg-slate-700 rounded-full overflow-hidden"
                     role="progressbar" aria-label="Progres belajar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="pct">
                    <div class="h-full bg-blue-900 dark:bg-sky-500 rounded-full transition-all duration-300" :style="`width: ${pct}%`"></div>
                </div>
            </div>
        @endif
    </div>

    @if ($total > 0)
        <div x-show="doneCount === ids.length" x-cloak style="display: none;"
             class="flex items-center gap-2 px-4 py-3 text-xs font-semibold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-sm">
            <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
            Selamat, semua materi sudah selesai. Anda bisa mengulang materi mana pun kapan saja.
        </div>
    @endif

    @if (! $current)
        {{-- ================= KOSONG ================= --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 px-5 py-14 text-center">
            <x-heroicon-o-play-circle stroke-width="1.5" class="w-8 h-8 mx-auto text-neutral-300 dark:text-neutral-600" />
            <p class="mt-3 text-sm font-semibold text-neutral-700 dark:text-neutral-200">Belum ada tutorial untuk akun ini</p>
            <p class="mt-1 text-xs text-neutral-400">Tutorial akan muncul di sini setelah ditambahkan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-[19rem_minmax(0,1fr)] gap-4 items-start">

            {{-- ================= DAFTAR MATERI ================= --}}
            <aside class="order-2 lg:order-1 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02] lg:sticky lg:top-0">
                <div class="px-4 py-3.5 border-b border-neutral-100 dark:border-slate-700">
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Daftar materi</h2>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Materi {{ $index + 1 }} dari {{ $total }}</p>
                </div>

                <nav class="max-h-[calc(100vh-14rem)] overflow-y-auto no-scrollbar py-2" aria-label="Daftar materi tutorial">
                    @php $no = 0; @endphp
                    @foreach ($sections as $section)
                        <div wire:key="tut-section-{{ $section['id'] }}" class="pb-2">
                            <div class="px-4 pt-2.5 pb-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $section['title'] }}</p>
                                    <span class="text-[10px] font-semibold text-neutral-400 normal-case tracking-normal"
                                          x-text="countDone(@js(collect($section['lessons'])->pluck('id')->all())) + '/{{ count($section['lessons']) }}'"></span>
                                </div>
                                @if (! empty($section['summary']))
                                    <p class="text-[11px] text-neutral-400/90 mt-0.5 normal-case">{{ $section['summary'] }}</p>
                                @endif
                            </div>

                            <ul class="px-2 space-y-0.5">
                                @foreach ($section['lessons'] as $lesson)
                                    @php
                                        $no++;
                                        $active = $lesson['id'] === $current['id'];
                                    @endphp
                                    <li wire:key="tut-lesson-{{ $lesson['id'] }}">
                                        <button type="button"
                                                wire:click="select(@js($lesson['id']))"
                                                @click="toTop()"
                                                @if ($active) aria-current="true" @endif
                                                class="w-full flex items-start gap-2.5 px-2.5 py-2 rounded-sm text-left transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30
                                                       {{ $active
                                                            ? 'bg-blue-50 dark:bg-blue-950/40'
                                                            : 'hover:bg-neutral-50 dark:hover:bg-slate-700/40' }}">

                                            {{-- Nomor / centang selesai --}}
                                            <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center text-[10px] font-bold border transition-colors
                                                         {{ $active
                                                              ? 'bg-blue-900 dark:bg-sky-500 border-blue-900 dark:border-sky-500 text-white'
                                                              : 'border-neutral-200 dark:border-slate-600 text-neutral-500 dark:text-neutral-400' }}"
                                                  :class="done.includes(@js($lesson['id'])) && '!bg-emerald-600 !border-emerald-600 !text-white'">
                                                <span x-show="!done.includes(@js($lesson['id']))">{{ $no }}</span>
                                                <span x-show="done.includes(@js($lesson['id']))" x-cloak style="display: none;" class="flex"><x-heroicon-s-check class="w-3 h-3" /></span>
                                            </span>

                                            <span class="min-w-0">
                                                <span class="block text-[12px] leading-snug {{ $active ? 'font-semibold text-blue-900 dark:text-sky-300' : 'font-medium text-neutral-700 dark:text-neutral-200' }}">{{ $lesson['title'] }}</span>
                                                <span class="flex items-center gap-1 mt-0.5 text-[11px] text-neutral-400">
                                                    <x-heroicon-o-play-circle class="w-3 h-3" />
                                                    Video{{ ! empty($lesson['duration']) ? ' · ' . $lesson['duration'] : '' }}
                                                </span>
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>
            </aside>

            {{-- ================= PEMUTAR & ISI MATERI ================= --}}
            <section id="tutorial-player" wire:key="tut-player-{{ $current['id'] }}"
                     class="order-1 lg:order-2 min-w-0 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02] scroll-mt-2"
                     x-data="{ showTranscript: false }">

                {{-- Bar atas: posisi materi + navigasi --}}
                <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-neutral-100 dark:border-slate-700">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 min-w-0 truncate">
                        Materi <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $index + 1 }}</span> dari {{ $total }}
                    </p>

                    <div class="flex items-center gap-2 shrink-0">
                        @if ($prevId)
                            <button type="button" wire:click="select(@js($prevId))" @click="toTop()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-slate-700 hover:bg-neutral-50 dark:hover:bg-slate-700/50 rounded-sm transition-all cursor-pointer">
                                <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </button>
                        @endif

                        @if ($nextId)
                            <button type="button" wire:click="select(@js($nextId))" @click="mark(@js($current['id'])); toTop()"
                                    class="inline-flex items-center gap-1 px-4 py-1.5 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                                <span>Berikutnya</span>
                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                            </button>
                        @else
                            <button type="button" @click="mark(@js($current['id']))"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                                <x-heroicon-o-check class="w-3.5 h-3.5" />
                                <span x-text="done.includes(@js($current['id'])) ? 'Sudah selesai' : 'Tandai selesai'"></span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Video --}}
                <div class="p-3 sm:p-5 pb-0 sm:pb-0">
                    <div class="aspect-video w-full bg-neutral-900 rounded-sm overflow-hidden">
                        @if ($video && $video['kind'] === 'iframe')
                            <iframe src="{{ $video['src'] }}"
                                    title="{{ $current['title'] }}"
                                    class="w-full h-full"
                                    loading="lazy"
                                    allow="accelerometer; encrypted-media; picture-in-picture; fullscreen"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen></iframe>
                        @elseif ($video && $video['kind'] === 'video')
                            <video src="{{ $video['src'] }}" class="w-full h-full" controls preload="metadata" controlsList="nodownload"></video>
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-center px-6">
                                <x-heroicon-o-play-circle stroke-width="1.25" class="w-12 h-12 text-neutral-600" />
                                <p class="mt-3 text-sm font-semibold text-neutral-200">Video segera hadir</p>
                                <p class="mt-1 text-xs text-neutral-400 max-w-sm">
                                    @if ($isMaster)
                                        Isi kolom <code class="text-neutral-300">video</code> materi ini di <code class="text-neutral-300">config/tutorials.php</code>.
                                    @else
                                        Video untuk materi ini sedang disiapkan.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Isi materi --}}
                <div class="p-4 sm:p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-neutral-900 dark:text-white leading-snug">{{ $current['title'] }}</h2>
                            @if (! empty($current['summary']))
                                <p class="mt-1.5 text-[13px] leading-relaxed text-neutral-600 dark:text-neutral-300 max-w-2xl">{{ $current['summary'] }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button"
                                    @click="done.includes(@js($current['id'])) ? unmark(@js($current['id'])) : mark(@js($current['id']))"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold rounded-sm transition-all cursor-pointer whitespace-nowrap"
                                    :class="done.includes(@js($current['id']))
                                        ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30'
                                        : 'text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-slate-700/60 hover:bg-blue-50 dark:hover:bg-blue-950/40'">
                                <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                <span x-text="done.includes(@js($current['id'])) ? 'Selesai' : 'Tandai selesai'"></span>
                            </button>

                            @if (! empty($current['transcript']))
                                <button type="button" @click="showTranscript = !showTranscript"
                                        :aria-expanded="showTranscript ? 'true' : 'false'"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-800 dark:hover:text-sky-400 bg-neutral-100 dark:bg-slate-700/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-sm transition-all cursor-pointer whitespace-nowrap">
                                    <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                    <span x-text="showTranscript ? 'Sembunyikan transkrip' : 'Tampilkan transkrip'"></span>
                                </button>
                            @endif

                            @if ($pageUrl)
                                <a wire:navigate href="{{ $pageUrl }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-950/70 rounded-sm transition-all whitespace-nowrap">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    Buka halaman terkait
                                </a>
                            @endif
                        </div>
                    </div>

                    @if (! empty($current['steps']))
                        <div class="border-t border-neutral-100 dark:border-slate-700 pt-4">
                            <h3 class="text-xs font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Yang akan dipelajari</h3>
                            <ul class="space-y-1.5">
                                @foreach ($current['steps'] as $step)
                                    <li class="flex items-start gap-2 text-[12px] text-neutral-600 dark:text-neutral-300">
                                        <x-heroicon-o-check-circle class="w-4 h-4 mt-px shrink-0 text-neutral-300 dark:text-neutral-600" />
                                        <span>{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($current['transcript']))
                        <div x-show="showTranscript" x-cloak style="display: none;"
                             class="border-t border-neutral-100 dark:border-slate-700 pt-4">
                            <h3 class="text-xs font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Transkrip</h3>
                            <div class="text-[12px] leading-relaxed text-neutral-600 dark:text-neutral-300 whitespace-pre-line max-w-2xl">{{ $current['transcript'] }}</div>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif
</div>