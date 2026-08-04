<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics & Statistik</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Performa keseluruhan sistem.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Unit</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $totalUnits }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Produk</p>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ $totalProducts }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Transaksi</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">{{ $totalTransactions }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Laba Bersih</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">Rp {{ number_format($totalRevenue - $totalExpense, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-600 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Grafik Analytics</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Grafik interaktif (Chart.js / ApexCharts) akan ditampilkan di sini.</p>
    </div>
</div>