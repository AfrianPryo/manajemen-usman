<?php

namespace App\Http\Controllers;

use App\Exports\FinanceReportExport;
use App\Exports\StockReportExport;
use App\Models\FinanceTransaction;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    public function stockPdf()
    {
        $products = Product::with('category')->orderBy('name')->get();

        $pdf = Pdf::loadView('reports.stock-pdf', ['products' => $products]);

        return $pdf->stream('laporan-stok-' . now()->format('Y-m-d') . '.pdf');
    }

    public function stockExcel()
    {
        return Excel::download(new StockReportExport, 'laporan-stok-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function financePdf(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $transactions = FinanceTransaction::with('category')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $monthNum)
            ->orderBy('transaction_date')
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');

        $pdf = Pdf::loadView('reports.finance-pdf', [
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'month' => $month,
        ]);

        return $pdf->stream('laporan-keuangan-' . $month . '.pdf');
    }

    public function financeExcel(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        return Excel::download(new FinanceReportExport($month), 'laporan-keuangan-' . $month . '.xlsx');
    }
}
