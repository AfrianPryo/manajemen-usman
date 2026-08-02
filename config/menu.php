<?php

/**
 * Konfigurasi menu sidebar.
 * Untuk menambah menu baru, cukup tambahkan array baru di sini.
 * 'roles' => null artinya semua role yang login bisa lihat menu ini.
 */
return [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon'  => 'home',
        'roles' => null,
    ],
    [
        'label' => 'Master Data',
        'icon'  => 'folder',
        'roles' => ['admin'],
        'children' => [
            ['label' => 'Kategori Produk', 'route' => 'categories.index', 'roles' => ['admin']],
            ['label' => 'Produk', 'route' => 'products.index', 'roles' => ['admin']],
        ],
    ],
    [
        'label' => 'Stok / Inventori',
        'icon'  => 'box',
        'roles' => ['admin', 'petugas'],
        'children' => [
            ['label' => 'Data Stok', 'route' => 'stock.index', 'roles' => ['admin', 'petugas']],
            ['label' => 'Riwayat Mutasi', 'route' => 'stock.history', 'roles' => ['admin', 'petugas']],
        ],
    ],
    [
        'label' => 'Keuangan',
        'icon'  => 'wallet',
        'roles' => ['admin', 'bendahara'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'finance.index', 'roles' => ['admin', 'bendahara']],
            ['label' => 'Kategori Keuangan', 'route' => 'finance.categories', 'roles' => ['admin', 'bendahara']],
        ],
    ],
    [
        'label' => 'Laporan',
        'icon'  => 'chart',
        'roles' => ['admin', 'bendahara'],
        'children' => [
            ['label' => 'Laporan Stok', 'route' => 'reports.stock', 'roles' => ['admin', 'petugas']],
            ['label' => 'Laporan Laba-Rugi', 'route' => 'reports.finance', 'roles' => ['admin', 'bendahara']],
        ],
    ],
    [
        'label' => 'Manajemen User',
        'route' => 'users.index',
        'icon'  => 'users',
        'roles' => ['admin'],
    ],
];
