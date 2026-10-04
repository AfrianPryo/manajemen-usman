<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Registry tutorial kontekstual per halaman/aksi (di luar tutorial setup awal
 * Dashboard Master Admin). Dipasang lewat <livewire:page-tour tour="..." />.
 *
 * Tiap langkah:
 *  - target : selector CSS elemen asli yang disorot (null / tidak ketemu /
 *             tidak terlihat => kartu tampil di tengah layar)
 *  - side   : 'bottom' (default) atau 'right'
 *  - title, body : teks petunjuk
 *  - cta + href  : tombol tautan ke halaman lain (opsional)
 *  - cta + click : tombol yang meng-klik elemen target (opsional)
 *
 * Selector dibuat dari atribut wire:* / id yang sudah ada, jadi view tidak perlu
 * diberi data-tour satu per satu, dan tetap jalan di layout Master maupun Unit
 * (view Dokumen Resmi dipakai bersama).
 */
class PageTours
{
    public static function has(string $key): bool
    {
        return array_key_exists($key, static::definitions());
    }

    public static function steps(string $key): array
    {
        return static::definitions()[$key]['steps'] ?? [];
    }

    public static function title(string $key): string
    {
        return static::definitions()[$key]['title'] ?? 'Panduan';
    }

    // ---- helper selector ----

    private static function model(string $name, bool $live = false): string
    {
        return '[wire\\:model' . ($live ? '\\.live' : '') . '="' . $name . '"]';
    }

    private static function click(string $call): string
    {
        return '[wire\\:click="' . $call . '"]';
    }

    /** Selector tautan <a> berdasarkan path route (null bila route tidak ada). */
    private static function link(string $routeName): ?string
    {
        if (! Route::has($routeName)) {
            return null;
        }

        return 'a[href$="' . parse_url(route($routeName), PHP_URL_PATH) . '"]';
    }

    private static function href(string $routeName): ?string
    {
        return Route::has($routeName) ? route($routeName) : null;
    }

    /**
     * Tautan sub-halaman Dokumen Resmi yang sadar konteks: di halaman Unit
     * (route unit.*) mengarah ke unit.documents.{page} lengkap dengan slug
     * unit yang sedang dibuka; selain itu tetap ke master.documents.{page}.
     * Mengembalikan null bila route-nya tidak ada (mis. Kelola Template
     * yang memang hanya milik Master), sama seperti href().
     */
    private static function docsHref(string $page): ?string
    {
        if (request()->routeIs('unit.*')) {
            $unit = request()->route('unit');
            $slug = is_object($unit) ? ($unit->slug ?? null) : (is_string($unit) ? $unit : null);
            $name = 'unit.documents.' . $page;

            return ($slug && Route::has($name)) ? route($name, ['unit' => $slug]) : null;
        }

        return static::href('master.documents.' . $page);
    }

    /** Tautan halaman Tutorial yang sadar konteks (unit.* butuh slug unit yang sedang dibuka). */
    private static function tutorialHref(): ?string
    {
        if (request()->routeIs('unit.*')) {
            $unit = request()->route('unit');
            $slug = is_object($unit) ? ($unit->slug ?? null) : (is_string($unit) ? $unit : null);

            return ($slug && Route::has('unit.tutorials.index')) ? route('unit.tutorials.index', ['unit' => $slug]) : null;
        }

        return static::href('master.tutorials.index');
    }

    /**
     * Kolom pencarian umum: atribut wire:model-nya bervariasi antar halaman
     * (debounce 300ms / 400ms), jadi dicocokkan lewat daftar selector.
     */
    private static function searchBox(): string
    {
        return '[wire\\:model\\.live\\.debounce\\.300ms="search"], [wire\\:model\\.live\\.debounce\\.400ms="search"]';
    }

    private static function step(?string $target, string $title, string $body, array $extra = []): array
    {
        return array_filter(array_merge([
            'target' => $target,
            'side'   => 'bottom',
            'title'  => $title,
            'body'   => $body,
        ], $extra), fn ($v) => $v !== null);
    }

    public static function definitions(): array
    {
        return [

            // =================== DOKUMEN RESMI ===================

            'documents.hub' => [
                'title' => 'Panduan Dokumen Resmi',
                'steps' => [
                    static::step('[data-tour="docs-flow"]', 'Alur membuat dokumen resmi',
                        'Dokumen dibuat dari tiga bahan: template (kop surat), profil tanda tangan, dan data singkat yang Anda isi. Siapkan template dan tanda tangan sekali saja, setelah itu membuat dokumen tinggal beberapa klik.'),
                    static::step('[data-tour="docs-card-templates"]', 'Langkah 1: Siapkan template',
                        'Unggah kop surat (.docx) untuk tiap jenis dokumen. Isi surat dibuat otomatis oleh sistem, jadi file Word cukup berisi kop dan pengaturan halaman.',
                        ['cta' => 'Kelola Template', 'href' => static::docsHref('templates')]),
                    static::step('[data-tour="docs-card-signature"]', 'Langkah 2: Siapkan tanda tangan',
                        'Isi nama, jabatan, dan gambar tanda tangan pejabat. Profil ini dipilih saat membuat dokumen.',
                        ['cta' => 'Atur Tanda Tangan', 'href' => static::docsHref('signature')]),
                    static::step('[data-tour="docs-card-generate"]', 'Langkah 3: Buat dokumen',
                        'Pilih jenis dokumen, template, dan penanda tangan, lengkapi datanya, lalu tekan Buat Dokumen. Nomor surat diberikan otomatis.',
                        ['cta' => 'Buat Dokumen', 'href' => static::docsHref('generate')]),
                    static::step('[data-tour="docs-card-history"]', 'Langkah 4: Unduh dan arsip',
                        'Setiap dokumen yang dibuat tercatat di Riwayat Dokumen beserta nomor suratnya, dan bisa diunduh kembali kapan saja.'),
                ],
            ],

            'documents.templates' => [
                'title' => 'Panduan Template Dokumen',
                'steps' => [
                    static::step(static::click('create'), 'Tambah template pertama',
                        'Template adalah file kop surat (.docx) untuk satu jenis dokumen. Klik tombol ini untuk membuka formulir template baru.',
                        ['cta' => 'Buka Formulir', 'click' => true]),
                    static::step('table', 'Daftar template',
                        'Hanya template berstatus Aktif yang muncul di halaman Buat Dokumen. Anda bisa mengedit, menonaktifkan, atau menghapus template. Template yang sudah dipakai membuat dokumen tidak bisa dihapus, nonaktifkan saja.'),
                ],
            ],

            'documents.template-form' => [
                'title' => 'Panduan Form Template',
                'steps' => [
                    static::step(static::model('type', true), 'Pilih jenis dokumen',
                        'Jenis dokumen menentukan data apa yang otomatis masuk ke isi surat. Setelah dipilih, muncul kotak informasi berisi daftar datanya.'),
                    static::step(static::model('name'), 'Beri nama template',
                        'Pakai nama yang mudah dikenali, misalnya "Laporan Keuangan Bulanan - Format A", karena nama ini yang Anda pilih saat membuat dokumen.'),
                    static::step('input[type="file"][wire\\:model="templateFile"]', 'Unggah kop surat (.docx)',
                        'File hanya perlu berisi kop surat (header/footer seperti logo, alamat, kontak) dan pengaturan halaman (margin, ukuran kertas). Kosongkan bagian isi dan jangan taruh placeholder apa pun, karena isi surat ditulis otomatis oleh sistem.'),
                    static::step(static::model('numbering_format'), 'Atur format nomor surat',
                        'Susun teks dan token sesuai format nomor surat Anda. Contoh: {nomor}/UND/{bulan_romawi}/{tahun} menghasilkan 005/UND/VII/2026. Token yang tersedia tertulis di bawah kolom ini.'),
                    static::step(static::model('numbering_reset'), 'Kapan nomor kembali ke 1?',
                        'Pilih Setiap Tahun, Setiap Bulan, atau Tidak Pernah. Ini menentukan kapan nomor urut mulai lagi dari 1.', ['side' => 'top']),
                    static::step(static::click('save'), 'Simpan template',
                        'Setelah tersimpan, template langsung aktif dan bisa dipilih di halaman Buat Dokumen.', ['side' => 'top']),
                ],
            ],

            'documents.signature' => [
                'title' => 'Panduan Tanda Tangan',
                'steps' => [
                    static::step(static::model('name'), 'Nama dan jabatan penanda tangan',
                        'Isi nama yang akan tercetak dan jabatannya (misalnya Kepala TEFA). Keduanya muncul di blok tanda tangan pada dokumen.'),
                    static::step('input[type="file"][wire\\:model="signatureImage"]', 'Unggah gambar tanda tangan',
                        'Gunakan gambar PNG dengan latar transparan agar rapi saat ditempel ke dokumen. Ukuran maksimal 1 MB.'),
                    static::step('label:has(input[wire\\:model="is_default"])', 'Jadikan default',
                        'Kalau diaktifkan, profil ini otomatis terpilih setiap kali Anda membuat dokumen baru, dan masih bisa diganti saat itu juga.'),
                    static::step(static::click('save'), 'Simpan profil',
                        'Profil yang tersimpan muncul di daftar bawah dan bisa dipilih di halaman Buat Dokumen.', ['side' => 'top']),
                ],
            ],

            'documents.generate' => [
                'title' => 'Panduan Buat Dokumen',
                'steps' => [
                    static::step('#type', 'Pilih jenis dokumen',
                        'Jenis dokumen menentukan template yang tersedia dan isian khusus yang muncul di bagian bawah halaman.'),
                    static::step('#templateId', 'Pilih template',
                        'Kolom ini aktif setelah jenis dokumen dipilih. Kalau daftarnya kosong, buat dulu template lewat menu Kelola Template.'),
                    static::step('#signatureId', 'Pilih penanda tangan',
                        'Profil pejabat yang tanda tangannya dibubuhkan di dokumen. Kalau belum ada, buat dulu lewat menu Tanda Tangan.'),
                    static::step('#unit_id', 'Pilih unit',
                        'Unit usaha yang datanya dipakai untuk dokumen ini, misalnya pada laporan.'),
                    static::step('#title', 'Lengkapi detail surat',
                        'Isi judul/perihal dan penerima. Tanggal mulai dan selesai dipakai untuk dokumen berbasis periode seperti laporan keuangan. Beberapa jenis dokumen punya isian tambahan di bawahnya.'),
                    static::step('button[type="submit"][wire\\:target="generate"]', 'Buat dan unduh',
                        'Tekan Buat Dokumen. Setelah berhasil, tombol Unduh Dokumen muncul, dan dokumen tercatat di Riwayat Dokumen.', ['side' => 'top']),
                ],
            ],

            // =================== TRANSAKSI ===================

            'transactions.index' => [
                'title' => 'Panduan Transaksi',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Catat transaksi baru',
                        'Gunakan tombol ini untuk mencatat pemasukan atau pengeluaran satu per satu.',
                        ['cta' => 'Buka Formulir', 'click' => true]),
                    static::step(static::click('openImportModal'), 'Import banyak transaksi',
                        'Punya banyak data sekaligus? Import dari file Excel. Unduh template-nya di jendela import supaya formatnya sesuai.'),
                    static::step(static::click('openCategoryModal'), 'Kelola kategori',
                        'Kategori dipakai saat mencatat transaksi dan pada laporan. Tambahkan dulu kategori yang Anda butuhkan.'),
                ],
            ],

            'transactions.form' => [
                'title' => 'Panduan Form Transaksi',
                'steps' => [
                    // Sorot KEDUA tombol tipe (Pemasukan + Pengeluaran) lewat pembungkusnya, bukan hanya salah satunya.
                    static::step('div:has(> ' . static::click('$set(\'form_type\', \'income\')') . ')', 'Pilih tipe transaksi',
                        'Pemasukan untuk uang masuk, Pengeluaran untuk uang keluar. Daftar kategori menyesuaikan tipe yang dipilih.'),
                    static::step(static::model('form_unit_id', true), 'Pilih unit usaha',
                        'Transaksi dicatat atas nama unit ini. Kategori yang tampil ikut menyesuaikan unit.'),
                    static::step(static::model('form_finance_category_id'), 'Pilih kategori',
                        'Belum ada kategori yang cocok? Pakai tautan Tambah Kategori di atas kolom ini.'),
                    static::step(static::model('form_amount'), 'Isi nominal dan tanggal',
                        'Masukkan jumlah dalam rupiah dan tanggal transaksi.'),
                    static::step('form[wire\\:submit\\.prevent="saveTransaction"] .border-b', 'Lanjut ke Pembayaran & Bukti',
                        'Tab kedua berisi metode pembayaran, status, catatan, dan unggah bukti transaksi. Hanya transaksi berstatus Selesai yang dihitung ke total pemasukan/pengeluaran. Tekan Lanjut untuk pindah tab, lalu Simpan.'),
                ],
            ],

            // =================== INVENTORI ===================

            'inventory.index' => [
                'title' => 'Panduan Inventori',
                'steps' => [
                    static::step(static::click('openCategoryModal'), 'Siapkan kategori dulu',
                        'Kategori mengelompokkan produk. Buat kategori sebelum menambah produk supaya produk langsung masuk kelompok yang benar.'),
                    static::step(static::click('openCreateModal'), 'Tambah produk',
                        'Daftarkan produk satu per satu: kode, nama, harga, dan stok awal.',
                        ['cta' => 'Buka Formulir', 'click' => true]),
                    static::step(static::click('openImportModal'), 'Import banyak produk',
                        'Untuk banyak produk sekaligus, import dari Excel memakai template dari sistem.'),
                    static::step('[wire\\:click^="openStockModal"]', 'Restock atau sesuaikan stok',
                        'Tombol di baris produk ini dipakai untuk menambah stok saat barang datang atau menyesuaikan stok hasil hitung fisik.'),
                ],
            ],

            'inventory.form' => [
                'title' => 'Panduan Form Produk',
                'steps' => [
                    static::step(static::model('form_unit_id', true), 'Unit pemilik produk',
                        'Produk ini akan dikelola di unit yang dipilih.'),
                    static::step(static::model('form_name'), 'Nama dan kode produk',
                        'Beri nama yang jelas dan kode unik agar produk mudah dicari.'),
                    static::step(static::model('form_purchase_price'), 'Harga beli dan harga jual',
                        'Harga beli adalah modal, harga jual adalah harga ke pelanggan. Keduanya dipakai untuk menghitung keuntungan.'),
                    static::step(static::model('form_stock'), 'Stok awal dan stok minimum',
                        'Stok awal adalah jumlah saat ini. Stok minimum memicu peringatan saat persediaan menipis.'),
                ],
            ],

            // =================== PEMBELIAN ===================

            'purchasing.index' => [
                'title' => 'Panduan Pembelian',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Catat pembelian ke vendor',
                        'Gunakan tombol ini setiap kali unit berbelanja ke vendor. Pastikan vendor sudah terdaftar di menu Vendor.',
                        ['cta' => 'Buka Formulir', 'click' => true]),
                ],
            ],

            'purchasing.form' => [
                'title' => 'Panduan Form Pembelian',
                'steps' => [
                    static::step(static::model('unit_id', true), 'Pilih unit',
                        'Unit yang melakukan pembelian.'),
                    static::step(static::model('vendor_id'), 'Pilih vendor',
                        'Vendor tempat Anda berbelanja. Belum terdaftar? Tambahkan dulu di menu Vendor.'),
                    static::step(static::click('addItemRow'), 'Tambahkan barang',
                        'Tiap baris adalah satu barang yang dibeli. Tekan tombol ini untuk menambah baris.', ['side' => 'top']),
                    static::step(static::model('payment_method'), 'Metode pembayaran dan catatan',
                        'Pilih cara pembayaran dan tambahkan catatan bila perlu, lalu simpan.'),
                ],
            ],

            // =================== TRANSAKSI BERULANG ===================

            'recurring.index' => [
                'title' => 'Panduan Transaksi Berulang',
                'steps' => [
                    static::step(static::click('openModal'), 'Otomatiskan transaksi rutin',
                        'Untuk pemasukan atau pengeluaran yang terjadi berkala (misalnya gaji atau sewa), buat jadwalnya sekali. Sistem mencatat transaksinya sesuai jadwal.',
                        ['cta' => 'Buka Formulir', 'click' => true]),
                ],
            ],

            'recurring.form' => [
                'title' => 'Panduan Form Transaksi Berulang',
                'steps' => [
                    static::step(static::model('title'), 'Judul transaksi',
                        'Nama yang mudah dikenali, misalnya "Sewa gedung bulanan".'),
                    static::step(static::model('type', true), 'Tipe dan unit',
                        'Tentukan apakah pemasukan atau pengeluaran, dan unit yang bersangkutan.'),
                    static::step(static::model('amount'), 'Nominal dan kategori',
                        'Jumlah yang dicatat setiap periode, beserta kategorinya.'),
                    static::step(static::model('frequency'), 'Frekuensi dan periode',
                        'Atur seberapa sering transaksi dicatat, serta tanggal mulai dan berakhirnya.'),
                    // Toggle: input aslinya sr-only (1px, tak terlihat), jadi sorot <label> pembungkusnya.
                    static::step('label:has(' . static::model('auto_approve') . ')', 'Persetujuan otomatis',
                        'Kalau diaktifkan, transaksi langsung berstatus final saat dicatat sistem, tanpa menunggu tindakan Anda.'),
                ],
            ],

            // =================== MENU DASHBOARD (MASTER & UNIT) ===================
            // Tutorial tingkat-menu: tampil otomatis SEKALI saat akun pertama kali
            // membuka menunya (lihat App\Livewire\PageTour), bisa diputar ulang manual.

            'units.index' => [
                'title' => 'Panduan Unit Usaha',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Tambah Unit Usaha',
                        'Daftarkan unit usaha baru (nama, kategori, dan detailnya). Unit harus ada dulu sebelum Admin Unit, transaksi, atau produk bisa dibuat untuknya.'),
                    static::step(static::searchBox(), 'Cari dan saring unit',
                        'Cari unit berdasarkan nama, lalu saring menurut kategori atau status lewat pilihan di sebelahnya.'),
                    static::step('[wire\\:click^="toggleUnitStatus"]', 'Aktifkan atau nonaktifkan',
                        'Tombol di baris unit ini mengubah status aktif unit tanpa menghapus datanya. Unit yang nonaktif membuat admin unitnya tidak bisa masuk.'),
                    static::step('[wire\\:click^="openEditModal"]', 'Ubah data unit',
                        'Perbarui nama, kategori, logo, dan detail unit kapan saja lewat tombol edit di baris unit.'),
                ],
            ],

            'users.index' => [
                'title' => 'Panduan Manajemen Admin',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Tambah Admin Unit',
                        'Buat akun admin untuk sebuah unit usaha. Nomor WhatsApp aktif wajib diisi karena dipakai untuk notifikasi dan kredensial awal.'),
                    static::step(static::searchBox(), 'Cari admin',
                        'Temukan admin berdasarkan nama, username, atau email.'),
                    static::step('table', 'Daftar admin',
                        'Setiap baris menampilkan admin beserta unit dan statusnya. Admin yang dinonaktifkan tidak bisa masuk, tetapi datanya tetap tersimpan.'),
                    static::step('[wire\\:click^="toggleUserStatus"]', 'Aktifkan atau nonaktifkan akun',
                        'Gunakan tombol ini untuk menonaktifkan admin yang tidak lagi bertugas, atau mengaktifkannya kembali.'),
                ],
            ],

            'vendors.index' => [
                'title' => 'Panduan Vendor & Supplier',
                'steps' => [
                    static::step(static::click('openModal'), 'Tambah vendor',
                        'Daftarkan vendor atau supplier beserta kontaknya. Vendor yang sudah terdaftar bisa dipilih saat mencatat pembelian.'),
                    static::step(static::searchBox(), 'Cari vendor',
                        'Cari berdasarkan nama, kontak, atau email.'),
                    static::step(static::model('filterType', true), 'Saring vendor',
                        'Persempit daftar menurut tipe dan kategori vendor.'),
                    static::step('table', 'Daftar vendor',
                        'Lihat kontak dan detail tiap vendor, lalu ubah atau hapus dari kolom aksi di baris vendor.'),
                ],
            ],

            'customers.index' => [
                'title' => 'Panduan Pelanggan',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Tambah pelanggan',
                        'Catat pelanggan beserta kontak dan kategorinya supaya riwayatnya mudah dilacak.'),
                    static::step(static::searchBox(), 'Cari pelanggan',
                        'Cari berdasarkan nama, telepon, atau email.'),
                    static::step(static::model('categoryFilter', true), 'Saring per kategori',
                        'Tampilkan hanya pelanggan dari kategori tertentu.'),
                    static::step('[wire\\:click^="openEditModal"]', 'Ubah data pelanggan',
                        'Perbarui data pelanggan lewat tombol edit di barisnya.'),
                ],
            ],

            'unit.customers' => [
                'title' => 'Panduan Pelanggan',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Tambah pelanggan',
                        'Catat pelanggan unit Anda beserta kontak dan kategorinya.'),
                    static::step(static::searchBox(), 'Cari pelanggan',
                        'Cari berdasarkan nama, telepon, atau email.'),
                    static::step('[wire\\:click^="recordVisit"]', 'Catat kunjungan',
                        'Tekan tombol ini di baris pelanggan setiap kali ia berkunjung atau bertransaksi.'),
                    static::step('[wire\\:click^="openEditModal"]', 'Ubah data pelanggan',
                        'Perbarui data pelanggan lewat tombol edit di barisnya.'),
                ],
            ],

            'assets.index' => [
                'title' => 'Panduan Manajemen Aset',
                'steps' => [
                    static::step(static::click('openModal'), 'Tambah aset',
                        'Catat aset (peralatan, perangkat, dan sebagainya) beserta tag, kategori, unit, dan statusnya.'),
                    static::step(static::click('openImportModal'), 'Import banyak aset',
                        'Punya banyak aset sekaligus? Import dari Excel memakai template dari sistem.'),
                    static::step(static::click('exportData'), 'Export data aset',
                        'Unduh daftar aset sesuai penyaringan yang sedang aktif.'),
                    static::step(static::searchBox(), 'Cari aset',
                        'Cari berdasarkan tag aset, nama, nomor seri, atau pengguna. Saring lebih lanjut dengan status dan kategori.'),
                ],
            ],

            'exports.index' => [
                'title' => 'Panduan Export Data',
                'steps' => [
                    static::step('table', 'Pilih data yang diekspor',
                        'Tiap baris adalah satu jenis data (transaksi, produk, aset, dan lainnya) dengan tombol unduh sendiri.'),
                    static::step('[wire\\:click^="togglePanel"]', 'Atur filter',
                        'Buka panel filter di baris data untuk membatasi isi file, misalnya periode, unit, atau status.'),
                    static::step(static::click('bulkExport'), 'Export sekaligus',
                        'Centang beberapa jenis data, lalu unduh semuanya dalam satu file.'),
                ],
            ],

            'service-orders.index' => [
                'title' => 'Panduan Pesanan Layanan',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Catat pesanan layanan',
                        'Tambahkan pesanan baru: pelanggan, jenis layanan, petugas, harga, dan jadwalnya.'),
                    static::step(static::searchBox(), 'Cari pesanan',
                        'Cari berdasarkan pelanggan, layanan, atau petugas.'),
                    static::step(static::model('statusFilter', true), 'Saring per status',
                        'Pantau pesanan yang masih berjalan atau yang sudah selesai.'),
                    static::step('[wire\\:click^="openEditModal"]', 'Perbarui pesanan',
                        'Ubah detail atau status pesanan lewat tombol edit di barisnya.'),
                ],
            ],

            'analytics.master' => [
                'title' => 'Panduan Analytics',
                'steps' => [
                    static::step(static::model('selectedUnit', true), 'Pilih unit',
                        'Lihat statistik seluruh unit sekaligus, atau pilih satu unit untuk dianalisis.'),
                    static::step(static::model('periodFilter', true), 'Atur periode',
                        'Semua angka dan grafik mengikuti periode yang dipilih. Pilih rentang tanggal sendiri bila perlu.'),
                    static::step('[x-ref="chart"]', 'Baca grafik',
                        'Arahkan kursor ke grafik untuk melihat nilai tepatnya. Bagian bawah halaman membandingkan performa tiap unit usaha.'),
                ],
            ],

            'analytics.unit' => [
                'title' => 'Panduan Analytics',
                'steps' => [
                    static::step(static::model('periodFilter', true), 'Atur periode',
                        'Semua angka dan grafik mengikuti periode yang dipilih. Pilih rentang tanggal sendiri bila perlu.'),
                    static::step('[x-ref="chart"]', 'Baca grafik',
                        'Arahkan kursor ke grafik untuk melihat nilai tepatnya, mulai dari kontribusi pendapatan hingga tren arus kas.'),
                ],
            ],

            'activities.index' => [
                'title' => 'Panduan Aktivitas Login',
                'steps' => [
                    static::step('table', 'Pantau aktivitas akun',
                        'Halaman ini mencatat percobaan dan sesi login beserta alamat IP dan perangkatnya, berguna untuk mendeteksi akses yang mencurigakan.'),
                    static::step(static::searchBox(), 'Cari dan saring',
                        'Cari berdasarkan email, nama pengguna, atau alamat IP, lalu saring menurut jenis kejadian.'),
                    static::step(static::click('exportLog'), 'Export log',
                        'Unduh catatan aktivitas untuk arsip atau pemeriksaan lebih lanjut.'),
                ],
            ],

            'audit-logs.index' => [
                'title' => 'Panduan Audit Log',
                'steps' => [
                    static::step('#search_log', 'Cari catatan',
                        'Audit log merekam siapa mengubah apa di sistem. Cari berdasarkan kata kunci pada catatan.'),
                    static::step('#event_filter', 'Saring jenis kejadian',
                        'Tampilkan hanya jenis perubahan tertentu, misalnya pembuatan, perubahan, atau penghapusan data.'),
                    static::step('#start_date', 'Batasi tanggal',
                        'Isi tanggal mulai dan selesai untuk menyempitkan catatan ke periode tertentu.'),
                    static::step('[wire\\:click^="openDetail"]', 'Lihat detail',
                        'Buka detail satu catatan untuk melihat data sebelum dan sesudah perubahan.'),
                    static::step(static::click('exportLog'), 'Export audit log',
                        'Unduh catatan sesuai penyaringan yang sedang aktif.'),
                ],
            ],

            'log-archives.index' => [
                'title' => 'Panduan Arsip Log',
                'steps' => [
                    static::step('#retention_input', 'Masa simpan log',
                        'Atur berapa lama log aktif disimpan sebelum diarsipkan otomatis oleh sistem.'),
                    static::step('#type_filter', 'Saring arsip',
                        'Cari arsip berdasarkan jenis log dan tahunnya.'),
                    static::step('[wire\\:click^="download"]', 'Unduh arsip',
                        'Unduh arsip log lama kapan pun dibutuhkan.'),
                ],
            ],

            'announcements.index' => [
                'title' => 'Panduan Pengumuman',
                'steps' => [
                    static::step(static::click('openCreateModal'), 'Buat pengumuman',
                        'Tulis pengumuman untuk semua admin unit atau hanya admin tertentu. Pengumuman juga bisa dikirim lewat WhatsApp.'),
                    static::step('table', 'Riwayat pengumuman',
                        'Semua pengumuman yang pernah dikirim tercatat di sini.'),
                ],
            ],

            'notifications.index' => [
                'title' => 'Panduan Notifikasi',
                'steps' => [
                    static::step(static::model('badgeFilter', true), 'Saring notifikasi',
                        'Tampilkan notifikasi menurut jenisnya agar yang penting mudah ditemukan.'),
                    static::step('[wire\\:click^="markAsRead"], [wire\\:click^="markAsUnread"]', 'Tandai dibaca',
                        'Tandai satu notifikasi sebagai sudah dibaca, atau kembalikan menjadi belum dibaca.'),
                    static::step(static::click('markAllAsRead'), 'Tandai semua dibaca',
                        'Bersihkan seluruh notifikasi belum dibaca sekaligus.'),
                ],
            ],

            'settings.index' => [
                'title' => 'Panduan Pengaturan Sistem',
                'steps' => [
                    static::step(static::click('setTab(\'profile\')'), 'Profil Admin',
                        'Ubah nama, foto, nomor WhatsApp, dan password akun Anda. Foto dan nama tampil di sidebar.'),
                    static::step(static::click('setTab(\'features\')'), 'Fitur & Modul',
                        'Aktifkan atau matikan fitur sistem sesuai kebutuhan sekolah.'),
                    static::step(static::click('setTab(\'landing\')'), 'Landing Page',
                        'Atur tampilan halaman depan aplikasi.'),
                ],
            ],

            'profile.account' => [
                'title' => 'Panduan Profil Saya',
                'steps' => [
                    static::step(static::model('name'), 'Data akun',
                        'Perbarui nama, username, dan email akun Anda. Foto dan nama tampil di sidebar.'),
                    static::step(static::model('newPhone'), 'Nomor WhatsApp',
                        'Nomor ini dipakai untuk notifikasi. Mengubahnya memerlukan password dan kode OTP demi keamanan.'),
                    static::step(static::model('currentPassword'), 'Ganti password',
                        'Isi password lama dan password baru. Gunakan kombinasi yang sulit ditebak.'),
                ],
            ],

            'documents.history' => [
                'title' => 'Panduan Riwayat Dokumen',
                'steps' => [
                    static::step(static::searchBox(), 'Cari dokumen',
                        'Cari berdasarkan nomor surat, judul, atau penerima.'),
                    static::step(static::model('typeFilter', true), 'Saring jenis dokumen',
                        'Tampilkan hanya jenis dokumen tertentu.'),
                    static::step('[wire\\:click^="download"]', 'Unduh kembali',
                        'Setiap dokumen yang pernah dibuat bisa diunduh lagi dari sini.'),
                ],
            ],

            'unit.dashboard' => [
                'title' => 'Panduan Dashboard Unit',
                'steps' => [
                    static::step(static::model('periodFilter', true), 'Atur periode',
                        'Seluruh ringkasan dan grafik di dashboard mengikuti periode yang dipilih.'),
                    static::step('[x-ref="chart"]', 'Pantau tren',
                        'Grafik menampilkan perkembangan omzet unit Anda. Arahkan kursor untuk melihat nilai tepatnya.'),
                    static::step('[wire\\:model\\.live\\.debounce\\.400ms="searchTransaction"]', 'Transaksi terkini',
                        'Cari transaksi terbaru langsung dari dashboard. Catat transaksi baru lewat menu Transaksi di sidebar.'),
                    static::step(static::click('export'), 'Export dashboard',
                        'Unduh ringkasan dashboard sebagai file Excel.'),
                    static::step('[data-tour="tutorial-link"]', 'Butuh bantuan? Tonton tutorial',
                        'Video panduan singkat tersedia kapan saja lewat tombol Tutorial di pojok kanan atas. Mulai dari materi pertama agar tidak ada yang terlewat.',
                        ['cta' => 'Buka Tutorial', 'href' => static::tutorialHref()]),
                ],
            ],
        ];
    }
}