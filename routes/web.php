<?php

use App\Livewire\Auth\Login;
use App\Livewire\Password\ChangePassword;
use App\Livewire\Unit\Dashboard as UnitDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Master\Dashboard as MasterDashboard;
use App\Livewire\Master\Units\Index as UnitsIndex;
use App\Livewire\Master\Users\Index as UsersIndex;
use App\Livewire\Master\Transactions\Index as TransactionsIndex;
use App\Livewire\Master\Inventory\Index as InventoryIndex;
use App\Livewire\Master\Reports\Index as ReportsIndex;
use App\Livewire\Master\Analytics\Index as AnalyticsIndex;
use App\Livewire\Master\Activities\Index as ActivitiesIndex;
use App\Livewire\Master\AuditLogs\Index as AuditLogsIndex;
use App\Livewire\Master\Settings\Index as SettingsIndex;
use App\Livewire\Master\Profile\Index as ProfileIndex;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (publik, semua orang bisa akses)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// 🔴 ROUTE LOGIN - HANYA UNTUK YANG BELUM LOGIN (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// 🔴 ROUTE LOGOUT DARURAT (KHUSUS DEVELOPMENT - hapus saat production)
Route::get('/dev-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    
    return response('
        <h1>✅ Berhasil Logout!</h1>
        <p>Session Anda telah dihancurkan.</p>
        <p><a href="/login" style="background:blue;color:white;padding:10px 20px;text-decoration:none;display:inline-block;">👉 Klik di sini untuk Login</a></p>
        <p><a href="/">← Kembali ke Beranda</a></p>
    ');
})->name('dev.logout');

// Route yang butuh login (auth)
Route::middleware(['auth', 'user.active', 'single.session', 'password.change'])->group(function () {

    Route::get('/dashboard', function () {
        // 🔴 TAMBAHKAN BARIS INI untuk memberitahu IDE tipe datanya
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Karena sudah di dalam middleware 'auth', $user pasti tidak null
        if ($user->isMasterAdmin()) {
            return redirect()->route('master.dashboard');
        }
        
        if ($user->isUnitAdmin() && $user->unit) {
            return redirect()->route('unit.dashboard', $user->unit->slug);
        }
        
        return redirect('/');
    })->name('dashboard');
    
    // Logout (POST)
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Change Password
    Route::get('/password/change', ChangePassword::class)->name('password.change');

    //Master
    Route::middleware(['auth', 'user.active', 'single.session', 'password.change'])->group(function () {
        
        // ... (route smart dashboard, logout, password change tetap sama)

        // ================= MASTER ADMIN ROUTES =================
        Route::middleware('role:master-admin')->prefix('master')->name('master.')->group(function () {
            Route::get('/dashboard', MasterDashboard::class)->name('dashboard');
            
            // Master Management
            Route::get('/units', UnitsIndex::class)->name('units.index');
            Route::get('/users', UsersIndex::class)->name('users.index');
            
            // Operasional
            Route::get('/transactions', TransactionsIndex::class)->name('transactions.index');
            Route::get('/inventory', InventoryIndex::class)->name('inventory.index');
            Route::get('/reports', ReportsIndex::class)->name('reports.index');
            
            // Analytics
            Route::get('/analytics', AnalyticsIndex::class)->name('analytics.index');
            
            // System
            Route::get('/activities', ActivitiesIndex::class)->name('activities.index');
            Route::get('/audit-logs', AuditLogsIndex::class)->name('audit-logs.index');
            
            // Settings
            Route::get('/settings', SettingsIndex::class)->name('settings.index');
            Route::get('/profile', ProfileIndex::class)->name('profile.index');
        });

        // ... (route unit/{slug} tetap sama)
    });

    // Unit routes
    Route::prefix('unit/{unit:slug}')->middleware('unit.access')->group(function () {
        Route::get('/dashboard', UnitDashboard::class)->name('unit.dashboard');
        // Route operasional unit bisa ditambahkan di sini
    });
});