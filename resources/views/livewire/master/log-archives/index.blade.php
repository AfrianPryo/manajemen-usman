<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-md bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">Arsip Log</h1>
            <p class="text-xs text-neutral-400">File Excel per bulan dari log login & audit log yang sudah melewati batas retensi.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="runArchiveNow"
                wire:confirm="Jalankan pengarsipan sekarang? Log yang sudah melewati batas retensi akan diekspor ke Excel lalu dihapus dari tabel utama."
                wire:loading.attr="disabled" wire:target="runArchiveNow"
                class="px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-[3px] hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-60">
                <x-heroicon-o-archive-box wire:loading.remove wire:target="runArchiveNow" class="w-4 h-4" />
                <span wire:loading.remove wire:target="runArchiveNow">Arsipkan Sekarang</span>
                <span wire:loading wire:target="runArchiveNow">Mengarsipkan...</span>
            </button>
        </div>
    </div>

    {{-- Info Retensi --}}
    <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs px-4 py-3 rounded-md space-y-1.5">
        <p>
            Batas retensi saat ini <span class="font-bold">{{ $retentionDays }} hari</span>.
            Setiap hari pukul 02:00, bulan yang seluruhnya sudah lewat batas itu diekspor ke Excel, diverifikasi, lalu dihapus dari tabel utama.
            Log sebelum <span class="font-bold">{{ $cutoff->format('d M Y') }}</span> sudah/akan berada di arsip;
            karena diarsipkan per bulan penuh, data bisa bertahan di tabel utama sampai sekitar 31 hari lebih lama dari batas.
        </p>
        <p>
            Menunggu diarsipkan:
            <span class="font-bold">{{ number_format($pending['auth'] ?? 0, 0, ',', '.') }}</span> baris log login,
            <span class="font-bold">{{ number_format($pending['audit'] ?? 0, 0, ',', '.') }}</span> baris audit log.
        </p>

        {{-- Ubah batas retensi (setting yang sama dengan Pengaturan > Fitur & Modul) --}}
        <form wire:submit="saveRetention" class="pt-2 flex flex-col sm:flex-row sm:items-end gap-2">
            <div>
                <label for="retention_input" class="block text-[11px] font-semibold mb-1">Batas Retensi (hari)</label>
                <input id="retention_input" type="text" inputmode="numeric"
                    wire:model="retentionInput" oninput="onlyDigits(event)"
                    class="w-40 px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveRetention"
                class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all disabled:opacity-60 cursor-pointer">
                <span wire:loading.remove wire:target="saveRetention">Simpan</span>
                <span wire:loading wire:target="saveRetention">Menyimpan...</span>
            </button>
            <span class="text-[11px] opacity-80">Minimal {{ \App\Services\LogArchiveService::MIN_RETENTION_DAYS }}, maksimal {{ number_format(\App\Services\LogArchiveService::MAX_RETENTION_DAYS, 0, ',', '.') }} hari.</span>
        </form>
        @error('retentionInput') <p class="text-[11px] text-rose-500 font-semibold">{{ $message }}</p> @enderror
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">File Arsip</div>
            <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ number_format($totalFiles, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Total Baris Terarsip</div>
            <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ number_format($totalRows, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Ukuran Total</div>
            <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ \App\Models\LogArchive::formatBytes($totalSize) }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label for="type_filter" class="sr-only">Jenis Log</label>
                <select id="type_filter" wire:model.live="typeFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Jenis Log</option>
                    @foreach (\App\Models\LogArchive::typeLabels() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="year_filter" class="sr-only">Tahun</label>
                <select id="year_filter" wire:model.live="yearFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Tahun</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel Arsip --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Periode</th>
                        <th class="px-4 py-3.5">Jenis Log</th>
                        <th class="px-4 py-3.5 text-right">Jumlah Baris</th>
                        <th class="px-4 py-3.5 text-right">Ukuran</th>
                        <th class="px-4 py-3.5">Diarsipkan Pada</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($archives as $archive)
                        @php $exists = $archive->fileExists(); @endphp
                        <tr wire:key="archive-{{ $archive->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $archive->period_label }}</div>
                                <div class="text-[11px] font-mono text-neutral-400">{{ $archive->file_name }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-neutral-700 dark:text-neutral-200">
                                {{ $archive->type_label }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-mono text-neutral-700 dark:text-neutral-200">
                                {{ number_format($archive->row_count, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-mono text-neutral-500">
                                {{ $archive->human_size }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-500">
                                {{ $archive->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                @if ($exists)
                                    <button wire:click="download({{ $archive->id }})" wire:loading.attr="disabled" wire:target="download({{ $archive->id }})"
                                        class="px-3 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-[3px] hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-60">
                                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                        <span>Download</span>
                                    </button>
                                @else
                                    <span class="px-2.5 py-1 text-[11px] font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-[3px]" title="File tidak ada di storage/app/private/{{ $archive->file_path }}">File tidak ditemukan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-xs text-neutral-400">
                                Belum ada arsip. Arsip pertama akan muncul setelah ada log yang seluruh bulannya melewati batas retensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($archives->hasPages())
            <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700">
                {{ $archives->links('components.custom-pagination') }}
            </div>
        @endif
    </div>
</div>
