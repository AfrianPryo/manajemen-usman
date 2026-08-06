<div class="p-6 max-w-7xl mx-auto space-y-6">
    {{-- Header & Filter --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics & Statistik</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan performa finansial dan operasional seluruh unit usaha.</p>
        </div>

        {{-- Filter Rentang Waktu & Unit --}}
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="selectedUnit" class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Unit Usaha</option>
                @foreach($unitsList as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="periodFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Kustom Rentang Tanggal</option>
            </select>

            @if($periodFilter === 'custom')
                <input type="date" wire:model.live="startDate" class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                <span class="text-gray-400">-</span>
                <input type="date" wire:model.live="endDate" class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
            @endif
        </div>
    </div>

    {{-- Ringkasan Metrik Finansial & Operasional --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Pendapatan Gross --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Pendapatan</p>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">💰</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
                <span class="p-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg">💸</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Laba Bersih</p>
                <span class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">📈</span>
            </div>
            <p class="text-2xl font-bold {{ ($totalRevenue - $totalExpense) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-3">
                Rp {{ number_format($totalRevenue - $totalExpense, 0, ',', '.') }}
            </p>
        </div>

        {{-- Volume Transaksi --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Transaksi</p>
                <span class="p-2 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg">🛍️</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Section Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Grafik Tren Arus Kas (Line / Bar Chart) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Tren Arus Kas (Pendapatan vs Pengeluaran)</h3>
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
                                    { label: 'Pendapatan', data: @js($revenueChartData), backgroundColor: '#10b981' },
                                    { label: 'Pengeluaran', data: @js($expenseChartData), backgroundColor: '#f43f5e' }
                                ]
                            },
                            options: { responsive: true, maintainAspectRatio: false }
                        });
                    }
                }"
                x-init="initChart()"
                x-effect="$wire.chartLabels; initChart()"
                class="h-72"
            >
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        {{-- Grafik Distribusi Pendapatan per Unit (Doughnut Chart) --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Kontribusi per Unit Usaha</h3>
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
                                datasets: [{ data: @js($unitShareData), backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899'] }]
                            },
                            options: { responsive: true, maintainAspectRatio: false }
                        });
                    }
                }"
                x-init="initChart()"
                x-effect="$wire.unitShareData; initChart()"
                class="h-72"
            >
                <canvas id="unitDistributionChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Performa Unit Usaha & Produk Terlaris --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Tabel Performa Unit --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-3">Top Unit Usaha Berdasarkan Omset</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-4 py-2">Unit Usaha</th>
                            <th class="px-4 py-2 text-right">Transaksi</th>
                            <th class="px-4 py-2 text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($topUnits as $item)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                                <td class="px-4 py-2.5 text-right">{{ $item->total_tx }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->total_income, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-3 text-center text-gray-400">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Produk Terlaris --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-3">5 Produk/Jasa Terlaris</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-4 py-2">Nama Produk/Jasa</th>
                            <th class="px-4 py-2 text-right">Terjual</th>
                            <th class="px-4 py-2 text-right">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($topProducts as $product)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                                <td class="px-4 py-2.5 text-right">{{ $product->qty_sold }} unit</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-3 text-center text-gray-400">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Script Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>