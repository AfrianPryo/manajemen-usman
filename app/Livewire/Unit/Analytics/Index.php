<?php

namespace App\Livewire\Unit\Analytics;

use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Modul "Statistik Usaha" versi Unit Admin.
 *
 * Perbaikan penting:
 * - Cache hanya menyimpan data scalar/array yang aman untuk Livewire.
 * - Collection Eloquent $topCategories TIDAK lagi disimpan di cache.
 *   Data tersebut selalu diambil dari database pada setiap render.
 * - Cache key diberi versi agar cache lama tidak pernah dipakai lagi.
 *
 * Ini mencegah error seperti:
 *   Attempt to read property "total_income" on string
 *
 * karena Blade memang mengharapkan setiap item $topCategories berupa
 * object/model yang mempunyai property total_income, total_expense, dst.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Statistik Usaha')]
class Index extends Component
{
    use ScopedToUnit;

    /**
     * Naikkan angka ini jika struktur DATA YANG DI-CACHE berubah.
     *
     * v2 -> v3:
     * topCategories dikeluarkan dari cache karena berupa Eloquent Collection.
     */
    private const CACHE_VERSION = 3;
    private const CACHE_TTL_SECONDS = 120;

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

        /*
         * Hanya data scalar/array yang masuk cache.
         *
         * JANGAN masukkan Eloquent Collection/model ke dalam cache.
         */
        $data = Cache::remember(
            $this->analyticsCacheKey($unitId),
            self::CACHE_TTL_SECONDS,
            function () use ($unitId) {
                return $this->computeAnalyticsData($unitId);
            }
        );

        [
            'totalRevenue'        => $totalRevenue,
            'totalExpense'        => $totalExpense,
            'totalTransactions'   => $totalTransactions,
            'netProfit'           => $netProfit,
            'chartLabels'         => $this->chartLabels,
            'revenueChartData'    => $this->revenueChartData,
            'expenseChartData'    => $this->expenseChartData,
            'revenueContribution' => $revenueContribution,
        ] = $data;

        /*
         * IMPORTANT:
         * topCategories sengaja DIQUERY DI LUAR CACHE.
         *
         * Blade mengakses:
         *   $item->name
         *   $item->total_tx
         *   $item->total_income
         *   $item->total_expense
         *   $item->total_profit
         *
         * Karena itu hasilnya harus tetap berupa Collection berisi
         * Eloquent model/objects, bukan string atau data cache lama.
         */
        [$cStart, $cEnd] = $this->categoryDateRange();

        $topCategories = $this->computeTopCategories($unitId, $cStart, $cEnd);

        // Kirim event pembaruan data grafik ke AlpineJS.
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

    /**
     * Key cache unik untuk kombinasi unit + filter aktif.
     *
     * Prefix version membuat cache lama tidak ikut terbaca meskipun
     * php artisan cache:clear belum dijalankan.
     */
    private function analyticsCacheKey(int $unitId): string
    {
        return implode(':', [
            'analytics-unit-v' . self::CACHE_VERSION,
            $unitId,
            $this->startDate,
            $this->endDate,
            $this->cashflowPeriod,
            $this->cfStartDate ?: '-',
            $this->cfEndDate ?: '-',
        ]);
    }

    /**
     * Menghasilkan hanya data yang aman untuk disimpan di cache.
     *
     * @return array{
     *   totalRevenue: float|int,
     *   totalExpense: float|int,
     *   totalTransactions: int,
     *   netProfit: float|int,
     *   chartLabels: array,
     *   revenueChartData: array,
     *   expenseChartData: array,
     *   revenueContribution: array
     * }
     */
    private function computeAnalyticsData(int $unitId): array
    {
        // 1. Rentang tanggal filter umum.
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        // Pendapatan.
        $incomeQuery = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalRevenue      = (float) ((clone $incomeQuery)->sum('amount') ?? 0);
        $totalTransactions = (int) (clone $incomeQuery)->count();

        // Pengeluaran.
        $totalExpense = (float) (FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount') ?? 0);

        $netProfit = $totalRevenue - $totalExpense;

        // 2. Rentang tanggal grafik arus kas.
        [$cfStart, $cfEnd] = $this->cashflowDateRange();

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

        $chartLabels      = [];
        $revenueChartData = [];
        $expenseChartData = [];

        $period = CarbonPeriod::create($cfStart, $cfEnd);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');

            $chartLabels[]       = $date->format('d M');
            $revenueChartData[]  = (float) ($dailyRevenues[$formattedDate] ?? 0);
            $expenseChartData[]  = (float) ($dailyExpenses[$formattedDate] ?? 0);
        }

        /*
         * 3. Kontribusi pendapatan per kategori.
         *
         * Hasil query hanya dipakai untuk membuat array scalar.
         * Collection ini tidak ikut dikembalikan dari method cache.
         */
        $categoryContributions = FinanceTransaction::query()
            ->join(
                'finance_categories',
                'finance_transactions.finance_category_id',
                '=',
                'finance_categories.id'
            )
            ->where('finance_transactions.unit_id', $unitId)
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->selectRaw(
                'finance_categories.name, SUM(finance_transactions.amount) as total_income'
            )
            ->groupBy('finance_categories.id', 'finance_categories.name')
            ->orderByDesc('total_income')
            ->get();

        $grandTotalContribution = (float) $categoryContributions->sum('total_income');

        $revenueContribution = [
            'labels'      => [],
            'series'      => [],
            'percentages' => [],
        ];

        foreach ($categoryContributions as $contrib) {
            $val = (float) $contrib->total_income;

            $revenueContribution['labels'][] = (string) $contrib->name;
            $revenueContribution['series'][] = $val;
            $revenueContribution['percentages'][] = $grandTotalContribution > 0
                ? round(($val / $grandTotalContribution) * 100, 1)
                : 0;
        }

        return [
            'totalRevenue'        => $totalRevenue,
            'totalExpense'        => $totalExpense,
            'totalTransactions'   => $totalTransactions,
            'netProfit'           => $netProfit,
            'chartLabels'         => $chartLabels,
            'revenueChartData'    => $revenueChartData,
            'expenseChartData'    => $expenseChartData,
            'revenueContribution' => $revenueContribution,
        ];
    }

    /**
     * Rentang tanggal untuk grafik arus kas.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function cashflowDateRange(): array
    {
        $cfStart = match ($this->cashflowPeriod) {
            'this_week'    => Carbon::now()->startOfWeek(),
            'last_30_days' => Carbon::now()->subDays(30)->startOfDay(),
            'this_month'   => Carbon::now()->startOfMonth(),
            'this_year'    => Carbon::now()->startOfYear(),
            'custom'       => $this->cfStartDate
                ? Carbon::parse($this->cfStartDate)->startOfDay()
                : Carbon::now()->startOfMonth(),
            default        => Carbon::now()->startOfMonth(),
        };

        $cfEnd = match ($this->cashflowPeriod) {
            'custom' => $this->cfEndDate
                ? Carbon::parse($this->cfEndDate)->endOfDay()
                : Carbon::now()->endOfDay(),
            default => Carbon::now()->endOfDay(),
        };

        return [$cfStart, $cfEnd];
    }

    /**
     * Rentang tanggal tabel performa kategori.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function categoryDateRange(): array
    {
        $cStart = match ($this->categoryPeriod) {
            'this_week'    => Carbon::now()->startOfWeek(),
            'last_30_days' => Carbon::now()->subDays(30)->startOfDay(),
            'this_month'   => Carbon::now()->startOfMonth(),
            'this_year'    => Carbon::now()->startOfYear(),
            'custom'       => $this->categoryStartDate
                ? Carbon::parse($this->categoryStartDate)->startOfDay()
                : Carbon::now()->startOfMonth(),
            default        => Carbon::now()->startOfMonth(),
        };

        $cEnd = match ($this->categoryPeriod) {
            'custom' => $this->categoryEndDate
                ? Carbon::parse($this->categoryEndDate)->endOfDay()
                : Carbon::now()->endOfDay(),
            default => Carbon::now()->endOfDay(),
        };

        return [$cStart, $cEnd];
    }

    /**
     * Performa seluruh kategori transaksi milik unit ini.
     *
     * Method ini sengaja TIDAK dipanggil dari callback Cache::remember().
     * Hasil akhirnya berupa Eloquent Collection sehingga Blade dapat
     * menggunakan property access ($item->total_income, dll).
     */
    private function computeTopCategories(int $unitId, Carbon $cStart, Carbon $cEnd)
    {
        $incomeByCategory = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$cStart, $cEnd])
            ->selectRaw(
                'finance_category_id, SUM(amount) as total_income, COUNT(*) as total_tx'
            )
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');

        $expenseByCategory = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$cStart, $cEnd])
            ->selectRaw(
                'finance_category_id, SUM(amount) as total_expense, COUNT(*) as total_tx'
            )
            ->groupBy('finance_category_id')
            ->get()
            ->keyBy('finance_category_id');

        return FinanceCategory::query()
            ->forUnit($unitId)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($category) use ($incomeByCategory, $expenseByCategory) {
                $incomeRow = $incomeByCategory->get($category->id);
                $expenseRow = $expenseByCategory->get($category->id);

                $totalIncome = (float) ($incomeRow->total_income ?? 0);
                $totalExpense = (float) ($expenseRow->total_expense ?? 0);

                $totalTx = (int) ($incomeRow->total_tx ?? 0)
                    + (int) ($expenseRow->total_tx ?? 0);

                // Tetap Eloquent model, bukan array/string.
                $category->total_tx      = $totalTx;
                $category->total_income  = $totalIncome;
                $category->total_expense = $totalExpense;
                $category->total_profit  = $totalIncome - $totalExpense;

                return $category;
            })
            ->sortByDesc('total_income')
            ->values();
    }
}
