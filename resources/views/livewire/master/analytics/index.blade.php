<div class="w-full max-w-[1500px] mx-auto space-y-4 min-h-screen text-neutral-800 px-4 py-4 sm:px-6 font-sans">

    {{-- ================= HEADER & FILTER ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-md border border-neutral-100 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Analytics & Statistik Usaha</h1>
            </div>
            <p class="text-sm text-neutral-400 mt-1">
                Ringkasan performa finansial dan operasional seluruh unit usaha.
            </p>
        </div>

        {{-- Filter Rentang Waktu & Unit --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <select wire:model.live="selectedUnit" class="px-3.5 py-2.5 text-xs font-semibold text-neutral-600 bg-white hover:bg-neutral-50 border border-neutral-200 rounded-full transition-all shadow-sm shadow-black/[0.02] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-300 cursor-pointer">
                <option value="">Semua Unit Usaha</option>
                @foreach($unitsList as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="periodFilter" class="px-3.5 py-2.5 text-xs font-semibold text-neutral-600 bg-white hover:bg-neutral-50 border border-neutral-200 rounded-full transition-all shadow-sm shadow-black/[0.02] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-300 cursor-pointer">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Kustom Rentang Tanggal</option>
            </select>

            @if($periodFilter === 'custom')
                <div class="flex items-center gap-1.5">
                    <input type="date" wire:model.live="startDate" class="px-3 py-2 text-xs font-semibold text-neutral-600 bg-white border border-neutral-200 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-300 shadow-sm shadow-black/[0.02]">
                    <span class="text-neutral-400 text-xs">-</span>
                    <input type="date" wire:model.live="endDate" class="px-3 py-2 text-xs font-semibold text-neutral-600 bg-white border border-neutral-200 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-300 shadow-sm shadow-black/[0.02]">
                </div>
            @endif
        </div>
    </div>

    {{-- ================= KARTU METRIK FINANSIAL & OPERASIONAL ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Total Pendapatan --}}
        <div class="bg-white rounded-md border border-neutral-100 p-5 hover:border-neutral-200 transition-all shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pendapatan</p>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 tracking-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-neutral-400">Bruto akumulasi pendapatan</p>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-white rounded-md border border-neutral-100 p-5 hover:border-neutral-200 transition-all shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Pengeluaran</p>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9.75a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5zm10.5 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 tracking-tight">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-neutral-400">Total biaya operasional</p>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-white rounded-md border border-neutral-100 p-5 hover:border-neutral-200 transition-all shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Laba Bersih</p>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a.5.5 0 00.71 0L21.75 6M21.75 6v5.25m0-5.25h-5.25"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold tracking-tight {{ ($totalRevenue - $totalExpense) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                Rp {{ number_format($totalRevenue - $totalExpense, 0, ',', '.') }}
            </p>
            <p class="mt-3 text-xs text-neutral-400">Margin bersih operasional</p>
        </div>

        {{-- Total Transaksi --}}
        <div class="bg-white rounded-md border border-neutral-100 p-5 hover:border-neutral-200 transition-all shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Transaksi</p>
                <span class="p-2 rounded-xl bg-violet-50 text-violet-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            <p class="mt-3 text-xs text-neutral-400">Jumlah transaksi berhasil</p>
        </div>
    </div>

    {{-- ================= SECTION GRAFIK ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        {{-- Grafik Tren Arus Kas --}}
        <div class="lg:col-span-2 bg-white rounded-md border border-slate-100 p-6 shadow-sm shadow-slate-200/40 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Tren Arus Kas</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Perbandingan Pendapatan vs Pengeluaran</p>
                </div>
            </div>

            <div 
                x-data="{
                    chart: null,
                    initChart() {
                        const ctx = document.getElementById('cashflowChart');
                        if (!ctx) return;
                        if (this.chart) this.chart.destroy();
                        
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @js($chartLabels),
                                datasets: [
                                    { 
                                        label: 'Pendapatan', 
                                        data: @js($revenueChartData), 
                                        backgroundColor: '#10b981',
                                        borderRadius: 6 
                                    },
                                    { 
                                        label: 'Pengeluaran', 
                                        data: @js($expenseChartData), 
                                        backgroundColor: '#f43f5e',
                                        borderRadius: 6 
                                    }
                                ]
                            },
                            options: { 
                                responsive: true, 
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: { font: { family: 'Plus Jakarta Sans, sans-serif', size: 12 } }
                                    }
                                }
                            }
                        });
                    }
                }"
                x-init="initChart()"
                x-effect="$wire.chartLabels; initChart()"
                class="h-72 w-full"
            >
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        {{-- Grafik Distribusi Pendapatan per Unit --}}
        <div class="bg-white rounded-md border border-slate-100 p-6 shadow-sm shadow-slate-200/40 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Kontribusi Unit Usaha</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Proporsi pembagian pendapatan</p>
                </div>
            </div>

            <div 
                x-data="{
                    chart: null,
                    initChart() {
                        const ctx = document.getElementById('unitDistributionChart');
                        if (!ctx) return;
                        if (this.chart) this.chart.destroy();
                        
                        this.chart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: @js($unitShareLabels),
                                datasets: [{ 
                                    data: @js($unitShareData), 
                                    backgroundColor: ['#2563eb', '#38bdf8', '#f43f5e', '#8b5cf6', '#f59e0b'] 
                                }]
                            },
                            options: { 
                                responsive: true, 
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { font: { family: 'Plus Jakarta Sans, sans-serif', size: 11 } }
                                    }
                                }
                            }
                        });
                    }
                }"
                x-init="initChart()"
                x-effect="$wire.unitShareData; initChart()"
                class="h-72 w-full"
            >
                <canvas id="unitDistributionChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ================= PERFORMA UNIT USAHA & PRODUK TERLARIS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
        
        {{-- Tabel Performa Unit --}}
        <div class="bg-white rounded-md border border-neutral-100 overflow-hidden shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-neutral-100">
                    <h2 class="text-base font-bold text-neutral-900">Top Unit Usaha</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Unit usaha dengan omset tertinggi</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-neutral-50/70 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100">
                            <tr>
                                <th class="px-5 py-3.5">Unit Usaha</th>
                                <th class="px-5 py-3.5 text-right">Transaksi</th>
                                <th class="px-5 py-3.5 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($topUnits as $item)
                                <tr class="hover:bg-neutral-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-semibold text-neutral-900 text-xs sm:text-sm">
                                        {{ $item->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs text-neutral-600 font-medium">
                                        {{ number_format($item->total_tx, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs font-bold text-emerald-600">
                                        Rp {{ number_format($item->total_income, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-xs text-neutral-400">
                                        Belum ada data tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Produk Terlaris --}}
        <div class="bg-white rounded-md border border-neutral-100 overflow-hidden shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-neutral-100">
                    <h2 class="text-base font-bold text-neutral-900">5 Produk/Jasa Terlaris</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Item paling banyak diminati pelanggan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-neutral-50/70 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100">
                            <tr>
                                <th class="px-5 py-3.5">Nama Produk / Jasa</th>
                                <th class="px-5 py-3.5 text-right">Terjual</th>
                                <th class="px-5 py-3.5 text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($topProducts as $product)
                                <tr class="hover:bg-neutral-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-semibold text-neutral-900 text-xs sm:text-sm">
                                        {{ $product->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs text-neutral-600 font-medium">
                                        {{ number_format($product->qty_sold, 0, ',', '.') }} unit
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs font-bold text-blue-600">
                                        Rp {{ number_format($product->total_sales, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-xs text-neutral-400">
                                        Belum ada data tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Script Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>