<?php

namespace App\Livewire\Unit;

use App\Exports\Dashboard\Unit\UnitDashboardExport;
use App\Models\AuditLog;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\RecurringTransaction;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Dashboard Unit')]
class Dashboard extends Component
{
    // 🔴 Type-hint Model Unit agar Livewire otomatis resolve dari route-model-binding {unit}
    public Unit $unit;

    #[Url(as: 'q_trx', history: true)]
    public string $searchTransaction = '';

    // ------------------------------------------
    // FILTER PERIODE WAKTU (SAMA SEPERTI MASTER)
    // ------------------------------------------
    #[Url(as: 'period', history: true)]
    public string $periodFilter = 'this_month';

    public $startDate;
    public $endDate;

    // ------------------------------------------
    // LIFECYCLE HOOKS
    // ------------------------------------------
    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
        $this->applyPeriodFilter();
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
            case 'today':
                $this->startDate = Carbon::now()->toDateString();
                $this->endDate   = Carbon::now()->toDateString();
                break;
            case 'this_week':
                $this->startDate = Carbon::now()->startOfWeek()->toDateString();
                $this->endDate   = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'this_quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->toDateString();
                $this->endDate   = Carbon::now()->endOfQuarter()->toDateString();
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->toDateString();
                $this->endDate   = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
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

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        return match ($this->periodFilter) {
            'today'        => 'Hari Ini',
            'this_week'    => 'Minggu Ini',
            'this_month'   => 'Bulan Ini',
            'this_quarter' => 'Kuartal Ini',
            'this_year'    => 'Tahun Ini',
            'last_month'   => 'Bulan Lalu',
            'custom'       => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            default        => 'Bulan Ini',
        };
    }

    /**
     * Ekspor data Dashboard Unit ke Excel (multi-sheet), scoped ke unit yang sedang login.
     */
    public function export()
    {
        Carbon::setLocale('id');

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();
        $periodLabel = $this->periodLabel($start, $end);

        $baseQuery = FinanceTransaction::query()
            ->where('unit_id', $this->unit->id)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalIncome  = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $trxCount     = (clone $baseQuery)->count();

        $totalProducts  = Product::where('unit_id', $this->unit->id)->count();
        $lowStockCount  = Product::where('unit_id', $this->unit->id)->whereColumn('stock', '<=', 'min_stock')->count();

        $summary = [
            'unitName'      => $this->unit->name,
            'totalIncome'   => (float) $totalIncome,
            'totalExpense'  => (float) $totalExpense,
            'netRevenue'    => (float) ($totalIncome - $totalExpense),
            'trxCount'      => (int) $trxCount,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
        ];

        $fileName = 'Laporan_Dashboard_' . \Illuminate\Support\Str::slug($this->unit->name) . '_' . now()->format('Ymd_His') . '.xlsx';

        // Catat ke Audit Log: ekspor laporan dashboard unit.
        AuditLog::record(
            'dashboard_export',
            $fileName,
            "Export laporan dashboard unit '{$this->unit->name}' periode '{$periodLabel}'",
            null,
            [
                'unit_id'       => $this->unit->id,
                'period_filter' => $this->periodFilter,
                'period_label'  => $periodLabel,
                'start_date'    => $this->startDate,
                'end_date'      => $this->endDate,
            ]
        );

        return Excel::download(
            new UnitDashboardExport($this->unit, $summary, $periodLabel, $start, $end),
            $fileName
        );
    }

    public function render()
    {
        Carbon::setLocale('id');

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();
        $periodLabel = $this->periodLabel($start, $end);

        $unitId = $this->unit->id;

        // -------------------------------------------------------------
        // RINGKASAN OMZET & TRANSAKSI (SCOPED KE UNIT INI SAJA)
        // -------------------------------------------------------------
        $completedInRange = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalIncome  = (clone $completedInRange)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $completedInRange)->where('type', 'expense')->sum('amount');
        $trxCount     = (clone $completedInRange)->count();
        $avgTrxValue  = $trxCount > 0 ? ($totalIncome + $totalExpense) / $trxCount : 0;

        // TRANSAKSI TERKINI (bisa dicari)
        $recentTransactions = FinanceTransaction::with(['category', 'user'])
            ->where('unit_id', $unitId)
            ->when($this->searchTransaction, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', '%' . $this->searchTransaction . '%')
                        ->orWhere('reference_no', 'like', '%' . $this->searchTransaction . '%');
                });
            })
            ->latest('transaction_date')
            ->latest('id')
            ->limit(6)
            ->get();

        // TREN OMZET HARIAN DALAM RENTANG PERIODE (untuk grafik)
        $dailyTrend = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('transaction_date as date, SUM(amount) as total')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $revenueTrend = [
            'labels' => $dailyTrend->map(fn ($row) => Carbon::parse($row->date)->translatedFormat('d M'))->all(),
            'series' => $dailyTrend->map(fn ($row) => (float) $row->total)->all(),
        ];

        // -------------------------------------------------------------
        // STOK & PRODUK (ASUMSI: PRODUCT PUNYA KOLOM unit_id)
        // -------------------------------------------------------------
        $totalProducts = Product::where('unit_id', $unitId)->count();

        $lowStockProducts = Product::where('unit_id', $unitId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(6)
            ->get();

        $lowStockCount = Product::where('unit_id', $unitId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // -------------------------------------------------------------
        // TRANSAKSI BERULANG YANG AKAN JATUH TEMPO
        // -------------------------------------------------------------
        $upcomingRecurring = RecurringTransaction::where('unit_id', $unitId)
            ->where('status', 'active')
            ->whereNotNull('next_run_date')
            ->orderBy('next_run_date')
            ->limit(5)
            ->get();

        // -------------------------------------------------------------
        // AKTIVITAS TERKINI MILIK ADMIN UNIT INI
        // (AuditLog tidak punya unit_id langsung, jadi difilter lewat relasi user)
        // -------------------------------------------------------------
        $recentActivity = AuditLog::with('user')
            ->whereHas('user', fn ($q) => $q->where('unit_id', $unitId))
            ->latest()
            ->limit(6)
            ->get();

        return view('livewire.unit.dashboard', [
            'unit'                => $this->unit,
            'periodLabel'         => $periodLabel,
            'totalIncome'         => 'Rp ' . number_format($totalIncome, 0, ',', '.'),
            'totalExpense'        => 'Rp ' . number_format($totalExpense, 0, ',', '.'),
            'netRevenue'          => 'Rp ' . number_format($totalIncome - $totalExpense, 0, ',', '.'),
            'trxCount'            => $trxCount,
            'avgTrxValue'         => 'Rp ' . number_format($avgTrxValue, 0, ',', '.'),
            'recentTransactions'  => $recentTransactions,
            'revenueTrend'        => $revenueTrend,
            'totalProducts'       => $totalProducts,
            'lowStockProducts'    => $lowStockProducts,
            'lowStockCount'       => $lowStockCount,
            'upcomingRecurring'   => $upcomingRecurring,
            'recentActivity'      => $recentActivity,
        ]);
    }

    public function eventInfo(string $event): array
    {
        return match ($event) {
            'login.success'           => ['label' => 'Login Berhasil', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            'login.failed'            => ['label' => 'Login Gagal', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            'logout'                  => ['label' => 'Logout', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
            'password.changed'        => ['label' => 'Password Diubah', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'password.reset_by_admin' => ['label' => 'Reset Password', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            'dashboard_export'        => ['label' => 'Export Laporan', 'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
            'access.forbidden'        => ['label' => 'Akses Ditolak', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            default                   => ['label' => ucwords(str_replace(['.', '_'], ' ', $event)), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
        };
    }
}
