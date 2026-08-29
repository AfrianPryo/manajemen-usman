<?php
// config/menu.php

return [
    // ================= ANALYTICS (MASTER) =================
    [
        'label' => 'Analytics',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Dashboard', 'route' => 'master.dashboard', 'roles' => ['master-admin']],
            ['label' => 'Statistik Usaha', 'route' => 'master.analytics.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= MASTER MANAGEMENT (MASTER) =================
    [
        'label' => 'Master Management',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Unit Usaha', 'route' => 'master.units.index', 'roles' => ['master-admin']],
            ['label' => 'Admin', 'route' => 'master.users.index', 'roles' => ['master-admin']],
            ['label' => 'Vendor', 'route' => 'master.vendors.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= OPERASIONAL (MASTER) =================
    [
        'label' => 'Operasional',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'master.transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Transaksi Berulang', 'route' => 'master.recurring-transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Inventaris', 'route' => 'master.inventory.index', 'roles' => ['master-admin']],
            ['label' => 'Aset Unit Usaha', 'route' => 'master.assets.index', 'roles' => ['master-admin']],
            ['label' => 'Laporan', 'route' => 'master.documents.index', 'roles' => ['master-admin']],
            ['label' => 'Export Data', 'route' => 'master.exports.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= SYSTEM (MASTER) =================
    [
        'label' => 'System',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Aktivitas', 'route' => 'master.activities.index', 'roles' => ['master-admin']],
            ['label' => 'Audit Log', 'route' => 'master.audit-logs.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= SETTINGS (MASTER) =================
    [
        'label' => 'Settings',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Pengaturan Sistem', 'route' => 'master.settings.index', 'roles' => ['master-admin']],
            ['label' => 'Profil Saya', 'route' => 'master.profile.index', 'roles' => ['master-admin']],
        ],
    ],

    // =========================================================================
    // GRUP DI BAWAH INI UNTUK role 'unit-admin'.
    //
    // Strukturnya sengaja dibuat sepadan (mirror) dengan grup Master di atas:
    // "Operasional" <-> "Operasional Unit", "System" <-> "System" dst — supaya
    // pengalaman navigasi terasa konsisten antara dua peran ini. Yang TIDAK
    // ada padanannya (dan memang sengaja tidak dibuat) adalah "Master
    // Management" (Unit Usaha/Admin/Vendor -- data lintas-unit, hanya
    // dikelola dari sisi Master) dan "Pengaturan Sistem" di grup Settings
    // (unit-admin hanya punya "Profil Saya", lihat juga perubahan di
    // components/layouts/app.blade.php untuk tombol Pengaturan Sistem
    // di pojok bawah sidebar).
    //
    // PENTING soal route: semua route 'unit.*' butuh parameter {unit:slug}.
    // components/layouts/app.blade.php SUDAH disesuaikan untuk otomatis
    // menyisipkan slug unit milik user login saat me-render item dengan
    // prefix 'unit.' -- jadi di sini cukup tulis nama route-nya saja,
    // TIDAK PERLU (dan tidak bisa) menuliskan parameternya di config ini.
    // =========================================================================

    [
        'label' => 'Unit Usaha Saya',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Dashboard', 'route' => 'unit.dashboard', 'roles' => ['unit-admin']],
        ],
    ],

    [
        'label' => 'Operasional Unit',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'unit.transactions.index', 'roles' => ['unit-admin']],
            ['label' => 'Transaksi Berulang', 'route' => 'unit.recurring-transactions.index', 'roles' => ['unit-admin']],
            ['label' => 'Inventaris', 'route' => 'unit.inventory.index', 'roles' => ['unit-admin']],
            ['label' => 'Aset Unit Usaha', 'route' => 'unit.assets.index', 'roles' => ['unit-admin']],
            ['label' => 'Dokumen Resmi', 'route' => 'unit.documents.index', 'roles' => ['unit-admin']],
            ['label' => 'Export Data', 'route' => 'unit.exports.index', 'roles' => ['unit-admin']],
        ],
    ],

    [
        'label' => 'System',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Aktivitas', 'route' => 'unit.activities.index', 'roles' => ['unit-admin']],
        ],
    ],

    [
        'label' => 'Settings',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Profil Saya', 'route' => 'unit.profile.index', 'roles' => ['unit-admin']],
        ],
    ],
];
