<div class="w-full max-w-3xl mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">
    <livewire:page-tour tour="documents.generate" />

    <a href="{{ route('master.documents.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-neutral-500 hover:text-[#0d3b74] dark:hover:text-sky-400 transition-colors">
        <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
        Kembali ke Menu Laporan
    </a>

    <div class="relative pr-20 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <x-tour-replay tour="documents.generate" class="absolute top-4 right-4" />
        <h1 class="text-md font-bold text-neutral-900 dark:text-white tracking-tight">Buat Dokumen Resmi</h1>
        <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Pilih jenis dokumen, template, dan lengkapi data singkat untuk membuat dokumen resmi baru.</p>
    </div>

    @if (session('success'))
        <div wire:key="toast-success-{{ md5(session('success')) }}" x-data x-init="$store.toast.push('success', @js(session('success')))"></div>
    @endif

    <form wire:submit.prevent="generate" class="space-y-5">

        {{-- Jenis Dokumen & Template --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 space-y-4">
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white tracking-tight pb-3 border-b border-neutral-100 dark:border-slate-700">Jenis &amp; Template</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                    <select id="type" wire:model.live="type"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        <option value="">-- Pilih Jenis Dokumen --</option>
                        @foreach ($this->types() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <x-form-error :message="$message" :field="'type'" /> @enderror
                </div>

                <div>
                    <label for="templateId" class="flex items-center gap-1 text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                        Template <span class="text-red-500">*</span>
                        <x-help-tip text="Format/susunan isi dokumen untuk jenis dokumen yang dipilih. Kalau daftarnya kosong, buat dulu templatenya lewat menu Kelola Template." />
                    </label>
                    <select id="templateId" wire:model="templateId" @disabled(!$type)
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 disabled:bg-neutral-50 dark:disabled:bg-slate-900/50 disabled:cursor-not-allowed">
                        <option value="">-- Pilih Template --</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                    @if ($type && $templates->isEmpty())
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">
                            Belum ada template aktif untuk jenis dokumen ini.
                            @if (Route::has('master.documents.templates'))
                                <a href="{{ route('master.documents.templates') }}" class="underline hover:text-amber-800 dark:hover:text-amber-300">Kelola template</a>
                            @endif
                        </p>
                    @endif
                    @error('templateId') <x-form-error :message="$message" :field="'templateId'" /> @enderror
                </div>
            </div>
        </div>

        {{-- Tanda Tangan & Unit --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 space-y-4">
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white tracking-tight pb-3 border-b border-neutral-100 dark:border-slate-700">Penanda Tangan &amp; Unit</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="signatureId" class="flex items-center gap-1 text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                        Tanda Tangan <span class="text-red-500">*</span>
                        <x-help-tip text="Profil pejabat (nama, jabatan, & gambar tanda tangan) yang akan dibubuhkan di dokumen ini. Kelola daftarnya lewat menu Tanda Tangan sebelum membuat dokumen." />
                    </label>
                    <select id="signatureId" wire:model="signatureId"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        <option value="">-- Pilih Profil Tanda Tangan --</option>
                        @foreach ($signatures as $sig)
                            <option value="{{ $sig->id }}">{{ $sig->name }} ({{ $sig->position }})</option>
                        @endforeach
                    </select>
                    @if ($signatures->isEmpty())
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1">
                            Anda belum memiliki profil tanda tangan.
                            @if (Route::has('master.documents.signature'))
                                <a href="{{ route('master.documents.signature') }}" class="underline hover:text-amber-800 dark:hover:text-amber-300">Buat sekarang</a>
                            @endif
                        </p>
                    @endif
                    @error('signatureId') <x-form-error :message="$message" :field="'signatureId'" /> @enderror
                </div>

                <div>
                    <label for="unit_id" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Unit</label>
                    @if(! empty($lockedUnitId) && $units->count() === 1)
                        <x-locked-field :value="$units->first()->name" class="px-3.5 py-2 text-xs font-medium" />
                    @else
                    <select id="unit_id" wire:model="unit_id"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    @error('unit_id') <x-form-error :message="$message" :field="'unit_id'" /> @enderror
                </div>
            </div>
        </div>

        {{-- Field umum --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 space-y-4">
            <h2 class="text-sm font-bold text-neutral-900 dark:text-white tracking-tight pb-3 border-b border-neutral-100 dark:border-slate-700">Detail Surat</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Judul / Perihal Singkat</label>
                    <input type="text" id="title" wire:model="title"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('title') <x-form-error :message="$message" :field="'title'" /> @enderror
                </div>
                <div>
                    <label for="subject" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Subjek</label>
                    <input type="text" id="subject" wire:model="subject"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('subject') <x-form-error :message="$message" :field="'subject'" /> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="recipient" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Penerima / Ditujukan Kepada</label>
                    <input type="text" id="recipient" wire:model="recipient"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('recipient') <x-form-error :message="$message" :field="'recipient'" /> @enderror
                </div>
                <div>
                    <label for="start_date" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Mulai</label>
                    <input type="date" id="start_date" wire:model="start_date"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('start_date') <x-form-error :message="$message" :field="'start_date'" /> @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Selesai</label>
                    <input type="date" id="end_date" wire:model="end_date"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('end_date') <x-form-error :message="$message" :field="'end_date'" /> @enderror
                </div>
            </div>
        </div>

        {{--
            Field khusus per jenis dokumen.
            CATATAN: sesuaikan nilai konstanta di bawah ini ('surat_keterangan')
            dengan nilai sebenarnya pada App\Support\DocumentTypes bila nama
            konstantanya berbeda. Yang sudah pasti dari kode komponen:
            DocumentTypes::BERITA_ACARA_ASET.
        --}}
        @if ($type === \App\Support\DocumentTypes::SURAT_KETERANGAN ?? 'surat_keterangan')
            <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 space-y-4">
                <h2 class="text-sm font-bold text-neutral-900 dark:text-white tracking-tight pb-3 border-b border-neutral-100 dark:border-slate-700">Data Surat Keterangan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_penerima" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Penerima</label>
                        <input type="text" id="nama_penerima" wire:model="nama_penerima"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('nama_penerima') <x-form-error :message="$message" :field="'nama_penerima'" /> @enderror
                    </div>
                    <div>
                        <label for="jabatan_penerima" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Jabatan Penerima</label>
                        <input type="text" id="jabatan_penerima" wire:model="jabatan_penerima"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('jabatan_penerima') <x-form-error :message="$message" :field="'jabatan_penerima'" /> @enderror
                    </div>
                    <div>
                        <label for="nip_penerima" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">NIP Penerima</label>
                        <input type="text" id="nip_penerima" wire:model="nip_penerima" inputmode="numeric" oninput="onlyDigits(event)"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('nip_penerima') <x-form-error :message="$message" :field="'nip_penerima'" /> @enderror
                    </div>
                    <div>
                        <label for="keperluan" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Keperluan</label>
                        <input type="text" id="keperluan" wire:model="keperluan"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('keperluan') <x-form-error :message="$message" :field="'keperluan'" /> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="isi_keterangan" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Isi Keterangan</label>
                        <textarea id="isi_keterangan" wire:model="isi_keterangan" rows="4"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                        @error('isi_keterangan') <x-form-error :message="$message" :field="'isi_keterangan'" /> @enderror
                    </div>
                </div>
            </div>
        @endif

        @if ($type === \App\Support\DocumentTypes::BERITA_ACARA_ASET)
            <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 space-y-4">
                <h2 class="text-sm font-bold text-neutral-900 dark:text-white tracking-tight pb-3 border-b border-neutral-100 dark:border-slate-700">Data Berita Acara Serah Terima Aset</h2>

                <div>
                    <label for="asset_ids" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Aset yang Diserahterimakan</label>
                    <select id="asset_ids" wire:model="asset_ids" multiple size="6"
                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-neutral-400 mt-1">Tahan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu aset.</p>
                    @error('asset_ids') <x-form-error :message="$message" :field="'asset_ids'" /> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="pihak_pertama_nama" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Pihak Pertama</label>
                        <input type="text" id="pihak_pertama_nama" wire:model="pihak_pertama_nama"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('pihak_pertama_nama') <x-form-error :message="$message" :field="'pihak_pertama_nama'" /> @enderror
                    </div>
                    <div>
                        <label for="pihak_pertama_jabatan" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Jabatan Pihak Pertama</label>
                        <input type="text" id="pihak_pertama_jabatan" wire:model="pihak_pertama_jabatan"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('pihak_pertama_jabatan') <x-form-error :message="$message" :field="'pihak_pertama_jabatan'" /> @enderror
                    </div>
                    <div>
                        <label for="pihak_kedua_nama" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Pihak Kedua</label>
                        <input type="text" id="pihak_kedua_nama" wire:model="pihak_kedua_nama"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('pihak_kedua_nama') <x-form-error :message="$message" :field="'pihak_kedua_nama'" /> @enderror
                    </div>
                    <div>
                        <label for="pihak_kedua_jabatan" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Jabatan Pihak Kedua</label>
                        <input type="text" id="pihak_kedua_jabatan" wire:model="pihak_kedua_jabatan"
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('pihak_kedua_jabatan') <x-form-error :message="$message" :field="'pihak_kedua_jabatan'" /> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2.5 pt-1">
            @if ($lastGeneratedId)
                <button type="button" wire:click="download"
                    wire:loading.attr="disabled"
                    wire:target="download"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-sm hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                    Unduh Dokumen
                </button>
            @endif

            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="generate"
                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="generate">Buat Dokumen</span>
                <span wire:loading wire:target="generate">Memproses...</span>
            </button>
        </div>
    </form>
</div>