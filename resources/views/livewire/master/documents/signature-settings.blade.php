<div class="w-full max-w-3xl mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

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
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke Menu Laporan
    </a>

    <div>
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Pengaturan Tanda Tangan</h1>
        <p class="text-xs text-neutral-400 mt-1">Gambar ini akan ditempel otomatis oleh sistem ke setiap dokumen resmi yang Anda buat.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-400">{{ $editingId ? 'Edit Profil' : 'Tambah Profil Tanda Tangan' }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Tercetak</label>
                <input type="text" wire:model="name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                @error('name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jabatan</label>
                <input type="text" wire:model="position" placeholder="Contoh: Kepala TEFA" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                @error('position') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Gambar Tanda Tangan</label>
            <input type="file" wire:model="signatureImage" accept="image/*" class="w-full text-xs border border-neutral-200 dark:border-slate-700 rounded-md p-1.5 bg-neutral-50 dark:bg-slate-900 text-neutral-800 dark:text-neutral-200">
            <p class="text-[11px] text-neutral-400 mt-1">Gunakan PNG transparan agar hasil di dokumen lebih rapi. Maks. 1 MB.</p>
            <div wire:loading wire:target="signatureImage" class="text-[11px] text-amber-600 mt-1">Mengunggah...</div>
            @error('signatureImage') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
            @if ($signatureImage)
                <img src="{{ $signatureImage->temporaryUrl() }}" class="h-16 mt-2 border border-neutral-200 dark:border-slate-700 rounded-md p-1 bg-white">
            @endif
        </div>

        <label class="flex items-center gap-2 text-xs font-medium text-neutral-600 dark:text-neutral-300">
            <input type="checkbox" wire:model="is_default" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20">
            Jadikan tanda tangan default
        </label>

        <div class="flex justify-end gap-2 pt-2 border-t border-neutral-100 dark:border-slate-700">
            @if ($editingId)
                <button wire:click="$set('editingId', null)" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-full hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all">Batal</button>
            @endif
            <button wire:click="save" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-full transition-all shadow-sm shadow-blue-900/20">Simpan</button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Tanda Tangan</th>
                        <th class="px-4 py-3.5">Nama</th>
                        <th class="px-4 py-3.5">Jabatan</th>
                        <th class="px-4 py-3.5 text-center">Default</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($signatures as $sig)
                        <tr wire:key="sig-{{ $sig->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3">
                                @if ($sig->signature_path)
                                    <img src="{{ Storage::url($sig->signature_path) }}" class="h-10 bg-white border border-neutral-200 dark:border-slate-700 rounded-md p-1">
                                @else
                                    <span class="text-neutral-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-neutral-900 dark:text-white">{{ $sig->name }}</td>
                            <td class="px-4 py-3 text-xs text-neutral-600 dark:text-neutral-300">{{ $sig->position }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($sig->is_default)
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">Default</span>
                                @else
                                    <span class="text-neutral-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $sig->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $sig->id }})" wire:confirm="Yakin hapus profil ini?" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-400">Belum ada profil tanda tangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>