<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Dashboard {{ $unit->name }}
            </h1>

            @if(auth()->user()->isMasterAdmin())
                <a href="{{ route('master.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                    ← Kembali ke Master
                </a>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">
                Selamat Datang di Unit {{ $unit->name }}
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Jurusan: <span class="font-medium text-blue-600 dark:text-blue-400">{{ $unit->department }}</span>
            </p>
            
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Transaksi Hari Ini</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-200 mt-1">Rp 0</p>
                </div>
                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                    <p class="text-sm text-green-600 dark:text-green-400 font-medium">Stok Produk</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-200 mt-1">0 Item</p>
                </div>
                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-100 dark:border-purple-800">
                    <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Laporan Bulan Ini</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-200 mt-1">Belum Ada</p>
                </div>
            </div>
        </div>
    </div>
</div>