<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pemasukan</p>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6-6m-6 6l-6-6"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Transaksi berstatus selesai</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pengeluaran</p>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6 6m6-6l6 6"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Biaya & operasional keluar</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Arus Kas Bersih</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a.5.5 0 00.71 0L21.75 6M21.75 6v5.25m0-5.25h-5.25"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold tracking-tight {{ $netBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format($netBalance, 0, ',', '.') }}
            </p>
            <p class="mt-2 text-[11px] text-neutral-400">Selisih masuk - keluar</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Pending / Menunggu</p>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($pendingCount) }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Membutuhkan konfirmasi</p>
        </div>
    </div>

    {{-- Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            {{-- Tombol Export --}}
            <button wire:click="exportData" class="px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Export Excel</span>
            </button>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            {{-- Tombol Import Excel --}}
            <button wire:click="openImportModal" class="px-3.5 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span>Import Excel</span>
            </button>

            {{-- Tombol Tambah Transaksi --}}
            <button wire:click="openCreateModal" class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-full transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Transaksi</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No. Referensi atau Deskripsi..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>

            <div>
                <select wire:model.live="unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="typeFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="income">Pemasukan (Income)</option>
                    <option value="expense">Pengeluaran (Expense)</option>
                </select>
            </div>

            <div>
                <select wire:model.live="statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="completed">Selesai (Completed)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="cancelled">Dibatalkan (Cancelled)</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-neutral-100 dark:border-slate-700/60 text-xs">
            {{-- Rentang Tanggal --}}
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-medium text-neutral-400">Rentang Tanggal:</span>
                <input type="date" wire:model.live="startDate" class="px-3 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                <span class="text-neutral-400">s/d</span>
                <input type="date" wire:model.live="endDate" class="px-3 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
            </div>

            {{-- Reset Filter --}}
            <div class="flex items-center shrink-0">
                <button wire:click="resetFilters" class="px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all cursor-pointer">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    @if(count($selectedRows) > 0)
        <div class="flex items-center justify-between bg-neutral-900 text-white p-3.5 rounded-md shadow-md text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-red-400">{{ count($selectedRows) }}</span> item dipilih
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkUpdateStatus('completed')" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 rounded font-semibold transition-colors">
                    Tandai Selesai
                </button>
                <button wire:click="bulkUpdateStatus('pending')" class="px-3 py-1 bg-sky-600 hover:bg-sky-500 rounded font-semibold transition-colors">
                    Tandai Menunggu
                </button>
                <button wire:click="bulkUpdateStatus('cancelled')" class="px-3 py-1 bg-amber-600 hover:bg-amber-500 rounded font-semibold transition-colors">
                    Batalkan
                </button>
                <button wire:click="bulkDelete" onclick="confirm('Yakin ingin menghapus data terpilih?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 rounded font-semibold transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    @endif

    {{-- Transactions Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Tanggal & Ref</th>
                        <th class="px-4 py-3.5">Unit Usaha</th>
                        <th class="px-4 py-3.5">Deskripsi & Kategori</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Jumlah</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($transactions as $tr)
                        <tr wire:key="tr-{{ $tr->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $tr->id }}" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">
                                    {{ optional($tr->transaction_date)->format('d M Y') ?? '-' }}
                                </div>
                                <div class="text-[11px] font-mono text-neutral-400">
                                    {{ $tr->reference_no ?? '#TX-'.$tr->id }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                {{ $tr->unit->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-neutral-900 dark:text-white line-clamp-1">
                                        {{ $tr->description }}
                                    </span>
                                    @if(!empty($tr->proof_file))
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-1.5 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800 shrink-0" title="Ada Bukti Struk">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Struk
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    {{ $tr->category->name ?? 'Umum' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-300 uppercase font-mono">
                                {{ $tr->payment_method ?? 'TUNAI' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($tr->status === 'completed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">Selesai</span>
                                @elseif($tr->status === 'pending')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">Menunggu</span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right font-bold text-xs {{ $tr->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $tr->type === 'income' ? '+' : '-' }} Rp {{ number_format($tr->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openDetail({{ $tr->id }})" class="p-1.5 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-md transition-all" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.573 16.49 16.638 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button wire:click="editTransaction({{ $tr->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all" title="Edit Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Tidak ada data transaksi yang sesuai dengan filter.
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

                @if($transactions->total() > 0)
                    <span class="hidden sm:inline-block text-neutral-300 dark:text-slate-700">|</span>
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $transactions->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $transactions->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $transactions->total() }}</span> total data
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{-- Memanggil custom pagination view --}}
                {{ $transactions->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- ================= MODAL TRANSAKSI (TAMBAH & EDIT) ================= --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Transaksi' : 'Tambah Transaksi Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $isEditing ? 'Perbarui detail data transaksi finansial unit usaha.' : 'Catat transaksi pemasukan atau pengeluaran finansial unit usaha.' }}
                        </p>
                    </div>
                    <button wire:click="closeCreateModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="saveTransaction" class="p-6 space-y-4">
                    
                    {{-- Toggle Type: Income / Expense --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 mb-2">TIPE TRANSAKSI</label>
                        <div class="grid grid-cols-2 gap-3 p-1 bg-neutral-100 dark:bg-slate-900 rounded-lg">
                            <button type="button" wire:click="$set('form_type', 'income')"
                                    class="py-2.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2 {{ $form_type === 'income' ? 'bg-emerald-600 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6-6m-6 6l-6-6"/></svg>
                                Pemasukan (Income)
                            </button>
                            <button type="button" wire:click="$set('form_type', 'expense')"
                                    class="py-2.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2 {{ $form_type === 'expense' ? 'bg-rose-600 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6 6m6-6l6 6"/></svg>
                                Pengeluaran (Expense)
                            </button>
                        </div>
                    </div>

                    {{-- Row 1: Unit & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                            <select wire:model.live="form_unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('form_unit_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori Transaksi <span class="text-red-500">*</span></label>
                            <select wire:key="select-category-{{ $form_unit_id }}-{{ $form_type }}"
                                    wire:model="form_finance_category_id" 
                                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('form_finance_category_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2: Nominal & Tanggal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jumlah Nominal (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="form_amount" placeholder="0" min="1"
                                class="w-full px-3.5 py-2 text-xs font-bold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_amount') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="form_transaction_date"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_transaction_date') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 3: Ref No, Payment Method, Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                                No. Referensi <span class="text-xs text-neutral-400 font-normal">(Otomatis)</span>
                            </label>
                            <input type="text" 
                                wire:model="form_reference_no" 
                                readonly 
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-neutral-100 dark:bg-slate-800/60 text-neutral-500 dark:text-neutral-400 cursor-not-allowed focus:outline-none" 
                                placeholder="Otomatis diset sistem">
                            @error('form_reference_no') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="form_payment_method" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                                <option value="card">Kartu Debit/Kredit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status</label>
                            <select wire:model="form_status" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="completed">Selesai (Completed)</option>
                                <option value="pending">Menunggu (Pending)</option>
                                <option value="cancelled">Dibatalkan (Cancelled)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi / Catatan</label>
                        <textarea wire:model="form_description" rows="2" placeholder="Masukkan rincian keterangan transaksi..."
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500"></textarea>
                    </div>

                    {{-- Bukti Transaksi --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300">
                                Bukti Transaksi (JPG/PNG/PDF, Max 2MB)
                            </label>
                            @if($isEditing)
                                <span class="text-[10px] text-neutral-400">*Kosongkan jika tidak ingin mengubah</span>
                            @endif
                        </div>
                        
                        <input type="file" 
                            wire:model="form_proof_file" 
                            accept="image/png, image/jpeg, image/jpg, application/pdf"
                            class="w-full text-xs border border-neutral-200 dark:border-slate-700 rounded-md p-1 bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100">
                        
                        {{-- Indikator Upload --}}
                        <div wire:loading wire:target="form_proof_file" class="text-xs text-amber-600 mt-1">
                            Mengunggah berkas...
                        </div>

                        @error('form_proof_file') 
                            <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Transaksi' : 'Simpan Transaksi' }}</span>
                            <span wire:loading>{{ $isEditing ? 'Memperbarui...' : 'Menyimpan...' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedTransaction)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-md border border-neutral-200 dark:border-slate-700 shadow-xl overflow-hidden animate-in fade-in zoom-in duration-150 flex flex-col max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Rincian Transaksi</h3>
                        <p class="text-xs text-neutral-400">{{ $selectedTransaction->reference_no ?? '#TX-'.$selectedTransaction->id }}</p>
                    </div>
                    <button wire:click="closeDetail" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-lg font-bold">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-4 text-xs overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4 pb-3 border-b border-neutral-100 dark:border-slate-700">
                        <div>
                            <span class="text-neutral-400">Unit Usaha:</span>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-200 mt-0.5">{{ $selectedTransaction->unit->name ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-neutral-400">Tanggal:</span>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-200 mt-0.5">{{ optional($selectedTransaction->transaction_date)->format('d F Y') }}</p>
                        </div>
                        <div>
                            <span class="text-neutral-400">Kategori:</span>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-200 mt-0.5">{{ $selectedTransaction->category->name ?? 'Umum' }}</p>
                        </div>
                        <div>
                            <span class="text-neutral-400">Metode Pembayaran:</span>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-200 uppercase mt-0.5">{{ $selectedTransaction->payment_method ?? 'TUNAI' }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-neutral-400">Deskripsi / Catatan:</span>
                        <p class="font-medium text-neutral-800 dark:text-neutral-200 mt-1 bg-neutral-50 dark:bg-slate-900 p-3 rounded border border-neutral-100 dark:border-slate-700">
                            {{ $selectedTransaction->description ?? 'Tidak ada catatan.' }}
                        </p>
                    </div>

                    {{-- Section Bukti Transaksi --}}
                    <div>
                        <span class="text-neutral-400 block mb-1.5">Bukti Transaksi / Struk:</span>
                        
                        @if($selectedTransaction->proof_file)
                            @php
                                $cleanPath = ltrim(str_replace('public/', '', $selectedTransaction->proof_file), '/');
                                $fileUrl = asset('storage/' . $cleanPath);
                                $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            @endphp

                            <div class="space-y-2">
                                @if($isImage)
                                    <a href="{{ $fileUrl }}" target="_blank" class="block group relative overflow-hidden rounded-md border border-neutral-200 dark:border-slate-700 bg-neutral-100 dark:bg-slate-900">
                                        <img src="{{ $fileUrl }}" alt="Bukti Transaksi" class="w-full object-contain max-h-44 rounded group-hover:scale-105 transition-transform duration-200">
                                        <div class="absolute inset-0 bg-neutral-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-medium text-xs gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Buka Ukuran Penuh
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 text-xs text-blue-900 dark:text-red-400 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 rounded-md font-semibold hover:bg-red-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Unduh Dokumen Lampiran ({{ strtoupper($ext) }})
                                    </a>
                                @endif

                                <div class="flex justify-end">
                                    <button wire:click="deleteProof" wire:confirm="Apakah Anda yakin ingin menghapus bukti transaksi ini?" class="text-[11px] text-rose-600 hover:text-rose-700 dark:text-rose-400 font-medium">
                                        Hapus Bukti Ini
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Form Upload jika Belum Ada Bukti --}}
                            <div class="p-3 border-2 border-dashed border-neutral-200 dark:border-slate-700 rounded-md bg-neutral-50/50 dark:bg-slate-900/50 text-center">
                                <input type="file" wire:model="proofFile" id="proofInput" class="hidden">
                                <label for="proofInput" class="cursor-pointer block">
                                    <svg class="w-6 h-6 mx-auto text-neutral-400 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Klik untuk memilih file struk</span>
                                    <span class="block text-[10px] text-neutral-400 mt-0.5">JPG, PNG, WEBP, PDF (Maks. 3MB)</span>
                                </label>

                                @error('proofFile') 
                                    <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> 
                                @enderror

                                <div wire:loading wire:target="proofFile" class="text-blue-500 text-[10px] mt-2">
                                    Memproses berkas...
                                </div>

                                @if($proofFile)
                                    <div class="mt-3 pt-2 border-t border-neutral-200 dark:border-slate-700 flex items-center justify-between">
                                        <span class="text-[11px] text-neutral-700 dark:text-neutral-300 truncate max-w-[200px]">{{ $proofFile->getClientOriginalName() }}</span>
                                        <button wire:click="uploadProof" wire:loading.attr="disabled" class="px-3 py-1 bg-emerald-600 text-white rounded text-[11px] font-semibold hover:bg-emerald-700 disabled:opacity-50">
                                            Simpan Struk
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <span class="text-neutral-400">Status:</span>
                            <div class="mt-0.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase
                                    {{ $selectedTransaction->status === 'completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : ($selectedTransaction->status === 'pending' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400') }}">
                                    {{ $selectedTransaction->status }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-neutral-400">Nominal:</span>
                            <p class="text-lg font-bold {{ $selectedTransaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $selectedTransaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-neutral-50 dark:bg-slate-900 border-t border-neutral-100 dark:border-slate-700 text-right shrink-0">
                    <button wire:click="closeDetail" class="px-4 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md hover:bg-neutral-100 dark:hover:bg-slate-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Import Excel --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-md border border-neutral-200 dark:border-slate-700 shadow-xl overflow-hidden animate-in fade-in zoom-in duration-150">
                <div class="p-4 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Import Transaksi Massal</h3>
                    <button wire:click="closeImportModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-lg font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="importExcel" class="p-4 space-y-4 text-xs">
                    <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-md p-3 text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold">Petunjuk Pengisian:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-700 dark:text-amber-400">
                            <li>Unduh template terlebih dahulu untuk format yang sesuai.</li>
                            <li>Gunakan pilihan dropdown yang tersedia pada kolom Excel.</li>
                            <li>Pastikan format tanggal menggunakan <code class="font-bold">YYYY-MM-DD</code>.</li>
                        </ul>
                    </div>

                    <div>
                        <button type="button" wire:click="downloadTemplate" class="w-full py-2 px-3 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-md hover:bg-emerald-100 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Unduh Template (.XLSX)
                        </button>
                    </div>

                    <div>
                        <label class="block font-medium text-neutral-700 dark:text-neutral-300 mb-1">Unggah Berkas Excel</label>
                        <input type="file" wire:model="excel_file" accept=".xlsx, .xls" class="w-full text-xs border border-neutral-200 dark:border-slate-700 rounded p-1.5 bg-neutral-50 dark:bg-slate-900 text-neutral-800 dark:text-neutral-200">
                        
                        <div wire:loading wire:target="excel_file" class="text-amber-600 mt-1">Membaca file...</div>
                        @error('excel_file') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-neutral-100 dark:border-slate-700">
                        <button type="button" wire:click="closeImportModal" class="px-3 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded hover:bg-neutral-200">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded transition flex items-center gap-1.5">
                            <span wire:loading.remove wire:target="importExcel">Import Data</span>
                            <span wire:loading wire:target="importExcel">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Detail Error Import Excel --}}
    @if($showErrorModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-fadeIn">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-neutral-200 dark:border-slate-700">
            
            {{-- Header Modal --}}
            <div class="flex items-start justify-between border-b border-neutral-100 dark:border-slate-700/80 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Import File Dibatalkan</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Ditemukan data yang tidak sesuai pada baris dan kolom berikut:</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showErrorModal', false)" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Tabel Rincian Lokasi Error --}}
            <div class="max-h-64 overflow-y-auto border border-neutral-200 dark:border-slate-700 rounded-xl mb-5">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-neutral-50 dark:bg-slate-900 text-neutral-600 dark:text-neutral-400 font-semibold sticky top-0 z-10 border-b dark:border-slate-700">
                        <tr>
                            <th class="p-3 text-center w-20">Baris</th>
                            <th class="p-3">Nama Kolom</th>
                            <th class="p-3">Input Pengguna</th>
                            <th class="p-3">Keterangan Masalah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800 text-neutral-700 dark:text-neutral-300">
                        @foreach($importErrors as $err)
                            <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-950/20 transition-colors">
                                <td class="p-3 text-center font-bold text-rose-600 dark:text-rose-400 bg-rose-50/30 dark:bg-rose-950/10">
                                    Baris {{ $err['row'] }}
                                </td>
                                <td class="p-3 font-semibold text-neutral-800 dark:text-neutral-200">
                                    {{ $err['column'] }}
                                </td>
                                <td class="p-3">
                                    <code class="px-2 py-0.5 rounded bg-neutral-100 dark:bg-slate-700 text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
                                        {{ $err['value'] }}
                                    </code>
                                </td>
                                <td class="p-3 text-rose-600 dark:text-rose-400 font-medium">
                                    {{ $err['messages'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-neutral-400">Total item bermasalah: <strong>{{ count($importErrors) }}</strong></span>
                <button type="button" 
                        wire:click="$set('showErrorModal', false)" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-neutral-900 hover:bg-neutral-800 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-all shadow-sm">
                    Perbaiki File & Coba Lagi
                </button>
            </div>
        </div>
    </div>
    @endif
</div>