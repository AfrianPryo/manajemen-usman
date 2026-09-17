<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-none bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- ================= HEADER & FILTER ================= --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-none border border-neutral-100 dark:border-slate-700">
        <div>
            <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Analytics & Statistik Usaha</h1>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">
                Ringkasan performa finansial dan operasional seluruh unit usaha.
            </p>
        </div>

        {{-- Filter Rentang Waktu & Unit --}}
        <div class="flex flex-wrap items-center justify-end gap-2">
            <div class="flex items-stretch border border-neutral-200 dark:border-slate-700 divide-x divide-neutral-200 dark:divide-slate-700 shrink-0">
                <select wire:model.live="selectedUnit" class="px-3 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 focus:outline-none focus:bg-neutral-50 dark:focus:bg-slate-800 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($unitsList as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="periodFilter" class="px-3 py-2 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 focus:outline-none focus:bg-neutral-50 dark:focus:bg-slate-800 cursor-pointer">
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="custom">Kustom Rentang Tanggal</option>
                </select>
            </div>

            @if($periodFilter === 'custom')
                <div class="flex items-center gap-1.5 border border-neutral-200 dark:border-slate-700 px-3 py-2 shrink-0">
                    <input type="date" wire:model.live="startDate" class="text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-transparent focus:outline-none">
                    <span class="text-neutral-300 dark:text-neutral-600 text-xs">–</span>
                    <input type="date" wire:model.live="endDate" class="text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-transparent focus:outline-none">
                </div>
            @endif
        </div>
    </div>

    {{-- ================= KARTU METRIK FINANSIAL & OPERASIONAL ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Pendapatan --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Pendapatan</p>
                <x-heroicon-o-currency-dollar stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-400">Bruto akumulasi pendapatan</p>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Pengeluaran</p>
                <x-heroicon-o-banknotes stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-400">Total biaya operasional</p>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Laba Bersih</p>
                <x-heroicon-o-arrow-trending-up stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight {{ ($totalRevenue - $totalExpense) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                Rp {{ number_format($totalRevenue - $totalExpense, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] text-neutral-400">Margin bersih operasional</p>
        </div>

        {{-- Total Transaksi --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Transaksi</p>
                <x-heroicon-o-shopping-bag stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            <div class="mt-1 flex items-center gap-3 text-[10px] text-neutral-400">
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ number_format($incomeCount, 0, ',', '.') }} Masuk
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    {{ number_format($expenseCount, 0, ',', '.') }} Keluar
                </span>
            </div>
        </div>
    </div>

    {{-- ================= GRAFIK KONTRIBUSI & PERINGKAT OMZET PER UNIT USAHA ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Widget Chart Donut ApexCharts --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Kontribusi Omzet per Unit Usaha</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Proporsi pembagian total omzet seluruh unit bisnis</p>
                </div>
            </div>

            {{-- Container ApexChart --}}
            {{-- wire:ignore WAJIB di sini: elemen ini digambar ApexCharts di sisi
                 browser, jadi Livewire tidak boleh ikut memorph isinya saat filter
                 berubah (itu penyebab grafik sempat muncul lalu hilang). Data
                 diperbarui secara reaktif lewat $wire.revenueLabels/revenueSeries,
                 mengikuti pola yang sama dengan grafik arus kas di bawah. --}}
            <div
                wire:ignore
                x-data="{
                    chart: null,
                    renderChart() {
                        const labels = Array.from($wire.revenueLabels || []);
                        const series = Array.from($wire.revenueSeries || []);

                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }

                        let options = {
                            series: series,
                            labels: labels,
                            chart: {
                                type: 'donut',
                                height: 310,
                                fontFamily: 'Plus Jakarta Sans, Inter, sans-serif'
                            },
                            colors: ['#0d3b74', '#2563EB', '#38BDF8', '#64748B', '#94A3B8'],
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
                                                label: 'Total Omzet',
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
                    }
                }"
                x-effect="renderChart()"
                class="w-full flex justify-center items-center py-2">
                <div x-ref="chart" class="w-full"></div>
            </div>
        </div>

        {{-- Widget Rincian & Peringkat Pendapatan Unit --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Peringkat Omzet</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Kontribusi unit bisnis</p>
                    </div>
                    <span class="text-[11px] font-bold text-[#0d3b74] dark:text-neutral-400 bg-blue-50 dark:bg-slate-900/80 px-2.5 py-1 rounded-none border border-blue-100 dark:border-slate-700">
                        Top 5
                    </span>
                </div>

                <div class="space-y-3.5">
                    @php
                        $colors = [
                            ['bg' => 'bg-[#0d3b74]', 'badge' => 'bg-blue-50 dark:bg-blue-950/50 text-[#0d3b74] dark:text-blue-400'],
                            ['bg' => 'bg-blue-600', 'badge' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'],
                            ['bg' => 'bg-sky-400', 'badge' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400'],
                            ['bg' => 'bg-slate-400', 'badge' => 'bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400'],
                            ['bg' => 'bg-slate-300', 'badge' => 'bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400'],
                        ];
                    @endphp

                    @forelse($revenueContribution['labels'] as $index => $label)
                        @php
                            $nominal = $revenueContribution['series'][$index];
                            $percent = $revenueContribution['percentages'][$index];
                            $colorScheme = $colors[$index % count($colors)];
                        @endphp
                        <div class="group p-2 rounded-none hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2.5 truncate max-w-[60%]">
                                    <span class="w-2.5 h-2.5 rounded-none {{ $colorScheme['bg'] }} shrink-0"></span>
                                    <span class="font-bold text-neutral-800 dark:text-neutral-200 truncate group-hover:text-neutral-900 dark:group-hover:text-white">{{ $label }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-none {{ $colorScheme['badge'] }}">
                                        {{ $percent }}%
                                    </span>
                                    <span class="font-extrabold text-neutral-900 dark:text-white text-xs">
                                        Rp {{ number_format($nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="w-full bg-neutral-100 dark:bg-slate-700 h-2 rounded-none overflow-hidden">
                                <div class="{{ $colorScheme['bg'] }} h-full rounded-none transition-all duration-700" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-neutral-400">Belum ada data kontribusi omzet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SECTION GRAFIK TREN ARUS KAS ================= --}}
    <div class="bg-white dark:bg-slate-800 rounded-none border border-slate-100 dark:border-slate-700/60 p-5 shadow-sm shadow-black/[0.02] transition-all">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">Statistics</h2>
                <p class="text-xs text-slate-400 mt-0.5">Grafik Tren Arus Kas Masuk & Keluar</p>
            </div>

            {{-- Legend saja -- grafik ini sekarang mengikuti filter Unit Usaha &
                Periode global di bagian atas halaman, tidak punya filter sendiri lagi. --}}
            <div class="flex items-center gap-4 text-xs font-medium text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-0.5 rounded-full bg-[#0d3b74]"></span>
                    <span>Pendapatan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-0.5 rounded-full bg-sky-300"></span>
                    <span>Pengeluaran</span>
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
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Pendapatan',
                                    data: revenue,
                                    borderColor: '#0d3b74',
                                    backgroundColor: 'rgba(13, 59, 116, 0.06)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    pointHoverRadius: 4,
                                    pointHoverBackgroundColor: '#0d3b74',
                                    pointHoverBorderColor: '#ffffff',
                                    pointHoverBorderWidth: 2
                                },
                                {
                                    label: 'Pengeluaran',
                                    data: expense,
                                    borderColor: '#7dd3fc',
                                    backgroundColor: 'rgba(125, 211, 252, 0.08)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    pointHoverRadius: 4,
                                    pointHoverBackgroundColor: '#38bdf8',
                                    pointHoverBorderColor: '#ffffff',
                                    pointHoverBorderWidth: 2
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
                                    backgroundColor: isDark ? '#1e293b' : '#ffffff',
                                    titleColor: textColor,
                                    bodyColor: isDark ? '#ffffff' : '#0f172a',
                                    borderColor: isDark ? '#334155' : '#e2e8f0',
                                    borderWidth: 1,
                                    titleFont: { family: 'Plus Jakarta Sans', size: 10, weight: '500' },
                                    bodyFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                                    cornerRadius: 0,
                                    displayColors: true,
                                    boxWidth: 6,
                                    boxHeight: 6,
                                    usePointStyle: true,
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
                                        maxTicksLimit: labels.length > 20 ? 10 : labels.length
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

    {{-- ================= PERFORMA UNIT USAHA & PRODUK TERLARIS ================= --}}
    <div class="flex flex-col gap-5 pt-2">
        
    {{-- Tabel Performa Seluruh Unit Usaha --}}
    <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700/60 overflow-hidden shadow-sm shadow-black/[0.02]">
        
        {{-- Header & Filter Rentang Waktu --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-neutral-100 dark:border-slate-700">
            <div>
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Performa Seluruh Unit Usaha</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Laporan finansial lengkap mencakup seluruh unit usaha (untung & rugi)</p>
            </div>

            {{-- Select Filter Waktu & Custom Date Input --}}
            <div class="flex items-center gap-2">
                <div class="relative">
                    <select wire:model.live="unitPeriod" class="appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold py-2 pl-3.5 pr-8 rounded-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                        <option value="this_week">Minggu ini</option>
                        <option value="this_month">Bulan ini</option>
                        <option value="last_30_days">30 Hari Terakhir</option>
                        <option value="this_year">Tahun ini</option>
                        <option value="custom">Kustom Tanggal</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <x-heroicon-m-chevron-down class="w-3.5 h-3.5" />
                    </div>
                </div>

                @if($unitPeriod === 'custom')
                    <div class="flex items-center gap-1.5">
                        <input type="date" wire:model.live="unitStartDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-none focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        <span class="text-slate-400 text-xs font-bold">-</span>
                        <input type="date" wire:model.live="unitEndDate" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-none focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    </div>
                @endif
            </div>
        </div>

        {{-- Body Tabel --}}
        <div class="w-full">
            <table class="w-full text-sm text-left table-auto">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 sm:px-5 py-3.5">Unit Usaha</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Transaksi</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Pendapatan</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Pengeluaran</th>
                        <th class="px-4 sm:px-5 py-3.5 text-right">Laba / (Rugi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($topUnits as $item)
                        @continue(! is_object($item))
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
                            <td class="px-4 sm:px-5 py-3.5 text-right text-xs font-bold {{ $profit >= 0 ? 'text-[#0d3b74] dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $profit < 0 ? '- Rp ' . number_format(abs($profit), 0, ',', '.') : 'Rp ' . number_format($profit, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-5 py-8 text-center text-xs text-neutral-400">
                                Belum ada data unit usaha tersedia.
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