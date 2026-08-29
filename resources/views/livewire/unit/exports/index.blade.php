<div class="w-full max-w-[1100px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Filter Periode (dipakai oleh export Transaksi & Laporan Keuangan) --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
        <p class="text-xs font-bold text-neutral-900 dark:text-white mb-3">Periode Export</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md">
            <div>
                <label class="text-[11px] font-medium text-neutral-400">Dari Tanggal</label>
                <input type="date" wire:model="startDate" class="mt-1 w-full text-xs rounded-md border-neutral-200 dark:border-slate-600 dark:bg-slate-700">
            </div>
            <div>
                <label class="text-[11px] font-medium text-neutral-400">Sampai Tanggal</label>
                <input type="date" wire:model="endDate" class="mt-1 w-full text-xs rounded-md border-neutral-200 dark:border-slate-600 dark:bg-slate-700">
            </div>
        </div>
        <p class="mt-2 text-[11px] text-neutral-400">Periode ini dipakai untuk export Transaksi & Laporan Keuangan. Export Stok selalu menampilkan kondisi stok terkini.</p>
    </div>

    {{-- Kartu Export --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 inline-flex">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m0 0v4.5m0-4.5H10.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="mt-3 text-xs font-bold text-neutral-900 dark:text-white">Transaksi</p>
                <p class="mt-1 text-[11px] text-neutral-400 leading-relaxed">Semua transaksi pemasukan & pengeluaran unit ini sesuai periode di atas.</p>
            </div>
            <button wire:click="exportTransactions" wire:loading.attr="disabled"
                class="mt-4 w-full text-xs font-semibold px-3 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 transition">
                Download Excel
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 inline-flex">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375C2.754 3.75 2.25 4.254 2.25 4.875v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </span>
                <p class="mt-3 text-xs font-bold text-neutral-900 dark:text-white">Stok / Inventaris</p>
                <p class="mt-1 text-[11px] text-neutral-400 leading-relaxed">Kondisi stok seluruh produk unit ini saat ini (habis, menipis, normal).</p>
            </div>
            <button wire:click="exportStock" wire:loading.attr="disabled"
                class="mt-4 w-full text-xs font-semibold px-3 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 transition">
                Download Excel
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500 inline-flex">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                </span>
                <p class="mt-3 text-xs font-bold text-neutral-900 dark:text-white">Laporan Keuangan</p>
                <p class="mt-1 text-[11px] text-neutral-400 leading-relaxed">Ringkasan & rincian per kategori keuangan unit ini sesuai periode di atas.</p>
            </div>
            <button wire:click="exportFinanceReport" wire:loading.attr="disabled"
                class="mt-4 w-full text-xs font-semibold px-3 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 transition">
                Download Excel
            </button>
        </div>

    </div>

</div>
