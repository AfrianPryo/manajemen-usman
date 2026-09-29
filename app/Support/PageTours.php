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
                        ['cta' => 'Kelola Template', 'href' => static::href('master.documents.templates')]),
                    static::step('[data-tour="docs-card-signature"]', 'Langkah 2: Siapkan tanda tangan',
                        'Isi nama, jabatan, dan gambar tanda tangan pejabat. Profil ini dipilih saat membuat dokumen.',
                        ['cta' => 'Atur Tanda Tangan', 'href' => static::href('master.documents.signature')]),
                    static::step('[data-tour="docs-card-generate"]', 'Langkah 3: Buat dokumen',
                        'Pilih jenis dokumen, template, dan penanda tangan, lengkapi datanya, lalu tekan Buat Dokumen. Nomor surat diberikan otomatis.',
                        ['cta' => 'Buat Dokumen', 'href' => static::href('master.documents.generate')]),
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
        ];
    }
}