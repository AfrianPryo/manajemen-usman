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
    // Berisi data yang sifatnya lintas-unit dan cuma dikelola dari sisi
    // Master. 'Pelanggan' (master.customers.index) DITAMBAHKAN di sini --
    // sebelumnya route-nya sudah ada di routes/web.php (persis setelah
    // 'vendors.index', lihat komentar "MANAJEMEN PELANGGAN (LINTAS UNIT)"
    // di sana) tapi belum pernah dikaitkan ke menu manapun, jadi fiturnya
    // "hilang" dari sidebar meski sudah bisa diakses lewat URL langsung.
    // Ditaruh setelah Vendor supaya urutannya sejajar dengan urutan
    // route di web.php (units -> users -> vendors -> customers).
    [
        'label' => 'Master Management',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Unit Usaha', 'route' => 'master.units.index', 'roles' => ['master-admin']],
            ['label' => 'Admin', 'route' => 'master.users.index', 'roles' => ['master-admin']],
            ['label' => 'Vendor', 'route' => 'master.vendors.index', 'roles' => ['master-admin']],
            ['label' => 'Pelanggan', 'route' => 'master.customers.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= OPERASIONAL (MASTER) =================
    // Label 'Laporan' diganti jadi 'Dokumen Resmi' supaya konsisten dengan
    // sisi Unit Admin (lihat 'unit.documents.index' di bawah, labelnya
    // sudah 'Dokumen Resmi') dan dengan komentar di routes/web.php sendiri
    // yang bilang fitur ini "pengganti menu Laporan Konsolidasi" -- route
    // & komponennya (Master\Documents\*) memang sudah lama bukan lagi
    // laporan konsolidasi biasa, tapi modul dokumen resmi (generate,
    // riwayat, template, tanda tangan). Route name TETAP 'master.documents.index',
    // tidak ada yang berubah selain teks label ini.
    [
        'label' => 'Operasional',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'master.transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Transaksi Berulang', 'route' => 'master.recurring-transactions.index', 'roles' => ['master-admin']],
            ['label' => 'Inventaris', 'route' => 'master.inventory.index', 'roles' => ['master-admin']],
            ['label' => 'Aset Unit Usaha', 'route' => 'master.assets.index', 'roles' => ['master-admin']],
            ['label' => 'Dokumen Resmi', 'route' => 'master.documents.index', 'roles' => ['master-admin']],
            ['label' => 'Export Data', 'route' => 'master.exports.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= MANAJEMEN LAYANAN (MASTER, LINTAS UNIT) =================
    // Grup BARU. Pasangan lintas-unit dari grup 'Manajemen Layanan' di sisi
    // Unit Admin (lihat di bawah). Route 'master.service-orders.index'
    // sudah ada di routes/web.php sejak awal (diletakkan persis setelah
    // 'exports.index', lihat komentarnya di sana) tapi belum pernah
    // dimasukkan ke menu ini -- jadi menu-nya "hilang" walau fiturnya
    // sudah jadi.
    //
    // TIDAK diberi key 'unit_category' di sini: berbeda dengan sisi Unit
    // Admin (yang sedang membuka SATU unit tertentu sehingga bisa
    // disembunyikan/ditampilkan berdasarkan kategori unit yang aktif),
    // halaman Master ini lintas-unit dan dirender lewat
    // components/layouts/app.blade.php yang memang tidak punya logic
    // filter 'unit_category' (logic itu cuma ada di
    // components/layouts/unit.blade.php). Pembatasan ke unit berkategori
    // 'jasa' cukup dilakukan di level query pada komponennya sendiri
    // (App\Livewire\Master\ServiceOrder\Index), sama seperti yang sudah
    // dijelaskan di komentar routes/web.php.
    //
    // Diletakkan setelah grup 'Operasional' supaya urutannya sejajar
    // dengan posisi grup 'Manajemen Layanan' di sisi Unit Admin (yang juga
    // diletakkan setelah grup 'Operasional Unit', sebelum 'System').
    [
        'label' => 'Manajemen Layanan',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Pesanan Layanan', 'route' => 'master.service-orders.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= PEMBELIAN (MASTER, LINTAS UNIT, READ-ONLY) =================
    // Grup BARU, pasangan lintas-unit dari grup 'Pembelian' di sisi Unit
    // Admin (lihat di bawah). Route 'master.purchasing.index' -- lihat
    // catatan lengkap "PEMBELIAN (LINTAS UNIT, READ-ONLY)" di
    // routes/web.php untuk kenapa modul ini read-only (bukan CRUD seperti
    // 'Pesanan Layanan' di atas): pembelian tetap harus dicatat dari sisi
    // Unit yang benar-benar berbelanja, di sini Master Admin hanya melihat
    // rekap total belanja per vendor dari SELURUH unit.
    //
    // TIDAK diberi key 'unit_category' juga (sama seperti grup
    // 'Master Management' & 'Pelanggan' di atas) karena Pembelian berlaku
    // untuk SEMUA kategori Unit Usaha, bukan cuma kategori 'jasa'.
    //
    // Diletakkan setelah grup 'Manajemen Layanan' supaya urutannya sejajar
    // dengan posisi route 'purchasing.index' di web.php (juga diletakkan
    // persis setelah 'service-orders.index' di sana).
    [
        'label' => 'Pembelian',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Pembelian Lintas-Unit', 'route' => 'master.purchasing.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= SYSTEM (MASTER) =================
    // 'Pengumuman' DITAMBAHKAN di sini (setelah Audit Log) -- fitur baru
    // untuk Master Admin mengirim pengumuman manual ke seluruh Unit Admin,
    // reuse infrastruktur notifikasi App\Notifications\SystemNotification
    // yang sudah ada. Diletakkan di grup System (bukan grup baru) karena
    // sifatnya administrasi/siaran lintas-unit, sejajar dengan Aktivitas &
    // Audit Log -- lihat App\Livewire\Master\Announcements\Index.
    [
        'label' => 'System',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Aktivitas', 'route' => 'master.activities.index', 'roles' => ['master-admin']],
            ['label' => 'Audit Log', 'route' => 'master.audit-logs.index', 'roles' => ['master-admin']],
            ['label' => 'Pengumuman', 'route' => 'master.announcements.index', 'roles' => ['master-admin']],
        ],
    ],

    // ================= SETTINGS (MASTER) =================
    // Catatan: dulu ada 2 item di sini -- "Pengaturan Sistem"
    // (master.settings.index) dan "Profil Saya" (master.profile.index) --
    // yang keduanya berujung ke pengaturan profil admin master, karena
    // dulu ada halaman profil berdiri sendiri terpisah dari Settings.
    // Halaman & route master.profile.index itu sudah dihapus (profil admin
    // sekarang cukup lewat tab "Profil Admin" di dalam Pengaturan Sistem),
    // jadi item menu "Profil Saya" di grup Master ini ikut dihapus supaya
    // tidak dobel/mengarah ke route yang sudah tidak ada.
    [
        'label' => 'Settings',
        'roles' => ['master-admin'],
        'children' => [
            ['label' => 'Pengaturan Sistem', 'route' => 'master.settings.index', 'roles' => ['master-admin']],
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
    // dikelola dari sisi Master) dan "Audit Log" & "Pengumuman" di grup
    // System (unit-admin hanya punya "Aktivitas" -- mengirim pengumuman
    // cuma wewenang Master Admin, Unit Admin menerimanya lewat
    // NotificationSidebar seperti notifikasi lain, lihat juga perubahan di
    // components/layouts/app.blade.php untuk tombol Pengaturan Sistem
    // di pojok bawah sidebar).
    //
    // PENTING soal route: semua route 'unit.*' butuh parameter {unit:slug}.
    // components/layouts/app.blade.php SUDAH disesuaikan untuk otomatis
    // menyisipkan slug unit milik user login saat me-render item dengan
    // prefix 'unit.' -- jadi di sini cukup tulis nama route-nya saja,
    // TIDAK PERLU (dan tidak bisa) menuliskan parameternya di config ini.
    //
    // PENTING soal 'Profil Saya' (unit.profile.index): komponen di
    // baliknya (App\Livewire\Unit\Profile\Index) sekarang mewarisi
    // App\Livewire\Master\Settings\Index apa adanya -- termasuk SEMUA
    // tabnya ("Profil Admin" & "Fitur & Modul"), bukan cuma tab profil
    // saja -- supaya tidak ada lagi logic profil/password/OTP yang
    // terduplikasi antara Master & Unit. Label menu ini tetap "Profil
    // Saya" (bukan diubah jadi "Pengaturan Sistem") karena route & prefix
    // URL-nya tidak berubah, hanya isi komponennya yang sekarang identik
    // dengan Settings Master.
    //
    // PENTING soal 'Statistik Usaha' (unit.analytics.index): item ini
    // SENGAJA ditaruh di dalam grup "Unit Usaha Saya" berdampingan dengan
    // 'Dashboard' -- persis meniru struktur grup "Analytics" milik Master
    // di atas (Dashboard + Statistik Usaha dalam satu grup yang sama).
    // Component di baliknya (App\Livewire\Unit\Analytics\Index) adalah
    // pasangan satu-unit dari App\Livewire\Master\Analytics\Index: metrik,
    // grafik, dan tabelnya SUDAH otomatis terkunci ke unit yang sedang
    // dibuka (trait ScopedToUnit), jadi TIDAK diberi key 'unit_category' --
    // berlaku untuk kedua kategori Unit Usaha (ritel maupun jasa), sama
    // seperti 'Transaksi', 'Aset Unit Usaha', dsb. di grup "Operasional
    // Unit" di bawah.
    //
    // PENTING soal 'Pelanggan' (unit.customers.index): item ini
    // DITAMBAHKAN di grup "Operasional Unit" di bawah, persis setelah
    // 'Aset Unit Usaha' -- mengikuti urutan route di routes/web.php, di
    // mana 'customers.index' (path '/pelanggan') memang didaftarkan tepat
    // setelah 'assets.index', sebelum grup 'dokumen-resmi'. Sama seperti
    // 'Statistik Usaha' di atas, item ini TIDAK diberi key 'unit_category'
    // karena Manajemen Pelanggan berlaku untuk KEDUA kategori Unit Usaha
    // (ritel maupun jasa), bukan cuma kategori 'jasa' seperti
    // 'Pesanan Layanan' di grup "Manajemen Layanan" di bawah. Sebelum
    // perbaikan ini, route-nya sudah ada & bisa diakses lewat URL langsung
    // tapi tidak muncul di sidebar sama sekali.
    //
    // PENTING soal 'Pembelian' (unit.purchasing.index): item ini
    // DITAMBAHKAN di grup "Operasional Unit" di bawah, persis setelah
    // 'Inventaris' -- mengikuti urutan route di routes/web.php (path
    // '/pembelian' didaftarkan tepat setelah 'inventory.index', sebelum
    // 'assets.index'). Modul ini mencatat belanja ke Vendor & Supplier
    // (App\Models\Vendor) dan otomatis membuat FinanceTransaction +
    // StockMovement sekaligus -- lihat App\Livewire\Unit\Purchasing\Index.
    // TIDAK diberi key 'unit_category': berlaku untuk KEDUA kategori Unit
    // Usaha, sama seperti 'Pelanggan' & 'Statistik Usaha' di atas (bukan
    // cuma kategori 'ritel' seperti 'Inventaris' di bawah, karena unit
    // kategori 'jasa' juga tetap bisa berbelanja bahan/alat dari vendor
    // walau tidak mengelola stok produk untuk dijual).
    //
    // PENTING soal 'unit_category': item/grup yang punya key ini HANYA
    // ditampilkan kalau kategori unit yang SEDANG DIBUKA (bukan unit milik
    // user login -- lihat catatan $slugUnitAktif / $categoryUnitAktif di
    // components/layouts/unit.blade.php, penting untuk kasus Master Admin
    // memantau unit lain) sama dengan nilai key ini. Item TANPA key ini
    // selalu tampil untuk semua kategori unit (perilaku lama, tidak
    // berubah). Dipakai untuk grup "Manajemen Layanan" di bawah, yang
    // hanya relevan untuk unit berkategori 'jasa'.
    // =========================================================================

    [
        'label' => 'Unit Usaha Saya',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Dashboard', 'route' => 'unit.dashboard', 'roles' => ['unit-admin']],
            ['label' => 'Statistik Usaha', 'route' => 'unit.analytics.index', 'roles' => ['unit-admin']],
        ],
    ],

    [
        'label' => 'Operasional Unit',
        'roles' => ['unit-admin'],
        'children' => [
            ['label' => 'Transaksi', 'route' => 'unit.transactions.index', 'roles' => ['unit-admin']],
            ['label' => 'Transaksi Berulang', 'route' => 'unit.recurring-transactions.index', 'roles' => ['unit-admin']],
            ['label' => 'Inventaris', 'route' => 'unit.inventory.index', 'roles' => ['unit-admin'], 'unit_category' => 'ritel'],
            ['label' => 'Pembelian', 'route' => 'unit.purchasing.index', 'roles' => ['unit-admin']],
            ['label' => 'Aset Unit Usaha', 'route' => 'unit.assets.index', 'roles' => ['unit-admin']],
            ['label' => 'Pelanggan', 'route' => 'unit.customers.index', 'roles' => ['unit-admin']],
            ['label' => 'Dokumen Resmi', 'route' => 'unit.documents.index', 'roles' => ['unit-admin']],
            ['label' => 'Export Data', 'route' => 'unit.exports.index', 'roles' => ['unit-admin']],
        ],
    ],

    // ================= MANAJEMEN LAYANAN (UNIT-ADMIN, KHUSUS KATEGORI 'JASA') =================
    // Grup baru, HANYA muncul di sidebar saat unit yang sedang dibuka
    // berkategori 'jasa' (diatur lewat form Tambah/Edit Unit di Master >
    // Unit Usaha, lihat App\Livewire\Master\Units\Index::$category).
    // Untuk unit berkategori 'ritel', grup ini disembunyikan sepenuhnya
    // dan menu "Inventaris" di atas (yang sebaliknya butuh 'ritel') tetap
    // muncul seperti sebelumnya.
    [
        'label' => 'Manajemen Layanan',
        'roles' => ['unit-admin'],
        'unit_category' => 'jasa',
        'children' => [
            ['label' => 'Pesanan Layanan', 'route' => 'unit.service-orders.index', 'roles' => ['unit-admin'], 'unit_category' => 'jasa'],
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
