<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h2 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .low { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Stok Barang</h2>
    <p class="subtitle">Dicetak pada {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->stock }} {{ $product->unit }}</td>
                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td class="{{ $product->isLowStock() ? 'low' : '' }}">{{ $product->isLowStock() ? 'Menipis' : 'Aman' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
