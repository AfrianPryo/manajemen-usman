<?php

namespace App\Livewire\Master\Analytics;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Unit;
use App\Models\FinanceTransaction;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Statistik Usaha')]
class Index extends Component
{
    // Kunci penyimpanan filter di session, supaya tetap "diingat" walau
    // pindah ke menu lain (link sidebar tidak membawa query string) —
    // bukan cuma bertahan saat refresh URL yang sama.
    private const SESSION_KEY = 'analytics_filter';

    #[Url(as: 'unit', history: true)]
    public $selectedUnit = '';

    #[Url(as: 'period', history: true)]
    public $periodFilter = 'this_month';

    #[Url(as: 'start', history: true)]
    public $startDate;

    #[Url(as: 'end', history: true)]
    public $endDate;

    // Properti grafik
    public array $chartLabels = [];
    public array $revenueChartData = [];
    public array $expenseChartData = [];

    // Data untuk grafik donut Kontribusi Omzet per Unit Usaha
    // (dibaca reaktif oleh Alpine lewat $wire, lihat blade)
    public array $revenueLabels = [];
    public array $revenueSeries = [];

    // Filter Khusus Tabel Unit Usaha
    public string $unitPeriod = 'this_month';
    public ?string $unitStartDate = null;
    public ?string $unitEndDate = null;

    public function mount(): void
    {
        // Kalau properti ini TIDAK datang dari query string (mis. user
        // klik menu lain lalu balik lagi ke Statistik lewat link biasa),
        // pulihkan dari session supaya filter yang sudah dipilih user
        // tidak diam-diam balik ke default.
        if (! request()->has('unit')) {
            $this->selectedUnit = session(self::SESSION_KEY . '.unit', $this->selectedUnit);
        }
        if (! request()->has('period')) {
            $this->periodFilter = session(self::SESSION_KEY . '.period', $this->periodFilter);
        }
        if (! request()->has('start')) {
            $this->startDate = session(self::SESSION_KEY . '.start', $this->startDate);
        }
        if (! request()->has('end')) {
            $this->endDate = session(self::SESSION_KEY . '.end', $this->endDate);
        }

        $this->applyPeriodFilter();
        $this->persistFilterToSession();
    }

    public function updatedSelectedUnit(): void
    {
        $this->persistFilterToSession();
    }

    public function updatedUnitPeriod(): void
    {
        if ($this->unitPeriod !== 'custom') {
            $this->unitStartDate = null;
            $this->unitEndDate = null;
        }
    }

    public function updatedPeriodFilter(): void
    {
        $this->applyPeriodFilter();
        $this->persistFilterToSession();
    }

    public function updatedStartDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
        $this->persistFilterToSession();
    }

    public function updatedEndDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
        $this->persistFilterToSession();
    }

    /**
     * Simpan filter aktif ke session (bukan cuma query string), supaya saat
     * user pindah ke menu lain lalu balik ke Statistik lewat link biasa
     * (tanpa query string), filter yang terakhir dipakai tetap dipulihkan.
     */
    private function persistFilterToSession(): void
    {
        session([
            self::SESSION_KEY . '.unit'   => $this->selectedUnit,
            self::SESSION_KEY . '.period' => $this->periodFilter,
            self::SESSION_KEY . '.start'  => $this->startDate,
            self::SESSION_KEY . '.end'    => $this->endDate,
        ]);
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

    public function export(): void
    {
        session()->flash('message', 'Fitur unduh laporan ekspor Excel siap digunakan.');
    }

    public function render()
    {
        $unitsList = Unit::select('id', 'name')->orderBy('name', 'asc')->get();

        // 1. Rentang Tanggal Filter Umum (Ringkasan Metrik)
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        // --- Transaksi Pendapatan ---
        $incomeQuery = FinanceTransaction::query()
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        if ($this->selectedUnit) {
            $incomeQuery->where('unit_id', $this->selectedUnit);
        }

        $totalRevenue = (clone $incomeQuery)->sum('amount') ?? 0;
        $incomeCount  = (clone $incomeQuery)->count();

        // --- Transaksi Pengeluaran (finance_transactions, terpisah dari tabel expenses lama) ---
        $expenseTxQuery = FinanceTransaction::query()
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        if ($this->selectedUnit) {
            $expenseTxQuery->where('unit_id', $this->selectedUnit);
        }

        $expenseCount = $expenseTxQuery->count();

        // KPI ringkas: total gabungan jumlah transaksi & rincian per tipe,
        // supaya kartu bisa menampilkan "104 transaksi (86 income, 18 expense)"
        // alih-alih hanya satu angka yang menutupi salah satu tipe.
        $totalTransactions = $incomeCount + $expenseCount;

        // --- Pengeluaran ---
        $expenseQuery = class_exists(Expense::class) && Schema::hasTable('expenses')
            ? Expense::query()->whereBetween('created_at', [$start, $end])
            : null;

        if ($expenseQuery && $this->selectedUnit) {
            $expenseQuery->where('unit_id', $this->selectedUnit);
        }

        $totalExpense = $expenseQuery ? $expenseQuery->sum('amount') : 0;

        if ($totalExpense == 0) {
            $financeExpenseQuery = FinanceTransaction::query()
                ->where('type', 'expense')
                ->where('status', 'completed')
                ->whereBetween('transaction_date', [$start, $end]);

            if ($this->selectedUnit) {
                $financeExpenseQuery->where('unit_id', $this->selectedUnit);
            }

            $totalExpense = $financeExpenseQuery->sum('amount') ?? 0;
        }

        // --- Metrik Finansial ---
        $netProfit = $totalRevenue - $totalExpense;

        // 2. Grafik Arus Kas mengikuti filter global (selectedUnit + startDate/endDate)
        // -- tidak lagi punya rentang tanggal sendiri (cashflowPeriod dihapus).
        $dailyRevenues = FinanceTransaction::query()
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->when($this->selectedUnit, fn($q) => $q->where('unit_id', $this->selectedUnit))
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpenses = FinanceTransaction::query()
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->when($this->selectedUnit, fn($q) => $q->where('unit_id', $this->selectedUnit))
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $this->chartLabels       = [];
        $this->revenueChartData = [];
        $this->expenseChartData = [];

        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $formattedDate            = $date->format('Y-m-d');
            $this->chartLabels[]       = $date->format('d M');
            $this->revenueChartData[] = (float) ($dailyRevenues[$formattedDate] ?? 0);
            $this->expenseChartData[] = (float) ($dailyExpenses[$formattedDate] ?? 0);
        }

        // --- Data Kontribusi Omzet per Unit Usaha ---
        $unitContributions = FinanceTransaction::query()
            ->join('units', 'finance_transactions.unit_id', '=', 'units.id')
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->when($this->selectedUnit, fn($q) => $q->where('finance_transactions.unit_id', $this->selectedUnit))
            ->selectRaw('units.name, SUM(finance_transactions.amount) as total_income')
            ->groupBy('units.id', 'units.name')
            ->orderByDesc('total_income')
            ->get();

        $grandTotalContribution = $unitContributions->sum('total_income');

        $revenueContribution = [
            'labels'      => [],
            'series'      => [],
            'percentages' => []
        ];

        foreach ($unitContributions as $contrib) {
            $val = (float) $contrib->total_income;
            $revenueContribution['labels'][]      = $contrib->name;
            $revenueContribution['series'][]      = $val;
            $revenueContribution['percentages'][] = $grandTotalContribution > 0 
                ? round(($val / $grandTotalContribution) * 100, 1) 
                : 0;
        }

        $this->revenueLabels = $revenueContribution['labels'];
        $this->revenueSeries = $revenueContribution['series'];

        // 3. Rentang Tanggal Khusus Tabel Performa Unit Usaha
        $uStart = match ($this->unitPeriod) {
            'this_week'    => Carbon::now()->startOfWeek(),
            'last_30_days' => Carbon::now()->subDays(30)->startOfDay(),
            'this_month'   => Carbon::now()->startOfMonth(),
            'this_year'    => Carbon::now()->startOfYear(),
            'custom'       => $this->unitStartDate ? Carbon::parse($this->unitStartDate)->startOfDay() : Carbon::now()->startOfMonth(),
            default        => Carbon::now()->startOfMonth(),
        };

        $uEnd = match ($this->unitPeriod) {
            'custom' => $this->unitEndDate ? Carbon::parse($this->unitEndDate)->endOfDay() : Carbon::now()->endOfDay(),
            default  => Carbon::now()->endOfDay(),
        };

        // --- Performa Seluruh Unit Usaha (Termasuk Untung/Rugi & Pengeluaran) ---
        $hasExpenseTable = class_exists(Expense::class) && Schema::hasTable('expenses');

        $topUnits = Unit::query()
            ->when($this->selectedUnit, fn($q) => $q->where('id', $this->selectedUnit))
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($unit) use ($uStart, $uEnd, $hasExpenseTable) {
                // Total Transaksi & Pendapatan berdasarkan $uStart & $uEnd
                $incomeQuery = FinanceTransaction::query()
                    ->where('unit_id', $unit->id)
                    ->where('type', 'income')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$uStart, $uEnd]);

                $totalIncome = (float) ($incomeQuery->sum('amount') ?? 0);
                $totalTx     = $incomeQuery->count();

                // Total Pengeluaran (Tabel Expense)
                $expFromModel = $hasExpenseTable
                    ? (float) Expense::where('unit_id', $unit->id)
                        ->whereBetween('created_at', [$uStart, $uEnd])
                        ->sum('amount')
                    : 0;

                // Total Pengeluaran (Tabel FinanceTransaction)
                $expFromFinance = (float) FinanceTransaction::query()
                    ->where('unit_id', $unit->id)
                    ->where('type', 'expense')
                    ->where('status', 'completed')
                    ->whereBetween('transaction_date', [$uStart, $uEnd])
                    ->sum('amount');

                $totalExpense = $expFromModel > 0 ? $expFromModel : $expFromFinance;

                $unit->total_tx      = $totalTx;
                $unit->total_income  = $totalIncome;
                $unit->total_expense = $totalExpense;
                $unit->total_profit  = $totalIncome - $totalExpense;

                return $unit;
            })
            ->sortByDesc('total_income')
            ->values();

        // --- Top Products ---
        $detailTable = Schema::hasTable('transaction_details') ? 'transaction_details' : (Schema::hasTable('transaction_items') ? 'transaction_items' : null);

        $topProducts = $detailTable
            ? DB::table($detailTable)
                ->join('products', "{$detailTable}.product_id", '=', 'products.id')
                ->join('finance_transactions', "{$detailTable}.transaction_id", '=', 'finance_transactions.id')
                ->whereBetween('finance_transactions.transaction_date', [$start, $end])
                ->when($this->selectedUnit, fn($q) => $q->where('finance_transactions.unit_id', $this->selectedUnit))
                ->selectRaw("products.name, SUM({$detailTable}.quantity) as qty_sold, SUM({$detailTable}.subtotal) as total_sales")
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('qty_sold')
                ->limit(5)
                ->get()
            : collect();

        // Kirim event pembaruan data grafik ke AlpineJS
        $this->dispatch('update-cashflow-chart', 
            labels: $this->chartLabels,
            revenue: $this->revenueChartData,
            expense: $this->expenseChartData
        );

        return view('livewire.master.analytics.index', compact(
            'unitsList',
            'totalRevenue',
            'totalExpense',
            'totalTransactions',
            'incomeCount',      // <- baru
            'expenseCount',     // <- baru
            'netProfit',
            'revenueContribution',
            'topUnits',
            'topProducts'
        ));
    }
}