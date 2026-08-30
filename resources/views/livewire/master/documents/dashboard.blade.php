<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    @php
        // Blade ini dipakai bersama oleh Master\Documents\Dashboard DAN
        // Unit\Documents\Dashboard. Nama route generate/history/signature
        // sengaja dibuat simetris di kedua sisi (master.documents.xxx vs
        // unit.documents.xxx) supaya cukup ganti prefix di sini saja.
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
    {{-- Quick Action Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route($docsPrefix.'generate', $docsParams) }}" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-red-300 dark:hover:border-red-800/60 transition-all">
            <span class="p-2 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-500 dark:text-red-400 w-9 h-9 flex items-center justify-center text-lg font-bold">+</span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Buat Dokumen</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Generate dokumen resmi baru dari data sistem.</p>
        </a>

        <a href="{{ route($docsPrefix.'history', $docsParams) }}" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-red-300 dark:hover:border-red-800/60 transition-all">
            <span class="p-2 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-500 dark:text-sky-400 w-9 h-9 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m6 3H9m3-12H9"/></svg>
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Riwayat Dokumen</h3>
            <p class="mt-1 text-[11px] text-neutral-400">{{ number_format($totalDocuments) }} dokumen sudah dibuat.</p>
        </a>

        @unless ($isUnitAdmin)
        <a href="{{ route('master.documents.templates') }}" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-red-300 dark:hover:border-red-800/60 transition-all">
            <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 dark:text-amber-400 w-9 h-9 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Kelola Template</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Atur template Word ber-KOP surat per jenis dokumen.</p>
        </a>
        @endunless

        <a href="{{ route($docsPrefix.'signature', $docsParams) }}" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-red-300 dark:hover:border-red-800/60 transition-all">
            <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 dark:text-emerald-400 w-9 h-9 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Tanda Tangan</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Kelola gambar tanda tangan & jabatan Anda.</p>
        </a>
    </div>

    {{-- Panduan Singkat --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5">
        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-400 mb-4">Alur Membuat Dokumen Resmi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-full bg-neutral-900 dark:bg-slate-700 text-white text-[11px] font-bold flex items-center justify-center">1</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Siapkan Template</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Unggah kop surat (.docx) per jenis dokumen di menu Kelola Template.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-full bg-neutral-900 dark:bg-slate-700 text-white text-[11px] font-bold flex items-center justify-center">2</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Siapkan Tanda Tangan</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Tambahkan profil nama, jabatan, dan gambar tanda tangan Anda.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-full bg-neutral-900 dark:bg-slate-700 text-white text-[11px] font-bold flex items-center justify-center">3</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Buat Dokumen</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Pilih jenis, template, isi data singkat, lalu pilih penanda tangan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-full bg-neutral-900 dark:bg-slate-700 text-white text-[11px] font-bold flex items-center justify-center">4</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Unduh & Arsip</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Dokumen otomatis tercatat di Riwayat Dokumen lengkap nomor suratnya.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Dokumen Terbaru --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] overflow-hidden">
        <div class="p-5 pb-0 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-400">Dokumen Terbaru</h2>
            <a href="{{ route($docsPrefix.'history', $docsParams) }}" class="text-[11px] font-semibold text-blue-900 dark:text-red-400 hover:underline">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-y border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-2.5">Judul Dokumen</th>
                        <th class="px-5 py-2.5">No. Surat</th>
                        <th class="px-5 py-2.5 text-right">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($recentDocuments as $doc)
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-3 text-xs font-semibold text-neutral-900 dark:text-white">{{ $doc->title }}</td>
                            <td class="px-5 py-3 text-[11px] font-mono text-neutral-400">{{ $doc->document_number }}</td>
                            <td class="px-5 py-3 text-[11px] text-neutral-400 text-right whitespace-nowrap">{{ optional($doc->generated_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-12 text-center text-xs text-neutral-400">Belum ada dokumen resmi yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>