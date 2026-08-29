<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Dashboard {{ $unit->name }}</h1>
            </div>
            <p class="text-sm tracking-tight text-neutral-400 mt-1">
                Ikhtisar transaksi, stok, dan aktivitas unit usaha Anda.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">

            {{-- Tombol Export --}}
            <button type="button"
                    wire:click="export"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-[3px] hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                <svg wire:loading.remove wire:target="export" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <svg wire:loading wire:target="export" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="export">Export</span>
                <span wire:loading wire:target="export">Mengunduh...</span>
            </button>
        </div>
    </div>

    {{-- ================= KARTU RINGKASAN OMZET (FILTER PERIODE) ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Omzet Bersih (kartu utama, dengan filter periode) --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between gap-3 xl:col-span-2">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-500 dark:text-blue-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 truncate">Omzet Bersih</p>
                        <p class="text-[10px] text-neutral-400 dark:text-neutral-500 font-medium leading-none mt-0.5 truncate">{{ $periodLabel }}</p>
                    </div>
                </div>

                <select wire:model.live="periodFilter"
                    class="px-2 py-1 text-[11px] font-medium bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-[2px] focus:ring-1 focus:ring-blue-900 focus:border-blue-900 outline-none transition-all cursor-pointer shrink-0">
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="this_quarter">Kuartal Ini</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="custom">Custom...</option>
                </select>
            </div>

            @if ($periodFilter === 'custom')
                <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-1.5 pt-2 border-t border-dashed border-neutral-100 dark:border-slate-700/60">
                    <span class="text-[10px] text-neutral-400 font-medium shrink-0">Rentang Tanggal:</span>
                    <div class="flex items-center gap-1.5 w-full sm:w-auto">
                        <input type="date" wire:model.live="startDate"
                            class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded outline-none focus:border-blue-500">
                        <span class="text-[10px] text-neutral-400">-</span>
                        <input type="date" wire:model.live="endDate"
                            class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded outline-none focus:border-blue-500">
                    </div>
                </div>
            @endif

            <div class="flex items-baseline justify-between pt-1 flex-wrap gap-2">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $netRevenue }}</p>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Masuk {{ $totalIncome }}</span>
                    <span class="text-rose-600 dark:text-rose-400 font-semibold">Keluar {{ $totalExpense }}</span>
                </div>
            </div>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Jumlah Transaksi</p>
                <span class="p-2 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-500 dark:text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $trxCount }}</p>
                <p class="mt-2 text-xs text-neutral-400">Rata-rata {{ $avgTrxValue }} / transaksi</p>
            </div>
        </div>

        {{-- Stok Menipis --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Stok Menipis</p>
                <span class="p-2 rounded-xl {{ $lowStockCount === 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-500 dark:text-amber-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $lowStockCount }}</p>
                <p class="mt-2 text-xs text-neutral-400">dari {{ $totalProducts }} produk terdaftar</p>
            </div>
        </div>
    </div>

    {{-- ================= TREN OMZET & STOK MENIPIS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Widget Chart Tren Omzet (ApexCharts + Alpine.js) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Tren Omzet Harian</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">{{ $periodLabel }}</p>
                </div>
            </div>

            @if (count($revenueTrend['labels']) > 0)
                <div x-data="{
                    init() {
                        let options = {
                            series: [{ name: 'Omzet', data: @js($revenueTrend['series']) }],
                            chart: {
                                type: 'area',
                                height: 260,
                                toolbar: { show: false },
                                fontFamily: 'inherit',
                            },
                            colors: ['#2563EB'],
                            stroke: { curve: 'smooth', width: 2 },
                            fill: {
                                type: 'gradient',
                                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] }
                            },
                            dataLabels: { enabled: false },
                            grid: { borderColor: '#F1F5F9', strokeDashArray: 4 },
                            xaxis: {
                                categories: @js($revenueTrend['labels']),
                                labels: { style: { colors: '#94A3B8', fontSize: '10px' } },
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: '#94A3B8', fontSize: '10px' },
                                    formatter: function (val) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val); }
                                }
                            },
                            tooltip: {
                                theme: 'light',
                                y: { formatter: function (val) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(val); } }
                            },
                        };
                        let chart = new ApexCharts(this.$refs.chart, options);
                        chart.render();
                    }
                }" class="w-full">
                    <div x-ref="chart" class="w-full"></div>
                </div>
            @else
                <p class="text-sm text-neutral-400 py-10 text-center">Belum ada data transaksi pemasukan pada periode ini.</p>
            @endif
        </div>

        {{-- Widget Stok Menipis --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Stok Menipis</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Produk perlu restock</p>
                    </div>
                    <span class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 bg-neutral-100/80 dark:bg-slate-900/80 px-2.5 py-1 rounded-[2px] border border-neutral-200/50 dark:border-slate-700">
                        Top 6
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($lowStockProducts as $product)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-neutral-700 dark:text-neutral-300 truncate max-w-[60%]">{{ $product->name }}</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $product->stock }} / {{ $product->min_stock }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400">Semua stok produk dalam kondisi aman.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TRANSAKSI TERKINI & AKTIVITAS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Transaksi Terkini --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between mb-4 gap-2 flex-wrap">
                <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Transaksi Terkini</h2>
                <input type="text" wire:model.live.debounce.400ms="searchTransaction" placeholder="Cari transaksi..."
                       class="px-2.5 py-1 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-[2px] outline-none focus:border-blue-500 w-36">
            </div>

            <div class="space-y-1">
                @forelse ($recentTransactions as $trx)
                    <div class="flex items-center justify-between px-2.5 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200 truncate">
                                {{ $trx->description ?? ($trx->category->name ?? 'Transaksi') }}
                            </p>
                            <p class="text-[10px] text-neutral-400">{{ optional($trx->transaction_date)->translatedFormat('d M Y') }} &middot; {{ $trx->user->name ?? '-' }}</p>
                        </div>
                        <span class="text-xs font-bold shrink-0 {{ $trx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-6 text-center">Belum ada transaksi tercatat.</p>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas Terkini --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight mb-4">Aktivitas Terkini</h2>

            <div class="space-y-1">
                @forelse ($recentActivity as $log)
                    @php $info = $this->eventInfo($log->event); @endphp
                    <div class="flex items-center justify-between px-2.5 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $info['class'] }}">
                                {{ $info['label'] }}
                            </span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $log->user->name ?? 'Sistem' }}</span>
                        </div>
                        <span class="text-[10px] text-neutral-400 shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-6 text-center">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= TRANSAKSI BERULANG MENDATANG ================= --}}
    @if ($upcomingRecurring->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight mb-4">Transaksi Berulang Mendatang</h2>
            <div class="space-y-1">
                @foreach ($upcomingRecurring as $rt)
                    <div class="flex items-center justify-between px-2.5 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                        <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 truncate max-w-[40%]">{{ $rt->title }}</span>
                        <span class="text-[11px] text-neutral-400">Jatuh tempo {{ optional($rt->next_run_date)->translatedFormat('d M Y') }}</span>
                        <span class="text-xs font-bold {{ $rt->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($rt->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
