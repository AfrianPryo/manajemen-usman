<div class="w-full max-w-5xl mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    @php
        // Blade ini dipakai bersama oleh Master\Documents\Generate DAN
        // Unit\Documents\Generate. Unit\Documents\Generate tidak mengirim
        // variabel 'units' (lihat catatan di class-nya), jadi di sini
        // WAJIB pakai ($units ?? collect()) supaya tidak error "Undefined
        // variable" saat diakses oleh unit-admin.
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
        $units = $units ?? collect();
    @endphp

    <a href="{{ route($docsPrefix.'index', $docsParams) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-neutral-500 hover:text-blue-900 dark:hover:text-red-400 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke Menu Laporan
    </a>

    <div>
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Buat Dokumen Resmi</h1>
        <p class="text-xs text-neutral-400 mt-1">Sistem menarik data secara otomatis dari database ke template — tidak perlu copy-paste manual.</p>
    </div>

    {{-- Panduan --}}
    <details class="group bg-sky-50/60 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/40 rounded-md" open>
        <summary class="cursor-pointer list-none flex items-center justify-between p-4 select-none">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-500 text-white text-xs font-bold flex items-center justify-center shrink-0">?</span>
                <span class="text-xs font-bold text-sky-800 dark:text-sky-300">Panduan Membuat Dokumen dengan Benar</span>
            </div>
            <svg class="w-4 h-4 text-sky-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </summary>
        <div class="px-4 pb-4">
            <ol class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <li class="bg-white dark:bg-slate-900/50 rounded-md border border-sky-100 dark:border-sky-900/40 p-3">
                    <span class="text-[10px] font-bold text-sky-500">LANGKAH 1</span>
                    <p class="text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 mt-0.5">Pilih Jenis & Template</p>
                    <p class="text-[10px] text-neutral-400 mt-1">Pastikan template kop surat untuk jenis dokumen ini sudah aktif.</p>
                </li>
                <li class="bg-white dark:bg-slate-900/50 rounded-md border border-sky-100 dark:border-sky-900/40 p-3">
                    <span class="text-[10px] font-bold text-sky-500">LANGKAH 2</span>
                    <p class="text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 mt-0.5">Isi Data Dokumen</p>
                    <p class="text-[10px] text-neutral-400 mt-1">Lengkapi kolom sesuai jenis dokumen. Judul & perihal boleh dikosongkan.</p>
                </li>
                <li class="bg-white dark:bg-slate-900/50 rounded-md border border-sky-100 dark:border-sky-900/40 p-3">
                    <span class="text-[10px] font-bold text-sky-500">LANGKAH 3</span>
                    <p class="text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 mt-0.5">Pilih Penanda Tangan</p>
                    <p class="text-[10px] text-neutral-400 mt-1">Belum punya profil? Buat dulu lewat menu Pengaturan Tanda Tangan.</p>
                </li>
                <li class="bg-white dark:bg-slate-900/50 rounded-md border border-sky-100 dark:border-sky-900/40 p-3">
                    <span class="text-[10px] font-bold text-sky-500">LANGKAH 4</span>
                    <p class="text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 mt-0.5">Generate & Unduh</p>
                    <p class="text-[10px] text-neutral-400 mt-1">Nomor surat, tanggal, dan tanda tangan ditempel otomatis oleh sistem.</p>
                </li>
            </ol>
            <p class="mt-3 text-[10.5px] text-sky-700 dark:text-sky-400">
                Catatan: isi surat dibuat otomatis dari data sistem, jadi Anda hanya perlu mengisi kolom data spesifik — bukan mengetik seluruh isi surat.
            </p>
        </div>
    </details>

    {{-- Progress Stepper --}}
    <div class="flex items-center gap-2 text-[11px] font-semibold">
        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $type ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700' }}">
            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] {{ $type ? 'bg-emerald-500 text-white' : 'bg-neutral-300 dark:bg-slate-600 text-white' }}">1</span>
            Jenis & Template
        </span>
        <span class="text-neutral-300 dark:text-slate-600">&rarr;</span>
        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $type ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700' }}">
            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] {{ $type ? 'bg-emerald-500 text-white' : 'bg-neutral-300 dark:bg-slate-600 text-white' }}">2</span>
            Data Dokumen
        </span>
        <span class="text-neutral-300 dark:text-slate-600">&rarr;</span>
        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $signatureId ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700' }}">
            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] {{ $signatureId ? 'bg-emerald-500 text-white' : 'bg-neutral-300 dark:bg-slate-600 text-white' }}">3</span>
            Penanda Tangan
        </span>
        <span class="text-neutral-300 dark:text-slate-600">&rarr;</span>
        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ session('success') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700' }}">
            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] {{ session('success') ? 'bg-emerald-500 text-white' : 'bg-neutral-300 dark:bg-slate-600 text-white' }}">4</span>
            Selesai
        </span>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('success') }}</span>
            <button wire:click="download" class="px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-full transition-all">Unduh Dokumen</button>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-5">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">1. Jenis Dokumen</label>
                <select wire:model.live="type" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    <option value="">-- Pilih jenis dokumen --</option>
                    @foreach ($this->types() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
            </div>

            @if ($type)
                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">2. Template</label>
                    <select wire:model="templateId" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        <option value="">-- Pilih template --</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                    @error('templateId') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    @if ($templates->isEmpty())
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">Belum ada template aktif untuk jenis ini. Tambahkan dulu di menu Template Dokumen.</p>
                    @endif
                </div>
            @endif
        </div>

        @if ($type)
            <hr class="border-neutral-100 dark:border-slate-700">
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-400">3. Data Dokumen</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul Dokumen (opsional)</label>
                    <input type="text" wire:model="title" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Perihal (opsional)</label>
                    <input type="text" wire:model="subject" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                </div>
            </div>

            @if (in_array($type, ['finance_report', 'laporan_konsolidasi']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Mulai</label>
                        <input type="date" wire:model="start_date" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Selesai</label>
                        <input type="date" wire:model="end_date" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    @if ($type === 'finance_report')
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha</label>
                            @if ($isUnitAdmin)
                                <div class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-neutral-50 dark:bg-slate-800 text-neutral-500">
                                    {{ request()->route('unit')?->name ?? '-' }} <span class="text-neutral-400">(unit terkait)</span>
                                </div>
                            @else
                                <select wire:model="unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                    <option value="">Seluruh Unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @if ($type === 'surat_keterangan')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Penerima Surat</label>
                        <input type="text" wire:model="nama_penerima" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jabatan / Status Penerima</label>
                        <input type="text" wire:model="jabatan_penerima" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">NIP / NIM (opsional)</label>
                        <input type="text" wire:model="nip_penerima" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha Terkait</label>
                        @if ($isUnitAdmin)
                            <div class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-neutral-50 dark:bg-slate-800 text-neutral-500">
                                {{ request()->route('unit')?->name ?? '-' }} <span class="text-neutral-400">(unit terkait)</span>
                            </div>
                        @else
                            <select wire:model="unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Keperluan</label>
                        <input type="text" wire:model="keperluan" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Isi Keterangan</label>
                        <textarea wire:model="isi_keterangan" rows="3" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500"></textarea>
                    </div>
                </div>
            @endif

            @if ($type === 'berita_acara_aset')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Pihak Pertama (Menyerahkan)</label>
                        <input type="text" wire:model="pihak_pertama_nama" placeholder="Nama" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md mb-2 bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        <input type="text" wire:model="pihak_pertama_jabatan" placeholder="Jabatan" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Pihak Kedua (Menerima)</label>
                        <input type="text" wire:model="pihak_kedua_nama" placeholder="Nama" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md mb-2 bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        <input type="text" wire:model="pihak_kedua_jabatan" placeholder="Jabatan" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Keperluan Serah Terima</label>
                        <input type="text" wire:model="keperluan" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Pilih Aset yang Diserahterimakan</label>
                        <div class="max-h-48 overflow-y-auto border border-neutral-200 dark:border-slate-700 rounded-md p-2 space-y-1 bg-white dark:bg-slate-900">
                            @forelse ($assets as $asset)
                                <label class="flex items-center gap-2 text-xs text-neutral-700 dark:text-neutral-300">
                                    <input type="checkbox" wire:model="asset_ids" value="{{ $asset->id }}" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20">
                                    {{ $asset->asset_tag }} — {{ $asset->name }}
                                </label>
                            @empty
                                <p class="text-[11px] text-neutral-400">Tidak ada data aset.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <hr class="border-neutral-100 dark:border-slate-700">

            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">4. Penanda Tangan</label>
                <select wire:model="signatureId" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                    <option value="">-- Pilih profil tanda tangan --</option>
                    @foreach ($signatures as $sig)
                        <option value="{{ $sig->id }}">{{ $sig->name }} ({{ $sig->position }})</option>
                    @endforeach
                </select>
                @error('signatureId') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                @if ($signatures->isEmpty())
                    <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">Anda belum punya profil tanda tangan. Buat dulu di menu Pengaturan Tanda Tangan.</p>
                @endif
            </div>

            <div class="flex justify-end pt-2">
                <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-full transition-all shadow-sm shadow-blue-900/20 disabled:opacity-50">
                    <span wire:loading.remove wire:target="generate">Buat Dokumen</span>
                    <span wire:loading wire:target="generate">Memproses...</span>
                </button>
            </div>
        @endif
    </div>
</div>