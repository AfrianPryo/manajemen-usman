<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Konsolidasi</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Generate laporan lintas unit usaha.</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Filter Laporan</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                <select wire:model="period" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
                    <option value="daily">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mulai</label>
                <input type="date" wire:model="startDate" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selesai</label>
                <input type="date" wire:model="endDate" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">Export PDF</button>
                <button class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Export Excel</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Pemasukan</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">Rp 0</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-2">Rp 0</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Laba Bersih</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">Rp 0</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-600 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">Pilih filter dan klik Export untuk mengunduh laporan.</p>
    </div>
</div>