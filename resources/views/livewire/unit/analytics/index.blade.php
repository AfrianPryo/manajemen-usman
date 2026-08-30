<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- ================= HEADER & FILTER ================= --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Statistik Usaha</h1>
            </div>
            <p class="text-xs text-neutral-400 mt-1">
                Ringkasan performa finansial dan operasional unit usaha {{ $unit->name }}.
            </p>
        </div>

        {{-- Filter Rentang Waktu --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <select wire:model.live="periodFilter" class="px-2 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[2px] transition-all shadow-sm shadow-black/[0.02] focus:outline-none focus:ring-2 focus:ring-blue-900/10 focus:border-blue-900 cursor-pointer">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Kustom Rentang Tanggal</option>
            </select>

            @if($periodFilter === 'custom')
                <div class="flex items-center gap-1.5">
                    <input type="date" wire:model.live="startDate" class="px-3 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 shadow-sm shadow-black/[0.02]">
                    <span class="text-neutral-400 text-xs">s/d</span>
                    <input type="date" wire:model.live="endDate" class="px-3 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 shadow-sm shadow-black/[0.02]">
                </div>
            @endif
        </div>
    </div>

    {{-- ================= KARTU METRIK FINANSIAL & OPERASIONAL ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Pendapatan --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pendapatan</p>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Bruto akumulasi pendapatan</p>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pengeluaran</p>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9.75a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5zm10.5 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Total biaya operasional</p>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Laba Bersih</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a.5.5 0 00.71 0L21.75 6M21.75 6v5.25m0-5.25h-5.25"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold tracking-tight {{ $netProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </p>
            <p class="mt-2 text-[11px] text-neutral-400">Margin bersih operasional</p>
        </div>

        {{-- Total Transaksi --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Transaksi</p>
                <span class="p-2 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Jumlah transaksi berhasil</p>
        </div>
    </div>

    {{-- ================= GRAFIK KONTRIBUSI & PERINGKAT PENDAPATAN PER KATEGORI ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Widget Chart Donut ApexCharts --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-6 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Kontribusi Pendapatan per Kategori</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Proporsi pembagian total pendapatan unit usaha ini per kategori transaksi</p>
                </div>
            </div>

            {{-- Container ApexChart --}}
            <div x-data="{
                    labels: @js($revenueContribution['labels']),
                    series: @js($revenueContribution['series']),
                    chart: null,
                    renderChart() {
                        if (this.chart) this.chart.destroy();
                        let options = {
                            series: this.series,
                            labels: this.labels,
                            chart: {
                                type: 'donut',
                                height: 310,
                                fontFamily: 'Plus Jakarta Sans, Inter, sans-serif'
                            },
                            colors: ['#2563EB', '#38BDF8', '#F43F5E', '#8B5CF6', '#F59E0B'],
                            stroke: { width: 3, colors: ['#ffffff'] },
                            legend: {
                                position: 'bottom',
                                fontSize: '12px',
                                fontWeight: 500,
                                labels: { colors: '#64748B' },
                                markers: { radius: 12 }
                            },
                            dataLabels: { enabled: false },
                            tooltip: {
                                theme: 'light',
                                y: {
                                    formatter: function(val) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                }
                            },
                            plotOptions: {
                                pie: {
                                    expandOnClick: true,
                                    donut: {
                                        size: '74%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                fontSize: '12px',
                                                fontWeight: '600',
                                                color: '#94A3B8',
                                                offsetY: -4
                                            },
                                            value: {
                                                show: true,
                                                fontSize: '20px',
                                                fontWeight: '800',
                                                color: '#0F172A',
                                                offsetY: 6,
                                                formatter: function (val) {
                                                    return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                                }
                                            },
                                            total: {
                                                show: true,
                                                label: 'Total Pendapatan',
                                                fontSize: '12px',
                                                fontWeight: '600',
                                                color: '#94A3B8',
                                                formatter: function (w) {
                                                    let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    return 'Rp ' + (total / 1000000).toFixed(1) + ' Jt';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.chart = new ApexCharts(this.$refs.chart, options);
                        this.chart.render();
                    },
                    init() {
                        this.renderChart();
                    }
                }"
                x-effect="labels = @js($revenueContribution['labels']); series = @js($revenueContribution['series']); renderChart();"
                class="w-full flex justify-center items-center py-2">
                <div x-ref="chart" class="w-full"></div>
            </div>
        </div>

        {{-- Widget Rincian & Peringkat Pendapatan per Kategori --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-6 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Peringkat Kategori</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Kontribusi kategori pendapatan</p>
                    </div>
                    <span class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 bg-neutral-100/80 dark:bg-slate-900/80 px-2.5 py-1 rounded-[2px] border border-neutral-200/50 dark:border-slate-700">
                        Top 5
                    </span>
                </div>

                <div class="space-y-3.5">
                    @php
                        $colors = [
                            ['bg' => 'bg-blue-600', 'badge' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'],
                            ['bg' => 'bg-sky-400', 'badge' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400'],
                            ['bg' => 'bg-rose-500', 'badge' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400'],
                            ['bg' => 'bg-purple-500', 'badge' => 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400'],
                            ['bg' => 'bg-amber-500', 'badge' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400'],
                        ];
                    @endphp

                    @forelse($revenueContribution['labels'] as $index => $label)
                        @php
                            $nominal = $revenueContribution['series'][$index];
                            $percent = $revenueContribution['percentages'][$index];
                            $colorScheme = $colors[$index % count($colors)];
                        @endphp
                        <div class="group p-2 rounded-2xl hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2.5 truncate max-w-[60%]">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $colorScheme['bg'] }} shrink-0"></span>
                                    <span class="font-bold text-neutral-800 dark:text-neutral-200 truncate group-hover:text-neutral-900 dark:group-hover:text-white">{{ $label }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $colorScheme['badge'] }}">
                                        {{ $percent }}%
                                    </span>
                                    <span class="font-extrabold text-neutral-900 dark:text-white text-xs">
                                        Rp {{ number_format($nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="w-full bg-neutral-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                <div class="{{ $colorScheme['bg'] }} h-full rounded-full transition-all duration-700" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-neutral-400">Belum ada data kontribusi pendapatan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SECTION GRAFIK TREN ARUS KAS ================= --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-slate-100 dark:border-slate-700/60 p-6 shadow-sm transition-all">

        {{-- Header & Filter Grafik Arus Kas --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">Statistics</h2>
                <p class="text-xs text-slate-400 mt-0.5">Grafik Tren Arus Kas Masuk & Keluar</p>
            </div>

            <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                {{-- Legend Minimalis --}}
                <div class="flex items-center gap-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <span>Pendapatan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-300"></span>
                        <span>Pengeluaran</span>
                    </div>
                </div>

                {{-- Select Filter Waktu & Custom Date --}}
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <select wire:model.live="cashflowPeriod" class="appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold py-2 pl-3.5 pr-8 rounded-[2px] focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                            <option value="this_week">Minggu ini</option>
                            <option value="this_month">Bulan ini</option>
                            <option value="last_30_days">30 Hari Terakhir</option>
                            <option value="this_year">Tahun ini</option>
                            <option value="custom">Kustom Tanggal</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </div>
                    </div>

                    @if($cashflowPeriod === 'custom')
                        <div class="flex items-center gap-1.5">
                            <input type="date" wire:model.live="cfStartDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none">
                            <span class="text-slate-400 text-xs font-bold">-</span>
                            <input type="date" wire:model.live="cfEndDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Container Chart --}}
        <div
            wire:ignore
            x-data="{
                chart: null,
                renderChart() {
                    const labels = Array.from($wire.chartLabels || []);
                    const revenue = Array.from($wire.revenueChartData || []);
                    const expense = Array.from($wire.expenseChartData || []);

                    if (!labels.length) return;

                    const ctx = document.getElementById('cashflowChart');
                    if (!ctx) return;

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : '#f1f5f9';
                    const textColor = isDark ? '#94a3b8' : '#64748b';

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Pendapatan',
                                    data: revenue,
                                    backgroundColor: '#2563eb',
                                    hoverBackgroundColor: '#1d4ed8',
                                    borderRadius: 10,
                                    borderSkipped: false,
                                    barPercentage: 0.55,
                                    categoryPercentage: 0.65
                                },
                                {
                                    label: 'Pengeluaran',
                                    data: expense,
                                    backgroundColor: '#7dd3fc',
                                    hoverBackgroundColor: '#38bdf8',
                                    borderRadius: 10,
                                    borderSkipped: false,
                                    barPercentage: 0.55,
                                    categoryPercentage: 0.65
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    titleColor: '#94a3b8',
                                    bodyColor: '#ffffff',
                                    titleFont: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                                    bodyFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                                    cornerRadius: 8,
                                    displayColors: false,
                                    caretSize: 5,
                                    callbacks: {
                                        title: function(context) {
                                            return context[0].label;
                                        },
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            let val = context.parsed.y !== null ? 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) : 'Rp 0';
                                            return `${label}: ${val}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: {
                                        color: textColor,
                                        font: { family: 'Plus Jakarta Sans, sans-serif', size: 11, weight: '600' },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: labels.length > 20 ? 12 : labels.length
                                    }
                                },
                                y: {
                                    grid: {
                                        color: gridColor,
                                        borderDash: [3, 3],
                                        drawTicks: false
                                    },
                                    border: { display: false },
                                    ticks: {
                                        color: textColor,
                                        padding: 10,
                                        font: { family: 'Plus Jakarta Sans, sans-serif', size: 11 },
                                        callback: function(value) {
                                            if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                            return 'Rp ' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }"
            x-effect="renderChart()"
            class="h-80 w-full"
        >
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>

    {{-- ================= PERFORMA PER KATEGORI TRANSAKSI ================= --}}
    <div class="flex flex-col gap-5 pt-2">

    {{-- Tabel Performa Seluruh Kategori Transaksi --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700/60 overflow-hidden shadow-sm">

        {{-- Header & Filter Rentang Waktu --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-neutral-100 dark:border-slate-700">
            <div>
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Performa per Kategori Transaksi</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Laporan finansial lengkap mencakup seluruh kategori transaksi unit usaha ini (untung & rugi)</p>
            </div>

            {{-- Select Filter Waktu & Custom Date Input --}}
            <div class="flex items-center gap-2">
                <div class="relative">
                    <select wire:model.live="categoryPeriod" class="appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold py-2 pl-3.5 pr-8 rounded-[2px] focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                        <option value="this_week">Minggu ini</option>
                        <option value="this_month">Bulan ini</option>
                        <option value="last_30_days">30 Hari Terakhir</option>
                        <option value="this_year">Tahun ini</option>
                        <option value="custom">Kustom Tanggal</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                        </svg>
                    </div>
                </div>

                @if($categoryPeriod === 'custom')
                    <div class="flex items-center gap-1.5">
                        <input type="date" wire:model.live="categoryStartDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none">
                        <span class="text-slate-400 text-xs font-bold">-</span>
                        <input type="date" wire:model.live="categoryEndDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none">
                    </div>
                @endif
            </div>
        </div>

        {{-- Body Tabel --}}
        <div class="w-full">
            <table class="w-full text-sm text-left table-auto">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 sm:px-5 py-3.5">Kategori Transaksi</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Transaksi</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Pendapatan</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Pengeluaran</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Laba / (Rugi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($topCategories as $item)
                        @php
                            $expense = $item->total_expense ?? 0;
                            $profit = $item->total_profit ?? ($item->total_income - $expense);
                        @endphp
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 sm:px-5 py-3.5 font-semibold text-neutral-900 dark:text-white text-xs sm:text-sm">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right text-xs text-neutral-600 dark:text-neutral-300 font-medium">
                                {{ number_format($item->total_tx, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($item->total_income, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right text-xs font-bold text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($expense, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right text-xs font-bold {{ $profit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $profit < 0 ? '- Rp ' . number_format(abs($profit), 0, ',', '.') : 'Rp ' . number_format($profit, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-5 py-8 text-center text-xs text-neutral-400">
                                Belum ada data kategori transaksi tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
