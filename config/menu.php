<?php
// config/menu.php

return [
    // ================= ANALYTICS =================
    [
        'label' => 'Analytics',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Dashboard', 'route' => 'master.dashboard', 'roles' => ['master-admin']],
            ['label' => 'Statistik Usaha', 'route' => 'master.analytics.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= MASTER MANAGEMENT =================
    [
        'label' => 'Master Management',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Unit Usaha', 'route' => 'master.units.index', 'roles' => ['master-admin']],
            ['label' => 'Admin', 'route' => 'master.users.index', 'roles' => ['master-admin']],
            ['label' => 'Vendor', 'route' => 'master.vendors.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= OPERASIONAL =================
    [
        'label' => 'Operasional',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'master.transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Transaksi Berulang', 'route' => 'master.recurring-transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Inventaris', 'route' => 'master.inventory.index', 'roles' => ['master-admin']],
            ['label' => 'Aset Unit Usaha', 'route' => 'master.assets.index', 'roles' => ['master-admin']],
            ['label' => 'Laporan', 'route' => 'master.documents.index', 'roles' => ['master-admin']],
            ['label' => 'Export Data', 'route' => 'master.exports.index', 'roles' => ['master-admin']], // Baru
        ],
    ],

    // ================= SYSTEM =================
    [
        'label' => 'System',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Aktivitas', 'route' => 'master.activities.index', 'roles' => ['master-admin']],
            ['label' => 'Audit Log', 'route' => 'master.audit-logs.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= SETTINGS =================
    [
        'label' => 'Settings',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Pengaturan Sistem', 'route' => 'master.settings.index', 'roles' => ['master-admin']],
            ['label' => 'Profil Saya', 'route' => 'master.profile.index', 'roles' => ['master-admin']],
        ],
    ],
];