<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Transaksi Berulang</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Atur jadwal otomatisasi arus kas harian, mingguan, bulanan, atau tahunan.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openModal" class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-full transition-all flex items-center gap-2 shadow-sm shadow-red-600/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Buat Transaksi Berulang</span>
            </button>
        </div>
    </div>

{{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            {{-- Input Search (Mengambil 2 Kolom di Layar Sedang/Besar) --}}
            <div class="sm:col-span-2 md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama transaksi..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>

            {{-- Filter Tipe --}}
            <div>
                <select wire:model.live="typeFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="income">Pendapatan (Income)</option>
                    <option value="expense">Pengeluaran (Expense)</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="paused">Dijeda</option>
                </select>
            </div>

            <div class="flex items-center">
                <button wire:click="resetFilters" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all cursor-pointer whitespace-nowrap text-center">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    @if(count($selectedRows) > 0)
        <div class="mb-3 flex items-center justify-between bg-neutral-900 text-white p-3.5 rounded-md shadow-md text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-red-400">{{ count($selectedRows) }}</span> item dipilih
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkUpdateStatus('active')" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 rounded font-semibold transition-colors">
                    Tandai Aktif
                </button>
                <button wire:click="bulkUpdateStatus('paused')" class="px-3 py-1 bg-amber-600 hover:bg-amber-500 rounded font-semibold transition-colors">
                    Tandai Dijeda
                </button>
                <button wire:click="bulkDelete" onclick="confirm('Yakin ingin menghapus data terpilih?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 rounded font-semibold transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    @endif

    {{-- Tabel Data --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Judul & Unit</th>
                        <th class="px-4 py-3.5 text-right">Tipe & Jumlah</th>
                        <th class="px-4 py-3.5 text-center">Frekuensi</th>
                        <th class="px-4 py-3.5">Jadwal Berikutnya</th>
                        <th class="px-4 py-3.5 text-center">Otomatisasi</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($recurringTransactions as $item)
                        <tr wire:key="rec-{{ $item->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors {{ $item->is_expired ? 'bg-neutral-100/60 dark:bg-slate-800/40 opacity-60' : '' }}">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $item->id }}" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs flex items-center gap-2">
                                    {{ $item->title }}
                                    @if($item->is_expired)
                                        <span class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-neutral-200 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300">Selesai</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-neutral-400 mt-0.5">{{ $item->unit->name ?? '-' }}</div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-bold {{ $item->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $item->type === 'income' ? '+' : '-' }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-center capitalize">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-neutral-100 dark:bg-slate-700 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-slate-600">
                                    {{ $item->frequency }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300">
                                @if($item->is_expired)
                                    <span class="line-through text-neutral-400 text-xs">
                                        {{ \Carbon\Carbon::parse($item->next_run_date)->format('d M Y') }}
                                    </span>
                                    <span class="block text-[10px] text-rose-500 font-medium">Masa berlaku habis</span>
                                @else
                                    <span class="font-semibold">{{ \Carbon\Carbon::parse($item->next_run_date)->format('d M Y') }}</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($item->auto_approve)
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                        Otomatis Dibuat
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">
                                        Draf / Konfirmasi
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($item->is_expired)
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">
                                        Kadaluarsa
                                    </span>
                                @else
                                    <button wire:click="toggleStatus({{ $item->id }})" class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full cursor-pointer transition-all border {{ $item->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-200/60 dark:border-amber-800' }}">
                                        {{ $item->status === 'active' ? 'Aktif' : 'Dijeda' }}
                                    </button>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all" title="Edit Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Apakah Anda yakin ingin menghapus transaksi berulang ini?" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all" title="Hapus Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada transaksi berulang yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination & Per Page Control --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                <div class="flex items-center gap-2">
                    <span>Tampilkan</span>
                    <select wire:model.live="perPage" class="py-1 px-2 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 text-neutral-700 dark:text-neutral-300 font-medium cursor-pointer">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>data</span>
                </div>

                @if($recurringTransactions->total() > 0)
                    <span class="hidden sm:inline-block text-neutral-300 dark:text-slate-700">|</span>
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $recurringTransactions->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $recurringTransactions->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $recurringTransactions->total() }}</span> total data
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{ $recurringTransactions->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            {{ $editingId ? 'Edit Transaksi Berulang' : 'Buat Transaksi Berulang Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $editingId ? 'Perbarui konfigurasi dan periode transaksi berulang.' : 'Atur transaksi otomatisasi arus kas secara berkala.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="p-6 space-y-4">
                    
                    {{-- Judul Transaksi --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul Transaksi <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="cth. Biaya Sewa Kantin Bulanan" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('title') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Row: Unit Usaha & Tipe Transaksi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                            <select wire:model="unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tipe Transaksi <span class="text-red-500">*</span></label>
                            <select wire:model="type" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="income">Pendapatan (Income)</option>
                                <option value="expense">Pengeluaran (Expense)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row: Nominal & Frekuensi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="amount" placeholder="0" class="w-full px-3.5 py-2 text-xs font-bold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('amount') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Frekuensi Berulang <span class="text-red-500">*</span></label>
                            <select wire:model="frequency" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row: Tanggal Mulai & Tanggal Selesai --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="start_date" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('start_date') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Selesai <span class="text-xs text-neutral-400 font-normal">(Opsional)</span></label>
                            <input type="date" wire:model="end_date" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>
                    </div>

                    {{-- Card Opsi Otomatisasi (Highlighted) --}}
                    <div class="p-3.5 rounded-lg border border-sky-200/80 bg-sky-50/50 dark:border-sky-900/50 dark:bg-sky-950/20 transition-all">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="auto_approve" wire:model="auto_approve" class="mt-0.5 rounded border-sky-300 dark:border-sky-700 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            <div class="space-y-1">
                                <label for="auto_approve" class="text-xs font-bold text-neutral-800 dark:text-neutral-100 cursor-pointer flex items-center gap-1.5">
                                    <span>Otomatiskan ke Laporan Keuangan</span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300">Auto Approve</span>
                                </label>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                    <strong class="text-sky-700 dark:text-sky-400">Dicentang:</strong> Transaksi langsung diterbitkan & dicatat ke laporan keuangan saat jatuh tempo.<br>
                                    <strong class="text-amber-700 dark:text-amber-400">Tidak Dicentang:</strong> Transaksi dibuat sebagai <span class="italic font-medium">Draf</span> dan butuh konfirmasi manual Anda.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan Tambahan</label>
                        <textarea wire:model="notes" rows="2" placeholder="Keterangan tambahan..." class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500"></textarea>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-md transition-all flex items-center gap-2 shadow-sm">
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>