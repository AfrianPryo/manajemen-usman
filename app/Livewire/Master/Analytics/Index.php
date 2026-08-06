<?php

namespace App\Livewire\Master\Analytics;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Unit;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.app')]
class Index extends Component
{
    public $selectedUnit = '';
    public $periodFilter = 'this_month';
    public $startDate;
    public $endDate;

    // Properti publik untuk grafik (wajib dideklarasikan agar bisa diakses oleh $wire di Alpine)
    public array $chartLabels = [];
    public array $revenueChartData = [];
    public array $expenseChartData = [];
    public array $unitShareLabels = [];
    public array $unitShareData = [];

    public function mount(): void
    {
        $this->applyPeriodFilter();
    }

    public function updatedPeriodFilter(): void
    {
        $this->applyPeriodFilter();
    }

    public function updatedSelectedUnit(): void
    {
        // Otomatis merender ulang data saat unit diubah
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
        $unitsList = Unit::select('id', 'name')->get();

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        // --- Transaksi & Pendapatan ---
        $transactionQuery = class_exists(Transaction::class) 
            ? Transaction::query()->whereBetween('created_at', [$start, $end])
            : null;

        if ($transactionQuery && $this->selectedUnit) {
            $transactionQuery->where('unit_id', $this->selectedUnit);
        }

        $totalRevenue      = $transactionQuery ? (clone $transactionQuery)->sum('total_amount') : 0;
        $totalTransactions = $transactionQuery ? (clone $transactionQuery)->count() : 0;

        // --- Pengeluaran ---
        $expenseQuery = class_exists(Expense::class) 
            ? Expense::query()->whereBetween('created_at', [$start, $end])
            : null;

        if ($expenseQuery && $this->selectedUnit) {
            $expenseQuery->where('unit_id', $this->selectedUnit);
        }

        $totalExpense = $expenseQuery ? (clone $expenseQuery)->sum('amount') : 0;

        // --- Data Grafik Arus Kas ---
        $this->chartLabels       = [];
        $this->revenueChartData = [];
        $this->expenseChartData = [];

        if ($transactionQuery) {
            $dailyRevenues = (clone $transactionQuery)
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $dailyExpenses = $expenseQuery 
                ? (clone $expenseQuery)
                    ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                    ->groupBy('date')
                    ->pluck('total', 'date')
                : collect();

            $period = \Carbon\CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $formattedDate            = $date->format('Y-m-d');
                $this->chartLabels[]       = $date->format('d M');
                $this->revenueChartData[] = (float) ($dailyRevenues[$formattedDate] ?? 0);
                $this->expenseChartData[] = (float) ($dailyExpenses[$formattedDate] ?? 0);
            }
        }

        // --- Data Grafik Kontribusi Unit ---
        $this->unitShareLabels = [];
        $this->unitShareData   = [];

        if (class_exists(Transaction::class)) {
            $unitContributions = Transaction::query()
                ->join('units', 'transactions.unit_id', '=', 'units.id')
                ->whereBetween('transactions.created_at', [$start, $end])
                ->selectRaw('units.name, SUM(transactions.total_amount) as total_income')
                ->groupBy('units.id', 'units.name')
                ->get();

            foreach ($unitContributions as $contrib) {
                $this->unitShareLabels[] = $contrib->name;
                $this->unitShareData[]   = (float) $contrib->total_income;
            }
        }

        // --- Top Units & Top Products ---
        $topUnits = class_exists(Transaction::class)
            ? Transaction::query()
                ->join('units', 'transactions.unit_id', '=', 'units.id')
                ->whereBetween('transactions.created_at', [$start, $end])
                ->selectRaw('units.name, COUNT(transactions.id) as total_tx, SUM(transactions.total_amount) as total_income')
                ->groupBy('units.id', 'units.name')
                ->orderByDesc('total_income')
                ->limit(5)
                ->get()
            : collect();

        $topProducts = class_exists(Product::class) && Schema::hasTable('transaction_details')
            ? DB::table('transaction_details')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereBetween('transactions.created_at', [$start, $end])
                ->when($this->selectedUnit, fn($q) => $q->where('transactions.unit_id', $this->selectedUnit))
                ->selectRaw('products.name, SUM(transaction_details.quantity) as qty_sold, SUM(transaction_details.subtotal) as total_sales')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('qty_sold')
                ->limit(5)
                ->get()
            : collect();

        return view('livewire.master.analytics.index', compact(
            'unitsList',
            'totalRevenue',
            'totalExpense',
            'totalTransactions',
            'topUnits',
            'topProducts'
        ));
    }
}