<?php

/**
 * Materi halaman "Tutorial" (App\Livewire\Master\Tutorials\Index & Unit\Tutorials\Index).
 *
 * Susunan mengikuti kursus "Canva for Beginners": mulai dari mengenal tampilan,
 * lalu fitur satu per satu, ditutup dengan langkah berikutnya. Tiap materi
 * punya satu video, ringkasan, dan daftar "yang akan dipelajari".
 *
 * Kolom materi:
 *  - id         : unik & stabil (dipakai di URL ?materi= dan penanda progres).
 *  - title      : judul materi.
 *  - video      : link YouTube / Google Drive / file .mp4 (kosong = "Video segera hadir").
 *  - summary    : ringkasan satu-dua kalimat (opsional).
 *  - steps      : poin "Yang akan dipelajari" (opsional).
 *  - transcript : teks transkrip (opsional, tombolnya muncul bila diisi).
 *  - duration   : mis. '05:30' (opsional).
 *  - route      : nama route untuk tombol "Buka halaman terkait" (opsional;
 *                 route yang tidak ada otomatis disembunyikan).
 *  - categories : batasi materi ke kategori unit tertentu, mis. ['ritel'] (opsional).
 *
 * Kolom kelompok (section):
 *  - scope    : 'master' (hanya Master Admin) atau 'unit' (Admin Unit + Master saat
 *               memantau unit).
 *  - category : (opsional) kelompok hanya tampil untuk unit berkategori ini, mis. 'jasa'.
 *
 * Master Admin melihat SEMUA kelompok. Untuk mengganti video cukup ubah kolom 'video'.
 */
return [
    'sections' => [

        // ------------------------------------------------------------------
        [
            'id'      => 'master',
            'scope'   => 'master',
            'title'   => 'Admin Master',
            'summary' => 'Menyiapkan sistem dari awal.',
            'lessons' => [
                [
                    'id'      => 'master-mulai',
                    'title'   => 'Login Pertama dan Setup Nomer',
                    'video'   => 'https://youtu.be/-PwEk74hP7E',
                    'summary' => 'Proses Login dan ganti password, serta mengatur nomer whatsapp.',
                    'steps'   => [
                        'Login dengan Username atau NIP',
                        'Membuat password baru',
                        'Setup dan verifikasi nomor WhatsApp dengan kode OTP 6 digit',
                    ],
                    'route'   => 'master.dashboard',
                ],
                [
                    'id'      => 'master-dashboard',
                    'title'   => 'Mengenal Dashboard Master',
                    'video'   => 'https://youtu.be/PP25S0vcT-E',
                    'summary' => 'Kenali tampilan dashboard Master Admin dan cara membaca setiap informasi di dalamnya.',
                    'steps'   => [
                        'Mengenal kartu ringkasan',
                        'Grafik Kontribusi Omzet, Peringkat Omzet, dan Kesehatan Unit Usaha',
                        'Kelompok menu di sidebar',
                    ],
                    'route'   => 'master.dashboard',
                ],
                [
                    'id'      => 'master-tambah-unit',
                    'title'   => 'Cara Menambah Unit Usaha',
                    'video'   => 'https://youtu.be/-FwhOxxQPXI',
                    'summary' => 'Pelajari cara menambah unit usaha dengan benar sejak awal.',
                    'steps'   => [
                        'Mengisi data unit',
                        'Memilih kategori usaha',
                        'Menyimpan unit dan mengaktifkan/menonaktifkannya',
                    ],
                    'route'   => 'master.units.index',
                ],
                [
                    'id'      => 'master-tambah-admin',
                    'title'   => 'Cara Menambah Admin Unit',
                    'video'   => 'https://youtu.be/lXHoF-ZHoUc',
                    'summary' => 'Lihat cara membuat akun admin dan mengirim kredensialnya secara otomatis.',
                    'steps'   => [
                        'Mengisi data admin baru',
                        'Kredensial login dikirim otomatis ke WhatsApp admin',
                        'Mereset password admin',
                    ],
                    'route'   => 'master.users.index',
                ],
                [
                    'id'      => 'master-pengaturan-awal',
                    'title'   => 'Pengaturan Awal: WhatsApp (Fonnte) dan Tanda Tangan',
                    'video'   => 'https://youtu.be/uOqaatRYOAM',
                    'summary' => 'pengaturan penting yang sebaiknya disesuaikan di awal agar notifikasi dan dokumen resmi berjalan lancar.',
                    'steps'   => [
                        'Mengaktifkan Notifikasi WhatsApp',
                        'Mengisi nomor pengirim dan API key Fonnte',
                        'Mengatur nama, jabatan, dan gambar tanda tangan pejabat',
                    ],
                    'route'   => 'master.settings.index',
                ],
                [
                    'id'      => 'master-dokumen',
                    'title'   => 'Membuat Dokumen Resmi Otomatis',
                    'video'   => 'https://youtu.be/NaNJneMsMv0',
                    'summary' => 'Buat dokumen resmi bernomor surat dalam empat langkah.',
                    'steps'   => [
                        'Mengunggah template Word berkop surat di Kelola Template',
                        'Menyiapkan tanda tangan penandatangan',
                        'Membuat dokumen dan mengunduh hasilnya',
                    ],
                    'route'   => 'master.documents.index',
                ],
                [
                    'id'      => 'master-vendor-pelanggan',
                    'title'   => 'Mengelola Vendor dan Pelanggan',
                    'video'   => 'https://youtu.be/HaRCYSIWhR8',
                    'summary' => 'Kelola data vendor dan pelanggan lintas unit dari sisi Master Admin.',
                    'steps'   => [
                        'Menyimpan data vendor',
                        'Melihat dan mengelola data pelanggan',
                        'Kegunaan data ini untuk pembelian dan pesanan',
                    ],
                    'route'   => 'master.vendors.index',
                ],
                [
                    'id'      => 'master-operasional',
                    'title'   => 'Memantau Operasional Seluruh Unit',
                    'video'   => 'https://youtu.be/R5MwuuX-BPo',
                    'summary' => 'Pantau transaksi, stok, dan pesanan dari semua unit dalam satu tempat.',
                    'steps'   => [
                        'Mencatat transaksi dan Import Transaksi Massal lewat template Excel',
                        'Mengatur Transaksi Berulang untuk biaya rutin',
                        'Memantau Inventaris, Pesanan Layanan, dan rekap Pembelian Lintas-Unit',
                    ],
                    'route'   => 'master.transactions.index',
                ],
                [
                    'id'      => 'master-statistik-export',
                    'title'   => 'Statistik Usaha dan Export Data',
                    'video'   => 'https://youtu.be/FOWdvgXnnhk',
                    'summary' => 'Bandingkan kinerja antarunit dan unduh laporan dalam format Excel.',
                    'steps'   => [
                        'Membaca metrik, grafik, dan tabel di Statistik Usaha',
                        'Memilih jenis data dan filter di Export Data',
                        'Mengunduh laporan ke Excel',
                    ],
                    'route'   => 'master.analytics.index',
                ],
                [
                    'id'      => 'master-keamanan',
                    'title'   => 'Keamanan, Log Aktivitas, dan Pengumuman',
                    'video'   => 'https://youtu.be/fZX6gdzv7E8',
                    'summary' => 'Tiga alat di grup System untuk menjaga dan mengarahkan seluruh sistem.',
                    'steps'   => [
                        'Memantau aktivitas login dan memblokir IP/perangkat mencurigakan',
                        'Menelusuri Log Aktivitas seluruh unit dengan pencarian dan filter',
                        'Mengirim pengumuman ke admin unit',
                    ],
                    'route'   => 'master.activities.index',
                ],
            ],
        ],
    ],
        // ------------------------------------------------------------------
        // SECTION UNIT (scope: 'unit') -- BUG-05 fix: sebelumnya tidak ada
        // section ber-scope 'unit' sehingga halaman Tutorial Unit Admin
        // selalu kosong (Unit\Tutorials\Index::sectionAllowed() menolak
        // semua section yang bukan scope 'unit').
        // ------------------------------------------------------------------
        [
            'id'      => 'unit-dasar',
            'scope'   => 'unit',
            'title'   => 'Admin Unit — Dasar',
            'summary' => 'Mengelola unit usaha sehari-hari.',
            'lessons' => [
                [
                    'id'      => 'unit-mulai',
                    'title'   => 'Login Pertama dan Setup Nomor',
                    'video'   => 'https://youtu.be/-PwEk74hP7E',
                    'summary' => 'Proses login dan ganti password, serta mengatur nomor WhatsApp.',
                    'steps'   => [
                        'Login dengan Username atau NIP',
                        'Membuat password baru',
                        'Setup dan verifikasi nomor WhatsApp dengan kode OTP 6 digit',
                    ],
                    'route'   => 'unit.dashboard',
                ],
                [
                    'id'      => 'unit-dashboard',
                    'title'   => 'Mengenal Dashboard Unit',
                    'video'   => 'https://youtu.be/PP25S0vcT-E',
                    'summary' => 'Kenali tampilan dashboard Unit Admin dan cara membaca ringkasan omzet, stok, dan notifikasi.',
                    'steps'   => [
                        'Kartu ringkasan omzet, transaksi, dan stok',
                        'Grafik performa unit',
                        'Kelompok menu di sidebar',
                    ],
                    'route'   => 'unit.dashboard',
                ],
                [
                    'id'      => 'unit-transaksi',
                    'title'   => 'Mencatat Transaksi Keuangan',
                    'video'   => 'https://youtu.be/R5MwuuX-BPo',
                    'summary' => 'Pelajari cara mencatat pemasukan dan pengeluaran unit dengan benar.',
                    'steps'   => [
                        'Menambah transaksi pemasukan dan pengeluaran',
                        'Memilih kategori transaksi',
                        'Melihat riwayat dan rekap transaksi',
                    ],
                    'route'   => 'unit.transactions.index',
                ],
                [
                    'id'      => 'unit-inventaris',
                    'title'   => 'Mengelola Inventaris (Stok Produk)',
                    'video'   => '',
                    'summary' => 'Kelola stok produk unit agar selalu akurat dan mudah dipantau.',
                    'steps'   => [
                        'Menambah dan mengedit produk',
                        'Memantau stok dan notifikasi stok rendah',
                        'Mencatat penyesuaian stok manual',
                    ],
                    'route'   => 'unit.inventory.index',
                ],
                [
                    'id'      => 'unit-pembelian',
                    'title'   => 'Mencatat Pembelian dari Vendor',
                    'video'   => '',
                    'summary' => 'Catat pembelian barang dari vendor agar stok dan keuangan otomatis terupdate.',
                    'steps'   => [
                        'Memilih vendor dan mengisi item pembelian',
                        'Stok produk otomatis bertambah setelah pembelian disimpan',
                        'Melihat riwayat pembelian',
                    ],
                    'route'   => 'unit.purchasing.index',
                ],
                [
                    'id'      => 'unit-pelanggan',
                    'title'   => 'Mengelola Data Pelanggan',
                    'video'   => '',
                    'summary' => 'Simpan dan kelola data pelanggan setia unit usaha.',
                    'steps'   => [
                        'Menambah dan mengedit data pelanggan',
                        'Melihat riwayat transaksi pelanggan',
                    ],
                    'route'   => 'unit.customers.index',
                ],
                [
                    'id'      => 'unit-dokumen',
                    'title'   => 'Membuat Dokumen Resmi',
                    'video'   => 'https://youtu.be/NaNJneMsMv0',
                    'summary' => 'Buat surat resmi bernomor otomatis dalam beberapa langkah.',
                    'steps'   => [
                        'Memilih template dokumen',
                        'Mengisi data dan mengunduh hasil dokumen',
                        'Melihat riwayat dokumen yang pernah dibuat',
                    ],
                    'route'   => 'unit.documents.index',
                ],
                [
                    'id'      => 'unit-statistik',
                    'title'   => 'Statistik dan Export Data',
                    'video'   => 'https://youtu.be/FOWdvgXnnhk',
                    'summary' => 'Baca laporan kinerja unit dan unduh data ke Excel.',
                    'steps'   => [
                        'Membaca grafik dan tabel di Statistik Usaha',
                        'Memilih jenis data dan filter di Export Data',
                        'Mengunduh laporan ke Excel',
                    ],
                    'route'   => 'unit.analytics.index',
                ],
            ],
        ],

        // ------------------------------------------------------------------
        // Section khusus unit kategori 'jasa' (Bengkel dll.)
        // ------------------------------------------------------------------
        [
            'id'       => 'unit-jasa',
            'scope'    => 'unit',
            'category' => 'jasa',
            'title'    => 'Fitur Khusus Unit Jasa',
            'summary'  => 'Fitur tambahan untuk unit kategori jasa (mis. bengkel).',
            'lessons'  => [
                [
                    'id'         => 'unit-pesanan',
                    'title'      => 'Mengelola Pesanan Layanan',
                    'video'      => '',
                    'summary'    => 'Kelola antrian pesanan layanan dari pelanggan: catat, proses, dan selesaikan.',
                    'steps'      => [
                        'Membuat pesanan layanan baru',
                        'Memperbarui status pesanan',
                        'Melihat riwayat pesanan selesai',
                    ],
                    'categories' => ['jasa'],
                    'route'      => 'unit.service-orders.index',
                ],
            ],
        ],

];