<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Dashboard {{ $unit->name }}</h1>
            </div>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">
                Ikhtisar transaksi, stok, dan aktivitas unit usaha Anda.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">

            {{-- Tombol Export --}}
            <button type="button"
                    wire:click="export"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#0d3b74] dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <x-heroicon-o-arrow-down-tray stroke-width="2.5" wire:loading.remove wire:target="export" class="w-4 h-4" />
                    <svg wire:loading wire:target="export" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                <span wire:loading.remove wire:target="export">Export</span>
                <span wire:loading wire:target="export">Mengunduh...</span>
            </button>
        </div>
    </div>

    {{--
        Badge perbandingan periode (period-over-period). $pct positif =
        naik, negatif = turun. $goodWhenUp menentukan warna: untuk metrik
        "omzet/transaksi naik itu bagus" set true (naik = hijau), untuk
        metrik "pengeluaran naik itu kurang bagus" set false (naik = merah).
    --}}
    @php
        $ppBadge = function (float $pct, bool $goodWhenUp = true) {
            $isUp   = $pct > 0;
            $isFlat = $pct == 0;
            $isGood = $isFlat ? null : ($goodWhenUp ? $isUp : ! $isUp);
            $color  = $isFlat ? 'text-neutral-400 bg-neutral-100 dark:bg-slate-900' : ($isGood ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50' : 'text-rose-600 bg-rose-50 dark:bg-rose-950/50');
            $arrow  = $isFlat ? '' : ($isUp ? '&uarr;' : '&darr;');
            return '<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-sm text-[10px] font-bold ' . $color . '">' . $arrow . ' ' . number_format(abs($pct), 1) . '%</span>';
        };
    @endphp

    {{-- ================= KARTU RINGKASAN OMZET (FILTER PERIODE) ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

        {{-- Omzet Bersih (kartu utama, dengan filter periode) --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between gap-3 xl:col-span-2">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div class="truncate">
                    <p class="text-xs text-neutral-400 truncate">Omzet Bersih</p>
                    <p class="text-[10px] text-neutral-400 dark:text-neutral-500 font-medium leading-none mt-1.5 truncate">{{ $periodLabel }}</p>
                </div>

                <select wire:model.live="periodFilter"
                    class="px-2 py-1 text-[11px] text-neutral-500 dark:text-neutral-300 bg-neutral-50 dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-none focus:ring-1 focus:ring-blue-900 focus:border-blue-900 outline-none cursor-pointer shrink-0">
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
                            class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-none outline-none focus:border-blue-500">
                        <span class="text-[10px] text-neutral-400">-</span>
                        <input type="date" wire:model.live="endDate"
                            class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-none outline-none focus:border-blue-500">
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

            <div class="flex items-center gap-1.5 pt-1" title="Dibandingkan periode {{ $periodComparison['previousPeriodLabel'] }}">
                {!! $ppBadge($periodComparison['netRevenueChangePct']) !!}
                <span class="text-[10px] text-neutral-400">vs periode sebelumnya</span>
            </div>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Jumlah Transaksi</p>
                <x-heroicon-o-shopping-bag stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <div class="mt-2">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $trxCount }}</p>
                <p class="mt-1 text-[11px] text-neutral-400">Rata-rata {{ $avgTrxValue }} / transaksi</p>
                <div class="mt-1.5">{!! $ppBadge($periodComparison['trxCountChangePct']) !!}</div>
            </div>
        </div>

        {{-- Stok Menipis --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Stok Menipis</p>
                <x-heroicon-o-cube stroke-width="1.5" class="w-4 h-4 {{ $lowStockCount === 0 ? 'text-neutral-300 dark:text-neutral-600' : 'text-amber-500 dark:text-amber-400' }}" />
            </div>
            <div class="mt-2">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $lowStockCount }}</p>
                <p class="mt-1 text-[11px] text-neutral-400">dari {{ $totalProducts }} produk terdaftar</p>
            </div>
        </div>

        {{-- Pelanggan Aktif (modul Customer sudah ada, sebelumnya belum tampil di dashboard) --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Pelanggan Aktif</p>
                <x-heroicon-o-users stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <div class="mt-2">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalActiveCustomers }}</p>
                <p class="mt-1 text-[11px] text-neutral-400">+{{ $newCustomersInRange }} pelanggan baru periode ini</p>
            </div>
        </div>
    </div>

    {{-- ================= TREN OMZET & STOK MENIPIS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Widget Chart Tren Omzet (ApexCharts + Alpine.js) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
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
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Stok Menipis</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Produk perlu restock</p>
                    </div>
                    <span class="text-[11px] font-bold text-[#0d3b74] dark:text-neutral-400 bg-blue-50 dark:bg-slate-900/80 px-2.5 py-1 rounded-sm border border-blue-100 dark:border-slate-700">
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

    {{-- ================= PENGELUARAN PER KATEGORI & ASET ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Rincian Pengeluaran per Kategori --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Rincian Pengeluaran per Kategori</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Top 5 &middot; {{ $periodLabel }}</p>
                </div>
            </div>

            <div class="space-y-3.5">
                @forelse ($expenseByCategory as $cat)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 truncate max-w-[60%]">{{ $cat['label'] }}</span>
                            <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-neutral-100 dark:bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-400 dark:bg-rose-500 rounded-full" style="width: {{ $cat['percentage'] }}%"></div>
                        </div>
                        <p class="text-[10px] text-neutral-400 mt-1">{{ $cat['percentage'] }}% dari total pengeluaran periode ini</p>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-6 text-center">Belum ada pengeluaran tercatat pada periode ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Aset Perlu Perhatian --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-neutral-400">Aset Perlu Perhatian</p>
                    <x-heroicon-o-exclamation-triangle stroke-width="1.5" class="w-4 h-4 {{ $assetsNeedAttention === 0 ? 'text-neutral-300 dark:text-neutral-600' : 'text-amber-500 dark:text-amber-400' }}" />
                </div>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $assetsNeedAttention }}</p>
                <p class="mt-2 text-[11px] text-neutral-400">dari {{ $totalAssets }} aset terdaftar &middot; kondisi rusak / sedang diperbaiki</p>
            </div>
        </div>
    </div>

    {{-- ================= TRANSAKSI TERKINI & AKTIVITAS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Transaksi Terkini --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between mb-4 gap-2 flex-wrap">
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Transaksi Terkini</h2>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.400ms="searchTransaction" placeholder="Cari transaksi..."
                           class="pl-8 pr-2.5 py-1.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-sm outline-none focus:border-blue-400 w-36 sm:w-44">
                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2" />
                </div>
            </div>

            <div class="divide-y divide-neutral-100 dark:divide-slate-700">
                @forelse ($recentTransactions as $trx)
                    <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200 truncate">
                                {{ $trx->description ?? ($trx->category->name ?? 'Transaksi') }}
                            </p>
                            <p class="text-[11px] text-neutral-400 truncate">{{ optional($trx->transaction_date)->translatedFormat('d M Y') }} &middot; {{ $trx->user->name ?? '-' }}</p>
                        </div>
                        <span class="text-xs font-bold shrink-0 {{ $trx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-8 text-center">Belum ada transaksi tercatat.</p>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas Terkini --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <h2 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Aktivitas Terkini</h2>

            <div class="divide-y divide-neutral-100 dark:divide-slate-700">
                @forelse ($recentActivity as $log)
                    @php $info = $this->eventInfo($log->event); @endphp
                    <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-sm border {{ $info['class'] }}">
                                {{ $info['label'] }}
                            </span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $log->user->name ?? 'Sistem' }}</span>
                        </div>
                        <span class="text-[11px] text-neutral-400 shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400 py-8 text-center">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= TRANSAKSI BERULANG MENDATANG ================= --}}
    @if ($upcomingRecurring->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <h2 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Transaksi Berulang Mendatang</h2>
            <div class="divide-y divide-neutral-100 dark:divide-slate-700">
                @foreach ($upcomingRecurring as $rt)
                    <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
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