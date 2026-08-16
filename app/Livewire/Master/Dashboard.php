<?php

namespace App\Livewire\Master;

use App\Models\AuthLog;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
#[Title('Dashboard Master Admin')]
class Dashboard extends Component
{
    #[Url(as: 'q_admin', history: true)]
    public string $searchAdmin = '';

    #[Url(as: 'q_unit', history: true)]
    public string $searchUnit = '';

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
     * Ekspor Data Laporan ke Format Excel (.xlsx)
     */
    public function export(): StreamedResponse
    {
        $fileName = 'Laporan_Master_Admin_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan');

            $row = 1;

            // 1. DATA ADMIN
            $sheet->setCellValue("A{$row}", 'DATA ADMIN');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;

            $headersAdmin = ['No', 'Nama', 'Username', 'Role', 'Unit Kerja', 'Status', 'Login Terakhir'];
            $sheet->fromArray($headersAdmin, null, "A{$row}");

            $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('EEEEEE');
            $row++;

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

            $noAdmin = 1;
            foreach ($users as $user) {
                $lastLogin = $user->last_login_at 
                    ? Carbon::parse($user->last_login_at)->format('Y-m-d H:i')
                    : '-';

                $isMaster = method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin();

                $sheet->setCellValue("A{$row}", $noAdmin++);
                $sheet->setCellValue("B{$row}", $user->name);
                $sheet->setCellValue("C{$row}", $user->username ?? explode('@', $user->email)[0]);
                $sheet->setCellValue("D{$row}", $isMaster ? 'Master Admin' : 'Admin Unit');
                $sheet->setCellValue("E{$row}", $isMaster ? 'Semua Unit' : ($user->unit->name ?? '-'));
                $sheet->setCellValue("F{$row}", $user->is_active ? 'Aktif' : 'Nonaktif');
                $sheet->setCellValue("G{$row}", $lastLogin);
                $row++;
            }

            $row += 2;

            // 2. DATA UNIT USAHA
            $sheet->setCellValue("A{$row}", 'DATA UNIT USAHA');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;

            $headersUnit = ['No', 'Nama Unit', 'Jurusan', 'Status', 'Nama PJ', 'Username PJ'];
            $sheet->fromArray($headersUnit, null, "A{$row}");

            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:F{$row}")->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('EEEEEE');
            $row++;

            $units = Unit::with('users')->orderBy('name')->get();
            $noUnit = 1;

            foreach ($units as $unit) {
                $pj = $unit->users->first();

                $sheet->setCellValue("A{$row}", $noUnit++);
                $sheet->setCellValue("B{$row}", $unit->name);
                $sheet->setCellValue("C{$row}", $unit->department ?? '-');
                $sheet->setCellValue("D{$row}", $unit->is_active ? 'Aktif' : 'Nonaktif');
                $sheet->setCellValue("E{$row}", $pj ? $pj->name : '-');
                $sheet->setCellValue("F{$row}", $pj ? ($pj->username ?? explode('@', $pj->email)[0]) : '-');
                $row++;
            }

            // 3. ALIGNMENT & AUTO-FIT
            $highestColumn = $sheet->getHighestColumn();
            $highestRow    = $sheet->getHighestRow();

            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            foreach (range('A', $highestColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    public function render()
    {
        Carbon::setLocale('id');

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

        $allUnitsCount    = Unit::count();
        $activeUnitsCount = Unit::where('is_active', true)->count();

        // -------------------------------------------------------------
        // DATA KONTRIBUSI OMZET PER UNIT USAHA
        // Total Omzet: Rp 148.500.000
        // -------------------------------------------------------------
        $revenueContribution = [
            'labels'      => ['Bengkel TO', 'Tefa PPLG', 'Kantin MPLB', 'Ritel PM', 'Jasa Akuntansi'],
            'series'      => [51975000, 37125000, 26730000, 17820000, 14850000], // Nominal (Rp)
            'percentages' => [35, 25, 18, 12, 10], // Persentase (%)
        ];

        return view('livewire.master.dashboard', [
            'units'               => $units,
            'users'               => $users,
            'logs'                => $logs,
            'totalRevenue'        => 'Rp ' . number_format(array_sum($revenueContribution['series']), 0, ',', '.'),
            'revenueContribution' => $revenueContribution, // <-- Dikirim ke View
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