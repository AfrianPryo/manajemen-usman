<?php

namespace App\Livewire\Master;

use App\Models\AuthLog;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Exports\DashboardExport;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
#[Title('Dashboard Master Admin')]
class Dashboard extends Component
{
    #[Url(as: 'q_admin', history: true)]
    public string $searchAdmin = '';

    #[Url(as: 'q_unit', history: true)]
    public string $searchUnit = '';

    // ------------------------------------------
    // FILTER PERIODE WAKTU (SESUAI PILIHAN WIDGET)
    // ------------------------------------------
    #[Url(as: 'period', history: true)]
    public string $periodFilter = 'this_month';

    public $startDate;
    public $endDate;

    // ------------------------------------------
    // STATE MODAL & FORM UNIT USAHA
    // ------------------------------------------
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $unitId = null;

    public string $name = '';
    public string $department = '';
    public string $category = '';
    public string $pic_name = '';
    public string $phone = '';
    public string $description = '';
    public bool $is_active = true;

    // ------------------------------------------
    // STATE MODAL & FORM TAMBAH ADMIN
    // ------------------------------------------
    public bool $showCreateAdminModal = false;
    public string $admin_name = '';
    public string $employee_status = 'nip'; // Default: nip
    public string $nip = '';
    public string $role = 'unit-admin';
    public ?int $admin_unit_id = null;

    // Popup Modal Kredensial Baru (Username & Password)
    public ?array $createdCredentials = null;

    // ------------------------------------------
    // LIFECYCLE HOOKS FILTER PERIODE
    // ------------------------------------------
    public function mount(): void
    {
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

    /**
     * Membuka Modal Tambah Admin Baru
     */
    public function openCreateAdminModal(): void
    {
        $this->reset(['admin_name', 'nip', 'admin_unit_id']);
        $this->resetValidation();
        $this->employee_status = 'nip';
        $this->role = 'unit-admin';
        $this->showCreateAdminModal = true;
    }

    /**
     * Menutup Modal Tambah Admin
     */
    public function closeCreateAdminModal(): void
    {
        $this->showCreateAdminModal = false;
    }

    /**
     * Menyimpan Data Admin Baru & Auto-generate Kredensial
     */
    public function saveAdmin(): void
    {
        $rules = [
            'admin_name'      => 'required|string|max:100',
            'employee_status' => 'required|in:nip,non_nip',
            'role'            => 'required|in:master-admin,unit-admin',
        ];

        if ($this->employee_status === 'nip') {
            $rules['nip'] = 'required|numeric|digits:18|unique:users,nip';
        }

        if ($this->role === 'unit-admin') {
            $rules['admin_unit_id'] = 'required|exists:units,id';
        }

        $this->validate($rules, [
            'nip.required'           => 'NIP wajib diisi untuk Pegawai NIP.',
            'nip.digits'             => 'NIP harus berjumlah 18 digit angka.',
            'admin_unit_id.required' => 'Unit Usaha wajib dipilih untuk Unit Admin.',
        ]);

        // Auto-generate Username & Password
        $username = Str::slug($this->admin_name, '.') . '.' . rand(100, 999);
        $plainPassword = Str::random(8);

        $user = User::create([
            'name'                 => $this->admin_name,
            'username'             => $username,
            'password'             => Hash::make($plainPassword),
            'employee_status'      => $this->employee_status,
            'nip'                  => $this->employee_status === 'nip' ? $this->nip : null,
            'unit_id'              => $this->role === 'unit-admin' ? $this->admin_unit_id : null,
            'is_active'            => true,
            'must_change_password' => true,
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($this->role);
        }

        // Tampilkan modal kredensial
        $this->createdCredentials = [
            'title'    => '🎉 Akun Admin Berhasil Dibuat!',
            'name'     => $user->name,
            'username' => $user->username,
            'password' => $plainPassword,
        ];

        $this->closeCreateAdminModal();
    }

    /**
     * Membuka Modal Tambah Unit Usaha Baru
     */
    public function openCreateUnitModal(): void
    {
        $this->resetValidation();
        $this->reset(['unitId', 'name', 'department', 'category', 'pic_name', 'phone', 'description']);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    /**
     * Membuka Modal Edit Unit Usaha
     */
    public function editUnit(int $id): void
    {
        $unit = Unit::findOrFail($id);

        $this->resetValidation();
        $this->unitId      = $unit->id;
        $this->name        = $unit->name;
        $this->department  = $unit->department ?? '';
        $this->category    = $unit->category ?? '';
        $this->pic_name    = $unit->pic_name ?? '';
        $this->phone       = $unit->phone ?? '';
        $this->description = $unit->description ?? '';
        $this->is_active   = (bool) $unit->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    /**
     * Menutup Modal Unit Usaha
     */
    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * Menyimpan atau Memperbarui Data Unit Usaha
     */
    public function save(): void
    {
        $validated = $this->validate([
            'name'        => 'required|string|max:255',
            'department'  => 'required|string',
            'category'    => 'required|string',
            'pic_name'    => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data = array_merge($validated, [
            'slug' => Str::slug($this->name),
        ]);

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            $data
        );

        $this->closeModal();
        session()->flash('message', $this->isEditing ? 'Unit Usaha berhasil diperbarui.' : 'Unit Usaha baru berhasil ditambahkan.');
    }

    /**
     * Ekspor seluruh data Dashboard Master Admin ke Excel (multi-sheet)
     */
    public function export()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        $periodLabel = match ($this->periodFilter) {
            'today'        => 'Hari Ini',
            'this_week'    => 'Minggu Ini',
            'this_month'   => 'Bulan Ini',
            'this_quarter' => 'Kuartal Ini',
            'this_year'    => 'Tahun Ini',
            'last_month'   => 'Bulan Lalu',
            'custom'       => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            default        => 'Bulan Ini',
        };

        // Kontribusi Omzet per Unit — DIPERKAYA dengan jumlah transaksi, rata-rata, rentang tanggal
        $unitContributions = FinanceTransaction::query()
            ->join('units', 'finance_transactions.unit_id', '=', 'units.id')
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->selectRaw('
                units.name,
                SUM(finance_transactions.amount) as total_income,
                COUNT(finance_transactions.id) as trx_count,
                AVG(finance_transactions.amount) as avg_amount,
                MIN(finance_transactions.transaction_date) as first_trx_date,
                MAX(finance_transactions.transaction_date) as last_trx_date
            ')
            ->groupBy('units.id', 'units.name')
            ->orderByDesc('total_income')
            ->get();

        $grandTotalContribution = $unitContributions->sum('total_income');

        $revenueContribution = [
            'labels' => [], 'series' => [], 'percentages' => [],
            'counts' => [], 'averages' => [], 'firstDates' => [], 'lastDates' => [],
        ];

        foreach ($unitContributions as $contrib) {
            $val = (float) $contrib->total_income;
            $revenueContribution['labels'][]      = $contrib->name;
            $revenueContribution['series'][]      = $val;
            $revenueContribution['percentages'][] = $grandTotalContribution > 0
                ? round(($val / $grandTotalContribution) * 100, 1)
                : 0;
            $revenueContribution['counts'][]     = (int) $contrib->trx_count;
            $revenueContribution['averages'][]   = (float) $contrib->avg_amount;
            $revenueContribution['firstDates'][] = $contrib->first_trx_date;
            $revenueContribution['lastDates'][]  = $contrib->last_trx_date;
        }

        $allUnitsCount    = Unit::count();
        $activeUnitsCount = Unit::where('is_active', true)->count();

        $summary = [
            'totalRevenue'       => $grandTotalContribution,
            'totalUnits'         => $allUnitsCount,
            'activeUnits'        => $activeUnitsCount,
            'inactiveUnits'      => $allUnitsCount - $activeUnitsCount,
            'totalAdmins'        => User::all()->filter(fn ($u) => method_exists($u, 'isUnitAdmin') ? $u->isUnitAdmin() : true)->count(),
            'totalTransactions'  => (int) $unitContributions->sum('trx_count'),
            'avgTransactionValue'=> $unitContributions->sum('trx_count') > 0
                ? $grandTotalContribution / $unitContributions->sum('trx_count')
                : 0,
        ];

        $filters = [
            'searchAdmin' => $this->searchAdmin,
            'searchUnit'  => $this->searchUnit,
        ];

        $fileName = 'Laporan_Master_Admin_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new DashboardExport($summary, $filters, $revenueContribution, $periodLabel),
            $fileName
        );
    }

    public function render()
    {
        Carbon::setLocale('id');

        // Rentang tanggal berdasarkan filter periode
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();

        // Label teks periode untuk dikirim ke view Blade
        $periodLabel = match ($this->periodFilter) {
            'today'        => 'Hari Ini',
            'this_week'    => 'Minggu Ini',
            'this_month'   => 'Bulan Ini',
            'this_quarter' => 'Kuartal Ini',
            'this_year'    => 'Tahun Ini',
            'last_month'   => 'Bulan Lalu',
            'custom'       => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            default        => 'Bulan Ini',
        };

        $units = Unit::with('users')
            ->when($this->searchUnit, fn ($q) => $q->where('name', 'like', '%' . $this->searchUnit . '%')
                                                  ->orWhere('department', 'like', '%' . $this->searchUnit . '%'))
            ->orderBy('name')
            ->get();

        $users = User::with(['unit', 'roles'])
            ->when($this->searchAdmin, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchAdmin . '%')
                      ->orWhere('username', 'like', '%' . $this->searchAdmin . '%')
                      ->orWhere('email', 'like', '%' . $this->searchAdmin . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $logs = AuthLog::latest()->limit(6)->get();

        // TRANSAKSI TERKINI
        $recentTransactions = FinanceTransaction::with('unit')
            ->latest('transaction_date')
            ->latest('id')
            ->limit(6)
            ->get();

        $allUnitsCount    = Unit::count();
        $activeUnitsCount = Unit::where('is_active', true)->count();

        // -------------------------------------------------------------
        // DATA KONTRIBUSI OMZET PER UNIT USAHA (DINAMIS & TERFILTER)
        // -------------------------------------------------------------
        $unitContributions = FinanceTransaction::query()
            ->join('units', 'finance_transactions.unit_id', '=', 'units.id')
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
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

        return view('livewire.master.dashboard', [
            'units'               => $units,
            'users'               => $users,
            'logs'                => $logs,
            'recentTransactions'  => $recentTransactions,
            'totalRevenue'        => 'Rp ' . number_format($grandTotalContribution, 0, ',', '.'),
            'periodLabel'         => $periodLabel,
            'revenueContribution' => $revenueContribution,
            'totalUnits'          => $allUnitsCount,
            'activeUnits'         => $activeUnitsCount,
            'inactiveUnits'       => $allUnitsCount - $activeUnitsCount,
            'totalAdmins'         => User::all()->filter(fn ($u) => method_exists($u, 'isUnitAdmin') ? $u->isUnitAdmin() : true)->count(),
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
            'access.forbidden'        => ['label' => 'Akses Ditolak', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            default                   => ['label' => ucwords(str_replace(['.', '_'], ' ', $event)), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
        };
    }
}