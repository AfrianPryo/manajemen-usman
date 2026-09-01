<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    @php
        // PERBAIKAN: sebelumnya dicek dari ROLE user (hasRole('unit-admin')),
        // yang salah saat Master Admin memantau (membuka) halaman Dokumen
        // Resmi milik sebuah unit -- Master Admin tidak punya role unit-admin,
        // jadi kondisi ini akan salah menganggap dia sedang di halaman Master
        // sendiri. Konteks yang benar adalah ROUTE yang sedang aktif, bukan
        // role -- karena itu dicek dari request()->routeIs('unit.*').
        $isUnitAdmin = request()->routeIs('unit.*');
        $docsPrefix = $isUnitAdmin ? 'unit.documents.' : 'master.documents.';
        // Slug diambil dari unit yang SEDANG DIBUKA (route-model-binding
        // {unit:slug}), bukan dari unit milik user login -- supaya tetap
        // benar saat dibuka Master Admin yang sedang memantau unit lain.
        $docsParams = $isUnitAdmin ? ['unit' => request()->route('unit')?->slug] : [];
    @endphp

    <a href="{{ route($docsPrefix.'index', $docsParams) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-neutral-500 hover:text-blue-900 dark:hover:text-red-400 transition-colors">
        <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
        Kembali ke Menu Laporan
    </a>

    <div>
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Riwayat Dokumen Resmi</h1>
        <p class="text-xs text-neutral-400 mt-1">Semua dokumen resmi yang pernah dibuat, lengkap dengan nomor surat dan jejak data sumbernya.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nomor surat / judul / penerima..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>
            <div>
                <select wire:model.live="typeFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Jenis</option>
                    @foreach ($this->types() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">No. Surat</th>
                        <th class="px-4 py-3.5">Jenis</th>
                        <th class="px-4 py-3.5">Judul</th>
                        <th class="px-4 py-3.5">Ditandatangani Oleh</th>
                        <th class="px-4 py-3.5">Dibuat</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($documents as $doc)
                        <tr wire:key="doc-{{ $doc->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 font-mono text-[11px] text-neutral-400">{{ $doc->document_number }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                    {{ \App\Support\DocumentTypes::label($doc->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs font-semibold text-neutral-900 dark:text-white">{{ $doc->title }}</td>
                            <td class="px-4 py-3.5 text-xs text-neutral-600 dark:text-neutral-300">{{ $doc->signed_by_name }} <span class="text-neutral-400">({{ $doc->signed_by_position }})</span></td>
                            <td class="px-4 py-3.5 text-[11px] text-neutral-400 whitespace-nowrap">{{ optional($doc->generated_at)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="download({{ $doc->id }})" class="p-1.5 text-sky-600 hover:text-sky-800 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-md transition-all" title="Unduh">
                                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                    </button>
                                    <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                            message: 'Yakin hapus dokumen ini dari riwayat?',
                                            confirmText: 'Ya, Hapus',
                                            onConfirm: () => $wire.delete({{ $doc->id }})
                                        })" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-400">Belum ada dokumen resmi yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if ($documents->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $documents->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $documents->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $documents->total() }}</span> total dokumen
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if ($documents->currentPage() > 1)
                    <button wire:click="gotoPage(1)" class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all">
                        <x-heroicon-o-chevron-double-left class="w-3.5 h-3.5" />
                        Kembali ke Awal
                    </button>
                @endif
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>