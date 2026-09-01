<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    <a href="{{ route('master.documents.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-neutral-500 hover:text-blue-900 dark:hover:text-red-400 transition-colors">
        <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
        Kembali ke Menu Laporan
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Template Dokumen Resmi</h1>
            <p class="text-xs text-neutral-400 mt-1">Kelola kop surat (.docx) untuk setiap jenis dokumen resmi. Isi surat dibuat otomatis oleh sistem.</p>
        </div>
        <button wire:click="create" class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-full transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 shrink-0">
            <x-heroicon-o-plus stroke-width="2.5" class="w-4 h-4" />
            Tambah Template
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-md bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Jenis Dokumen</th>
                        <th class="px-4 py-3.5">Nama Template</th>
                        <th class="px-4 py-3.5">Format Nomor</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($templates as $tpl)
                        <tr wire:key="tpl-{{ $tpl->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                    {{ \App\Support\DocumentTypes::label($tpl->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs font-semibold text-neutral-900 dark:text-white">{{ $tpl->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-neutral-400">{{ $tpl->numbering_format }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full {{ $tpl->is_active ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-100 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-slate-600' }}">
                                    {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $tpl->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all" title="Edit">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="toggleActive({{ $tpl->id }})" class="p-1.5 text-sky-600 hover:text-sky-800 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-md transition-all" title="{{ $tpl->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <x-heroicon-o-power class="w-4 h-4" />
                                    </button>
                                    <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                            message: 'Yakin hapus template ini?',
                                            confirmText: 'Ya, Hapus',
                                            onConfirm: () => $wire.delete({{ $tpl->id }})
                                        })" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-400">Belum ada template. Tambahkan template pertama Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if ($templates->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $templates->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $templates->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $templates->total() }}</span> total template
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if ($templates->currentPage() > 1)
                    <button wire:click="gotoPage(1)" class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all">
                        <x-heroicon-o-chevron-double-left class="w-3.5 h-3.5" />
                        Kembali ke Awal
                    </button>
                @endif
                {{ $templates->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $editingId ? 'Edit Template' : 'Tambah Template' }}
                        </h3>
                        <p class="text-xs text-neutral-400">Kop surat & format penomoran untuk jenis dokumen ini.</p>
                    </div>
                    <button wire:click="$set('showForm', false)" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jenis Dokumen</label>
                        <select wire:model.live="type" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            <option value="">-- Pilih jenis dokumen --</option>
                            @foreach ($this->types() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($type)
                        <div class="p-3 bg-sky-50/60 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/40 rounded-md text-[11px] space-y-2">
                            <p class="font-semibold text-sky-800 dark:text-sky-300">
                                File yang Anda unggah di bawah hanya perlu berisi kop surat (header/footer: logo, alamat, kontak) dan page setup (margin, ukuran kertas). Kosongkan bagian body — jangan taruh placeholder <code>${...}</code> apa pun, karena isi surat sekarang ditulis otomatis oleh sistem.
                            </p>
                            <p class="font-semibold text-sky-800 dark:text-sky-300">
                                Data yang akan otomatis muncul di isi surat untuk jenis dokumen ini:
                            </p>
                            <ul class="list-disc list-inside text-sky-700 dark:text-sky-400">
                                @foreach ($placeholderHelp as $key => $val)
                                    @if (is_array($val))
                                        <li>{{ $key }}: {{ implode(', ', $val) }}</li>
                                    @else
                                        <li>{{ $val }}</li>
                                    @endif
                                @endforeach
                                <li>Selalu otomatis: nomor surat, tanggal, perihal &amp; kepada Yth (jika diisi saat generate), serta blok tanda tangan (nama, jabatan, gambar tanda tangan).</li>
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Template</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Laporan Keuangan Bulanan - Format A" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi (opsional)</label>
                        <textarea wire:model="description" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">File Kop Surat (.docx)</label>
                        <input type="file" wire:model="templateFile" accept=".docx" class="w-full text-xs border border-neutral-200 dark:border-slate-700 rounded-md p-1.5 bg-neutral-50 dark:bg-slate-900 text-neutral-800 dark:text-neutral-200">
                        <div wire:loading wire:target="templateFile" class="text-[11px] text-amber-600 mt-1">Mengunggah...</div>
                        @error('templateFile') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        <p class="text-[11px] text-neutral-400 mt-1">Cukup header/footer (logo, alamat) + page setup. Body dikosongkan, isi surat dibuat otomatis oleh sistem.</p>
                        @if ($editingId)
                            <p class="text-[11px] text-neutral-400 mt-1">Kosongkan jika tidak ingin mengganti file kop surat.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Format Nomor Surat</label>
                            <input type="text" wire:model="numbering_format" class="w-full px-3.5 py-2 text-xs font-mono font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            <p class="text-[11px] text-neutral-400 mt-1">Token: {nomor}, {bulan_romawi}, {bulan}, {tahun}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Reset Nomor</label>
                            <select wire:model="numbering_reset" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="yearly">Setiap Tahun</option>
                                <option value="monthly">Setiap Bulan</option>
                                <option value="never">Tidak Pernah</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-neutral-50 dark:bg-slate-900 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-full hover:bg-neutral-100 dark:hover:bg-slate-700 transition-all">Batal</button>
                    <button wire:click="save" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-full transition-all shadow-sm shadow-blue-900/20">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>