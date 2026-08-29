<?php

use App\Livewire\Auth\Login;
use App\Livewire\Master\Activities\Index as ActivitiesIndex;
use App\Livewire\Master\Analytics\Index as AnalyticsIndex;
use App\Livewire\Master\Asset\Index as AssetIndex;
use App\Livewire\Master\AuditLog\Index as AuditLogsIndex;
use App\Livewire\Master\Dashboard as MasterDashboard;
use App\Livewire\Master\Documents\Dashboard as DocumentsDashboard;
use App\Livewire\Master\Documents\Generate as DocumentsGenerate;
use App\Livewire\Master\Documents\History as DocumentsHistory;
use App\Livewire\Master\Documents\SignatureSettings as DocumentsSignatureSettings;
use App\Livewire\Master\Documents\TemplateManager as DocumentsTemplateManager;
use App\Livewire\Master\Exports\Index as ExportsIndex;
use App\Livewire\Master\Inventory\Index as InventoryIndex;
use App\Livewire\Master\Profile\Index as ProfileIndex;
use App\Livewire\Master\RecurringTransaction\Index as RecurringTransactionIndex;
use App\Livewire\Master\Settings\Index as SettingsIndex;
use App\Livewire\Master\Transactions\Index as TransactionsIndex;
use App\Livewire\Master\Units\Index as UnitsIndex;
use App\Livewire\Master\Users\Index as UsersIndex;
use App\Livewire\Master\Vendor\Index as VendorIndex;
use App\Livewire\Password\ChangePassword;
use App\Livewire\Unit\Activities\Index as UnitActivitiesIndex;
use App\Livewire\Unit\Asset\Index as UnitAssetIndex;
use App\Livewire\Unit\Dashboard as UnitDashboard;
use App\Livewire\Unit\Documents\Dashboard as UnitDocumentsDashboard;
use App\Livewire\Unit\Documents\Generate as UnitDocumentsGenerate;
use App\Livewire\Unit\Documents\History as UnitDocumentsHistory;
use App\Livewire\Unit\Documents\SignatureSettings as UnitDocumentsSignatureSettings;
use App\Livewire\Unit\Exports\Index as UnitExportsIndex;
use App\Livewire\Unit\Inventory\Index as UnitInventoryIndex;
use App\Livewire\Unit\Profile\Index as UnitProfileIndex;
use App\Livewire\Unit\RecurringTransaction\Index as UnitRecurringTransactionIndex;
use App\Livewire\Unit\Transactions\Index as UnitTransactionsIndex;
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

            // Dokumen Resmi (pengganti menu Laporan Konsolidasi)
            Route::prefix('dokumen-resmi')->name('documents.')->group(function () {
                Route::get('/', DocumentsDashboard::class)->name('index');
                Route::get('/buat', DocumentsGenerate::class)->name('generate');
                Route::get('/riwayat', DocumentsHistory::class)->name('history');
                Route::get('/template', DocumentsTemplateManager::class)->name('templates');
                Route::get('/tanda-tangan', DocumentsSignatureSettings::class)->name('signature');
            });

            Route::get('/exports', ExportsIndex::class)->name('exports.index');

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
        // Struktur & penamaan route di grup ini SENGAJA dibuat semirip
        // mungkin dengan grup 'master' di atas (nama child route yang sama:
        // dashboard, documents.index/generate/history/signature, exports.index,
        // activities.index, profile.index) supaya blade yang di-reuse bersama
        // Master (lihat resources/views/livewire/master/documents/*.blade.php)
        // bisa menghitung nama route lawannya tinggal ganti prefix
        // 'master.' -> 'unit.'.
        //
        // HANYA middleware 'unit.access' (BUKAN 'role:unit-admin'). Middleware
        // EnsureUnitAccess sudah menangani dua kasus sekaligus: Master Admin
        // boleh mengakses unit MANA PUN untuk keperluan monitoring, sedangkan
        // Unit Admin hanya boleh mengakses unit miliknya sendiri. Menambahkan
        // 'role:unit-admin' di sini akan memblokir Master Admin (403) saat
        // mencoba melihat dashboard/menu unit dari sisi Master — itu bug,
        // bukan fitur, jadi jangan ditambahkan lagi.
        //
        // TIDAK ADA route untuk: units/users/vendors (data lintas-unit,
        // memang cuma dikelola dari sisi Master), documents.templates
        // (template dikelola terpusat oleh Master), settings.index &
        // audit-logs.index (pengaturan sistem & audit log lintas-unit
        // bukan wilayah unit-admin — unit-admin punya activities.index
        // sendiri sebagai log aktivitas unitnya).
        Route::prefix('unit/{unit:slug}')->middleware('unit.access')->name('unit.')->group(function () {
            Route::get('/dashboard', UnitDashboard::class)->name('dashboard');

            // Operasional
            Route::get('/transactions', UnitTransactionsIndex::class)->name('transactions.index');
            Route::get('/recurring-transactions', UnitRecurringTransactionIndex::class)->name('recurring-transactions.index');
            Route::get('/inventory', UnitInventoryIndex::class)->name('inventory.index');
            Route::get('/assets', UnitAssetIndex::class)->name('assets.index');

            // Dokumen Resmi (tanpa 'template', lihat catatan di atas)
            Route::prefix('dokumen-resmi')->name('documents.')->group(function () {
                Route::get('/', UnitDocumentsDashboard::class)->name('index');
                Route::get('/buat', UnitDocumentsGenerate::class)->name('generate');
                Route::get('/riwayat', UnitDocumentsHistory::class)->name('history');
                Route::get('/tanda-tangan', UnitDocumentsSignatureSettings::class)->name('signature');
            });

            Route::get('/exports', UnitExportsIndex::class)->name('exports.index');

            // System (log aktivitas unit sendiri)
            Route::get('/activities', UnitActivitiesIndex::class)->name('activities.index');

            // Settings
            Route::get('/profile', UnitProfileIndex::class)->name('profile.index');
        });

    });
});
