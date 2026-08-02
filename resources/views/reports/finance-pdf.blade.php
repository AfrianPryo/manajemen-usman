<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h2 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .summary td { font-weight: bold; }
        .income { color: #15803d; }
        .expense { color: #b91c1c; }
    </style>
</head>
<body>
    <h2>Laporan Laba - Rugi</h2>
    <p class="subtitle">Periode: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</p>

    <table class="summary">
        <tr><td>Total Pemasukan</td><td class="income">Rp {{ number_format($income, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pengeluaran</td><td class="expense">Rp {{ number_format($expense, 0, ',', '.') }}</td></tr>
        <tr><td>Saldo Bersih</td><td>Rp {{ number_format($balance, 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td>{{ $trx->transaction_date->format('d-m-Y') }}</td>
                    <td>{{ $trx->category->name }}</td>
                    <td>{{ $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                    <td>Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                    <td>{{ $trx->description ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
