<?php

use App\Livewire\Auth\Login;
use App\Livewire\Master\Activities\Index as ActivitiesIndex;
use App\Livewire\Master\Analytics\Index as AnalyticsIndex;
use App\Livewire\Master\Asset\Index as AssetIndex;
use App\Livewire\Master\AuditLog\Index as AuditLogsIndex;
use App\Livewire\Master\Dashboard as MasterDashboard;
use App\Livewire\Master\Exports\Index as ExportsIndex; // Import Baru
use App\Livewire\Master\Inventory\Index as InventoryIndex;
use App\Livewire\Master\Profile\Index as ProfileIndex;
use App\Livewire\Master\RecurringTransaction\Index as RecurringTransactionIndex;
use App\Livewire\Master\Reports\Index as ReportsIndex;
use App\Livewire\Master\Settings\Index as SettingsIndex;
use App\Livewire\Master\Transactions\Index as TransactionsIndex;
use App\Livewire\Master\Units\Index as UnitsIndex;
use App\Livewire\Master\Users\Index as UsersIndex;
use App\Livewire\Master\Vendor\Index as VendorIndex;
use App\Livewire\Password\ChangePassword;
use App\Livewire\Unit\Dashboard as UnitDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Landing Page (Publik)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// 2. Route Guest (Hanya untuk user yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Route Logout Khusus Development (Hanya Aktif di Lokal)
if (app()->environment('local', 'testing')) {
    Route::get('/dev-logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('dev.logout');
}

// 3. Base Authenticated Routes (Butuh Login, Status Aktif, dan Sesi Tunggal)
Route::middleware(['auth', 'user.active', 'single.session'])->group(function () {

    // Logout Action
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Halaman Ganti Password (Dapat diakses wajib maupun mandiri)
    Route::get('/password/change', ChangePassword::class)->name('password.change');

    // 4. Fully Protected Routes (Memerlukan verifikasi 'password.change')
    Route::middleware('password.change')->group(function () {

        // Smart Redirect Dashboard
        Route::get('/dashboard', function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            if ($user->isMasterAdmin()) {
                return redirect()->route('master.dashboard');
            }

            if ($user->isUnitAdmin()) {
                if ($user->unit) {
                    return redirect()->route('unit.dashboard', $user->unit->slug);
                }
                abort(403, 'Akun Admin Unit Anda belum terhubung dengan Unit Usaha manapun.');
            }

            return redirect('/');
        })->name('dashboard');

        // ================= MASTER ADMIN ROUTES =================
        Route::middleware('role:master-admin')->prefix('master')->name('master.')->group(function () {
            Route::get('/dashboard', MasterDashboard::class)->name('dashboard');
            
            // Master Management
            Route::get('/units', UnitsIndex::class)->name('units.index');
            Route::get('/users', UsersIndex::class)->name('users.index');
            Route::get('/vendors', VendorIndex::class)->name('vendors.index');
            
            // Operasional
            Route::get('/transactions', TransactionsIndex::class)->name('transactions.index');
            Route::get('/recurring-transactions', RecurringTransactionIndex::class)->name('recurring-transactions.index');
            Route::get('/inventory', InventoryIndex::class)->name('inventory.index');
            Route::get('/assets', AssetIndex::class)->name('assets.index');
            Route::get('/reports', ReportsIndex::class)->name('reports.index');
            Route::get('/exports', ExportsIndex::class)->name('exports.index'); // Route Baru
            
            // Analytics
            Route::get('/analytics', AnalyticsIndex::class)->name('analytics.index');
            
            // System
            Route::get('/activities', ActivitiesIndex::class)->name('activities.index');
            Route::get('/audit-logs', AuditLogsIndex::class)->name('audit-logs.index');
            
            // Settings
            Route::get('/settings', SettingsIndex::class)->name('settings.index');
            Route::get('/profile', ProfileIndex::class)->name('profile.index');
        });

        // ================= UNIT ADMIN ROUTES =================
        Route::prefix('unit/{unit:slug}')->middleware('unit.access')->group(function () {
            Route::get('/dashboard', UnitDashboard::class)->name('unit.dashboard');
        });

    });
});