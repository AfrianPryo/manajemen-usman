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
                    'title'   => 'Membuka USMAN untuk pertama kali',
                    'video'   => 'https://youtu.be/-PwEk74hP7E',
                    'summary' => 'Kenali tampilan dashboard Master, menu di sidebar, dan notifikasi.',
                    'steps'   => [
                        'Masuk dan mengenali area dashboard',
                        'Memahami menu sidebar dan tombol ciutkan menu',
                        'Memakai tombol Panduan untuk memutar ulang tur halaman',
                    ],
                    'route'   => 'master.dashboard',
                ],
                [
                    'id'      => 'master-unit-admin',
                    'title'   => 'Menambah Unit Usaha dan Admin',
                    'video'   => 'https://youtu.be/PP25S0vcT-E',
                    'summary' => 'Buat unit usaha, lalu daftarkan admin yang mengelolanya.',
                    'steps'   => [
                        'Menambah unit dan memilih kategorinya (ritel / jasa)',
                        'Membuat akun Admin Unit',
                        'Menonaktifkan atau mengatur ulang akun',
                    ],
                    'route'   => 'master.users.index',
                ],
                [
                    'id'      => 'master-pengaturan',
                    'title'   => 'Pengaturan sistem dan profil',
                    'video'   => 'https://youtu.be/-FwhOxxQPXI',
                    'summary' => 'Atur nama aplikasi, logo, dan data profil agar sesuai sekolah Anda.',
                    'steps'   => [
                        'Mengubah nama dan logo aplikasi',
                        'Memperbarui foto dan data profil',
                        'Mencari pengaturan yang dibutuhkan dengan cepat',
                    ],
                    'route'   => 'master.settings.index',
                ],
                [
                    'id'      => 'master-dokumen',
                    'title'   => 'Dokumen resmi dan template',
                    'video'   => 'https://youtu.be/lXHoF-ZHoUc',
                    'summary' => 'Pakai template untuk membuat surat dan laporan resmi.',
                    'steps'   => [
                        'Memilih jenis dokumen dan template',
                        'Mengatur tanda tangan',
                        'Membuka riwayat dokumen yang pernah dibuat',
                    ],
                    'route'   => 'master.documents.index',
                ],
            ],
        ],

        // ------------------------------------------------------------------
        [
            'id'      => 'unit',
            'scope'   => 'unit',
            'title'   => 'Admin Unit',
            'summary' => 'Mengelola operasional harian unit usaha.',
            'lessons' => [
                [
                    'id'      => 'unit-dashboard',
                    'title'   => 'Mengenal dashboard unit',
                    'video'   => 'https://youtu.be/uOqaatRYOAM',
                    'summary' => 'Baca ringkasan penjualan, stok, dan aktivitas unit Anda.',
                    'steps'   => [
                        'Membaca kartu ringkasan',
                        'Memahami peringatan stok',
                        'Berpindah ke menu lain dari dashboard',
                    ],
                    'route'   => 'unit.dashboard',
                ],
                [
                    'id'         => 'unit-inventaris',
                    'title'      => 'Mengelola inventaris dan produk',
                    'video'      => 'https://youtu.be/NaNJneMsMv0',
                    'summary'    => 'Tambah produk, atur stok, dan pantau pergerakannya.',
                    'steps'      => [
                        'Menambah dan mengubah produk',
                        'Mengatur stok masuk dan keluar',
                        'Mengimpor banyak produk sekaligus',
                    ],
                    'categories' => ['ritel'],
                    'route'      => 'unit.inventory.index',
                ],
                [
                    'id'      => 'unit-transaksi',
                    'title'   => 'Mencatat transaksi',
                    'video'   => 'https://youtu.be/HaRCYSIWhR8',
                    'summary' => 'Catat pemasukan dan pengeluaran, termasuk transaksi berulang.',
                    'steps'   => [
                        'Menambah transaksi baru',
                        'Memakai transaksi berulang',
                        'Mencari dan memfilter transaksi',
                    ],
                    'route'   => 'unit.transactions.index',
                ],
                [
                    'id'      => 'unit-ekspor',
                    'title'   => 'Ekspor, impor, dan laporan',
                    'video'   => 'https://youtu.be/R5MwuuX-BPo',
                    'summary' => 'Unduh laporan, siapkan data dengan template, lalu simpan rapi.',
                    'steps'   => [
                        'Mengekspor laporan ke Excel',
                        'Mengisi dan mengunggah template impor',
                        'Menyimpan dan mengatur berkas laporan',
                    ],
                    'route'   => 'unit.exports.index',
                ],
                [
                    'id'      => 'unit-selanjutnya',
                    'title'   => 'Langkah berikutnya',
                    'video'   => 'https://youtu.be/FOWdvgXnnhk',
                    'summary' => 'Ringkasan dan hal yang bisa dipelajari lebih lanjut.',
                    'steps'   => [
                        'Mengulang materi kapan saja lewat halaman ini',
                        'Memutar ulang tur per halaman dengan tombol Panduan',
                    ],
                ],
            ],
        ],

        // ------------------------------------------------------------------
        [
            'id'       => 'jasa',
            'scope'    => 'unit',
            'category' => 'jasa',
            'title'    => 'Tambahan unit Jasa',
            'summary'  => 'Khusus unit berkategori jasa.',
            'lessons'  => [
                [
                    'id'      => 'jasa-service-order',
                    'title'   => 'Mengelola service order',
                    'video'   => 'https://youtu.be/fZX6gdzv7E8',
                    'summary' => 'Terima pesanan jasa, perbarui statusnya, dan tutup pesanan.',
                    'steps'   => [
                        'Membuat service order',
                        'Memperbarui status pengerjaan',
                        'Menyelesaikan dan mencatat pembayaran',
                    ],
                    'route'   => 'unit.service-orders.index',
                ],
            ],
        ],
    ],
];