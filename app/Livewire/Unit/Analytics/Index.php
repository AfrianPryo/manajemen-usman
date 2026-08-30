<?php

namespace App\Livewire\Unit\Analytics;

use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Modul "Statistik Usaha" versi Unit Admin -- pasangan satu-unit dari
 * App\Livewire\Master\Analytics\Index. Ditulis berdiri sendiri (bukan
 * extends komponen Master) memakai konvensi yang sama dengan modul unit
 * lain: trait ScopedToUnit untuk penguncian unit_id (lihat komentar di
 * ScopedToUnit -- WAJIB dipakai, bukan Auth::user()->unit_id langsung,
 * supaya tetap benar saat Master Admin memantau unit ini), properti filter
 * periode & method applyPeriodFilter() disalin persis dari versi Master,
 * dan struktur render() (kartu ringkasan -> grafik -> tabel) mengikuti
 * urutan section yang sama persis dengan resources/views/livewire/master/
 * analytics/index.blade.php.
 *
 * PERBEDAAN DENGAN VERSI MASTER (karena halaman ini memang dikunci ke SATU
 * unit usaha, bukan lintas unit):
 *   - Tidak ada properti/dropdown $selectedUnit maupun daftar $unitsList --
 *     seluruh query sudah otomatis dikunci ke currentUnitId().
 *   - Widget "Kontribusi Omzet per Unit Usaha" (donut + peringkat) diganti
 *     jadi "Kontribusi Pendapatan per Kategori", dan tabel "Performa
 *     Seluruh Unit Usaha" diganti jadi "Performa per Kategori Transaksi" --
 *     keduanya memakai App\Models\FinanceCategory (kolom finance_category_id
 *     pada finance_transactions, lihat juga pola JOIN yang sama dipakai
 *     App\Exports\Finance\CategoryBreakdownSheetExport) sebagai pengganti
 *     dimensi "per unit" yang sudah tidak relevan ketika halamannya sendiri
 *     sudah dikunci ke satu unit.
 *   - Query total pengeluaran TIDAK melalui model Expense terpisah seperti
 *     versi Master (class_exists(Expense::class) selalu false di codebase
 *     ini karena model tsb tidak pernah dibuat -- cabang itu jadi dead code
 *     di versi Master), jadi di sini langsung memakai
 *     FinanceTransaction::where('type', 'expense') supaya konsisten dengan
 *     apa yang benar-benar dieksekusi oleh versi Master saat ini.
 *   - Query $topProducts pada versi Master dihitung tapi TIDAK PERNAH
 *     dirender di Blade-nya (dead code); tidak ikut disalin ke sini supaya
 *     tidak menambah query yang tidak dipakai.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Statistik Usaha')]
class Index extends Component
{
    use ScopedToUnit;

    // Filter Rentang Waktu Ringkasan Metrik Utama
    public string $periodFilter = 'this_month';
    public $startDate;
    public $endDate;

    // Properti Grafik Arus Kas
    public array $chartLabels = [];
    public array $revenueChartData = [];
    public array $expenseChartData = [];

    // Filter Khusus Grafik Arus Kas
    public string $cashflowPeriod = 'this_month';
    public ?string $cfStartDate = null;
    public ?string $cfEndDate = null;

    // Filter Khusus Tabel Performa per Kategori Transaksi
    public string $categoryPeriod = 'this_month';
    public ?string $categoryStartDate = null;
    public ?string $categoryEndDate = null;

    public function mount(): void
    {
        $this->applyPeriodFilter();
    }

    public function updatedCashflowPeriod(): void
    {
        if ($this->cashflowPeriod !== 'custom') {
            $this->cfStartDate = null;
            $this->cfEndDate = null;
        }
    }

    public function updatedCategoryPeriod(): void
    {
        if ($this->categoryPeriod !== 'custom') {
            $this->categoryStartDate = null;
            $this->categoryEndDate = null;
        }
    }

    public function updatedPeriodFilter(): void
    {
        $this->applyPeriodFilter();
    }

    public function updatedStartDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
    }

    public function updatedEndDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
    }

    private function applyPeriodFilter(): void
    {
        switch ($this->periodFilter) {
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->toDateString();
                $this->endDate   = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'custom':
                if (!$this->startDate) {
                    $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::now()->toDateString();
                }
                break;
            case 'this_month':
            default:
                $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                $this->endDate   = Carbon::now()->toDateString();
                break;
        }
    }

    public function render()
    {
        $unitId = $this->currentUnitId();
        $unit   = $this->currentUnit();

        // 1. Rentang Tanggal Filter Umum (Ringkasan Metrik)
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        // --- Transaksi Pendapatan ---
        $incomeQuery = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalRevenue      = (clone $incomeQuery)->sum('amount') ?? 0;
        $totalTransactions = (clone $incomeQuery)->count();

        // --- Pengeluaran ---
        $totalExpense = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount') ?? 0;

        // --- Metrik Finansial ---
        $netProfit = $totalRevenue - $totalExpense;

        // 2. Rentang Tanggal Khusus Grafik Arus Kas
        $cfStart = match ($this->cashflowPeriod) {
            'this_week'    => Carbon::now()->startOfWeek(),
            'last_30_days' => Carbon::now()->subDays(30)->startOfDay(),
            'this_month'   => Carbon::now()->startOfMonth(),
            'this_year'    => Carbon::now()->startOfYear(),
            'custom'       => $this->cfStartDate ? Carbon::parse($this->cfStartDate)->startOfDay() : Carbon::now()->startOfMonth(),
            default        => Carbon::now()->startOfMonth(),
        };

        $cfEnd = match ($this->cashflowPeriod) {
            'custom' => $this->cfEndDate ? Carbon::parse($this->cfEndDate)->endOfDay() : Carbon::now()->endOfDay(),
            default  => Carbon::now()->endOfDay(),
        };

        // --- Data Grafik Arus Kas (Harian) ---
        $dailyRevenues = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$cfStart, $cfEnd])
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpenses = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$cfStart, $cfEnd])
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $this->chartLabels      = [];
        $this->revenueChartData = [];
        $this->expenseChartData = [];

        $period = CarbonPeriod::create($cfStart, $cfEnd);
        foreach ($period as $date) {
            $formattedDate            = $date->format('Y-m-d');
            $this->chartLabels[]      = $date->format('d M');
            $this->revenueChartData[] = (float) ($dailyRevenues[$formattedDate] ?? 0);
            $this->expenseChartData[] = (float) ($dailyExpenses[$formattedDate] ?? 0);
        }

        // --- Data Kontribusi Pendapatan per Kategori Transaksi ---
        // Pengganti "kontribusi per unit" milik versi Master, karena
        // halaman ini sudah dikunci ke satu unit -- dimensi yang relevan
        // untuk dipecah di sini adalah kategori transaksi, bukan unit.
        $categoryContributions = FinanceTransaction::query()
            ->join('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->where('finance_transactions.unit_id', $unitId)
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->selectRaw('finance_categories.name, SUM(finance_transactions.amount) as total_income')
            ->groupBy('finance_categories.id', 'finance_categories.name')
            ->orderByDesc('total_income')
            ->get();

        $grandTotalContribution = $categoryContributions->sum('total_income');

        $revenueContribution = [
            'labels'      => [],
            'series'      => [],
            'percentages' => [],
        ];

        foreach ($categoryContributions as $contrib) {
            $val = (float) $contrib->total_income;
            $revenueContribution['labels'][]      = $contrib->name;
            $revenueContribution['series'][]      = $val;
            $revenueContribution['percentages'][] = $grandTotalContribution > 0
                ? round(($val / $grandTotalContribution) * 100, 1)
                : 0;
        }

        // 3. Rentang Tanggal Khusus Tabel Performa per Kategori Transaksi
        $cStart = match ($this->categoryPeriod) {
            'this_week'    => Carbon::now()->startOfWeek(),
            'last_30_days' => Carbon::now()->subDays(30)->startOfDay(),
            'this_month'   => Carbon::now()->startOfMonth(),
            'this_year'    => Carbon::now()->startOfYear(),
            'custom'       => $this->categoryStartDate ? Carbon::parse($this->categoryStartDate)->startOfDay() : Carbon::now()->startOfMonth(),
            default        => Carbon::now()->startOfMonth(),
        };

        $cEnd = match ($this->categoryPeriod) {
            'custom' => $this->categoryEndDate ? Carbon::parse($this->categoryEndDate)->endOfDay() : Carbon::now()->endOfDay(),
            default  => Carbon::now()->endOfDay(),
        };

        // --- Performa Seluruh Kategori Transaksi Milik Unit Ini ---
        // Pengganti "Performa Seluruh Unit Usaha" milik versi Master.
        $topCategories = FinanceCategory::query()
            ->where('unit_id', $unitId)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($category) use ($cStart, $cEnd) {
                $incomeQuery = FinanceTransaction::query()
                    ->where('finance_category_id', $category->id)
                    ->where('type', 'income')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$cStart, $cEnd]);

                $totalIncome = (float) ((clone $incomeQuery)->sum('amount') ?? 0);
                $totalTx     = (clone $incomeQuery)->count();

                $expenseQuery = FinanceTransaction::query()
                    ->where('finance_category_id', $category->id)
                    ->where('type', 'expense')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$cStart, $cEnd]);

                $totalExpense = (float) ((clone $expenseQuery)->sum('amount') ?? 0);

                // Kategori bertipe 'expense' tidak akan punya transaksi
                // 'income' (begitu pula sebaliknya) -- tapi tetap dihitung
                // dari kedua sisi supaya baris tabel tetap akurat kalau
                // suatu saat data tercampur.
                $totalTx += (clone $expenseQuery)->count();

                $category->total_tx      = $totalTx;
                $category->total_income  = $totalIncome;
                $category->total_expense = $totalExpense;
                $category->total_profit  = $totalIncome - $totalExpense;

                return $category;
            })
            ->sortByDesc('total_income')
            ->values();

        // Kirim event pembaruan data grafik ke AlpineJS
        $this->dispatch(
            'update-cashflow-chart',
            labels: $this->chartLabels,
            revenue: $this->revenueChartData,
            expense: $this->expenseChartData
        );

        return view('livewire.unit.analytics.index', compact(
            'unit',
            'totalRevenue',
            'totalExpense',
            'totalTransactions',
            'netProfit',
            'revenueContribution',
            'topCategories'
        ));
    }
}
