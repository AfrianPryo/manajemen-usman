<?php

use App\Http\Controllers\ReportExportController;
use App\Livewire\Auth\Login;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Dashboard;
use App\Livewire\Finance\Categories as FinanceCategories;
use App\Livewire\Finance\Index as FinanceIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Reports\FinanceReport;
use App\Livewire\Reports\StockReport;
use App\Livewire\Stock\History as StockHistory;
use App\Livewire\Stock\Index as StockIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing page publik
Route::get('/', function () {
    return view('landing');
})->name('landing');

/*
|--------------------------------------------------------------------------
| Guest routes (login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login'); // dipindah dari '/' ke '/login'
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/categories', CategoriesIndex::class)->name('categories.index');
        Route::get('/products', ProductsIndex::class)->name('products.index');
        Route::get('/users', UsersIndex::class)->name('users.index');
    });

    Route::middleware('role:admin|petugas')->group(function () {
        Route::get('/stock', StockIndex::class)->name('stock.index');
        Route::get('/stock/history', StockHistory::class)->name('stock.history');
        Route::get('/reports/stock', StockReport::class)->name('reports.stock');
        Route::get('/reports/stock/pdf', [ReportExportController::class, 'stockPdf'])->name('reports.stock.pdf');
        Route::get('/reports/stock/excel', [ReportExportController::class, 'stockExcel'])->name('reports.stock.excel');
    });

    Route::middleware('role:admin|bendahara')->group(function () {
        Route::get('/finance', FinanceIndex::class)->name('finance.index');
        Route::get('/finance/categories', FinanceCategories::class)->name('finance.categories');
        Route::get('/reports/finance', FinanceReport::class)->name('reports.finance');
        Route::get('/reports/finance/pdf', [ReportExportController::class, 'financePdf'])->name('reports.finance.pdf');
        Route::get('/reports/finance/excel', [ReportExportController::class, 'financeExcel'])->name('reports.finance.excel');
    });
});