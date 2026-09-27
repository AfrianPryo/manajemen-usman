<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Teks bawaan tiap section landing page (halaman depan publik),
     * dipakai sebagai fallback SELAMA admin belum pernah menyimpan teks
     * kustom lewat tab "Landing Page" di menu Pengaturan (lihat
     * App\Livewire\Master\Settings\Index & resources/views/landing.blade.php).
     * Sengaja satu-satunya sumber teks bawaan (bukan ditulis ulang di dua
     * tempat) supaya form pengaturan & tampilan publik selalu konsisten
     * sebelum admin pernah menyentuh pengaturan ini.
     *
     * Baris ganda pada judul (mis. "Dipercaya oleh\nMitra ...") memang
     * disengaja mengikuti pemenggalan baris asli desain landing page --
     * dirender jadi <br> lewat helper nl2br(e(...)) di blade.
     */
    public const LANDING_DEFAULTS = [
        // Hero (halaman utama) -- judul besar paling atas landing page.
        // Section ini selalu tampil (tidak ada toggle enabled) karena
        // menjadi halaman pembuka; hanya isi teksnya yang bisa diubah
        // admin lewat tab Landing Page. Baris baru (\n) tampil sebagai
        // baris terpisah, sama seperti judul section lain -- lihat
        // resources/views/landing.blade.php bagian HERO SECTION.
        'landing_hero_title_top'    => "Kelola Unit\nUsaha Sekolah",
        'landing_hero_title_bottom' => "dalam Satu\nPortal.",
        'landing_hero_scroll_text'  => 'SCROLL',

        'landing_mitra_title'       => "Dipercaya oleh\nMitra Unit Usaha Sekolah.",
        'landing_mitra_description' => "Kolaborasi kami tidak berhenti di sistem. Kami bekerja bersama unit usaha, penyedia layanan, dan mitra sekolah untuk memastikan setiap transaksi tercatat rapi dan dapat dipertanggungjawabkan.",

        'landing_fitur_eyebrow' => 'Fitur',
        'landing_fitur_title'   => "Di Garis Depan\nPengelolaan Usaha Sekolah.",

        'landing_cara_kerja_title'       => 'Mulai Kelola Cerdas.',
        'landing_cara_kerja_description' => '6 langkah untuk mulai mengelola unit usaha sekolah. Geser atau scroll untuk melihat tiap langkah.',

        'landing_tentang_title'       => 'Built for Real-World Financial Systems',
        'landing_tentang_description' => 'Platform ini memungkinkan institusi untuk mengelola aset digital dalam lingkungan yang terstruktur dan patuh. Dari penerbitan aset hingga eksekusi, setiap komponen dirancang untuk terintegrasi secara mulus dengan sistem dan alur kerja keuangan yang sudah ada.',

        'landing_faq_title' => 'Ada pertanyaan? Cek hal sering ditanyakan.',

        'landing_footer_title' => "Hubungi\nAdmin Pusat SIMS",
    ];

    /**
     * Item bawaan untuk tiap section landing page yang isinya berupa DAFTAR
     * (bukan sekadar judul/deskripsi tunggal seperti LANDING_DEFAULTS di
     * atas) -- Fitur Unggulan, Cara Kerja, dan FAQ. Disimpan & dibaca
     * sebagai JSON string lewat Setting::get()/set() (pola yang sama
     * dengan 'report_routine_sections'), supaya admin bisa menambah,
     * menghapus, dan mengurutkan ulang tiap butir lewat tab "Landing Page"
     * tanpa menyentuh kode. Lihat App\Livewire\Master\Settings\Index
     * (tab "landing") & resources/views/landing.blade.php.
     *
     * 'icon' pada landing_cara_kerja_items merujuk ke salah satu kunci
     * ikon garis bawaan di landing.blade.php (lihat $__caraKerjaIcons) --
     * bukan markup SVG bebas, supaya tampilan tetap konsisten dengan
     * desain asli walau jumlah langkah berubah.
     */
    public const LANDING_LIST_DEFAULTS = [
        'landing_fitur_items' => [
            [
                'title'       => 'Transaksi & Inventaris Terpadu',
                'description' => 'Catat transaksi harian, transaksi berulang, pembelian ke vendor, hingga stok inventaris dalam satu sistem yang saling terhubung — setiap unit usaha, baik ritel maupun jasa, punya alur kerja yang sesuai kebutuhannya.',
            ],
            [
                'title'       => 'Statistik & Dokumen Resmi Lintas Unit',
                'description' => 'Pantau performa seluruh unit usaha lewat statistik dan analitik terpusat, lalu terbitkan dokumen resmi maupun ekspor data kapan saja tanpa perlu merekap manual satu per satu.',
            ],
            [
                'title'       => 'Multi Admin & Hak Akses',
                'description' => 'Kelola peran Master Admin dan Admin Unit dengan hak akses yang jelas untuk tiap unit usaha, menjaga keamanan data sekaligus memudahkan pembagian tanggung jawab operasional.',
            ],
            [
                'title'       => 'Audit Log & Aktivitas',
                'description' => 'Setiap aktivitas dan perubahan data tercatat rapi dalam audit log, sehingga jejak penggunaan sistem tetap terpantau dan informasi sekolah tetap aman.',
            ],
        ],

        'landing_cara_kerja_items' => [
            ['badge' => 'AKUN', 'icon' => 'akun', 'title' => 'Akun Dibuatkan Master Admin', 'description' => 'Master Admin membuat akun Admin Unit dan kredensial login dikirim otomatis lewat WhatsApp.'],
            ['badge' => 'SETUP', 'icon' => 'setup', 'title' => 'Login & Ganti Password', 'description' => 'Admin login pakai kredensial awal, lalu wajib ganti password sebelum bisa mengakses dashboard.'],
            ['badge' => 'UNIT USAHA', 'icon' => 'unit', 'title' => 'Atur Unit Usaha', 'description' => 'Tentukan kategori unit — ritel atau jasa — lalu kelola inventaris atau pesanan layanan sesuai jenisnya.'],
            ['badge' => 'TRANSAKSI', 'icon' => 'transaksi', 'title' => 'Catat Transaksi & Pembelian', 'description' => 'Catat transaksi harian, transaksi berulang, dan pembelian ke vendor secara terpusat.'],
            ['badge' => 'LAPORAN', 'icon' => 'laporan', 'title' => 'Pantau dan Laporkan', 'description' => 'Lihat statistik unit secara real-time, terbitkan dokumen resmi, lalu ekspor data kapan saja.'],
            ['badge' => 'KEAMANAN', 'icon' => 'keamanan', 'title' => 'Audit dan Keamanan Data', 'description' => 'Setiap perubahan data tercatat dalam log audit yang dapat ditelusuri kapan saja.'],
        ],

        'landing_faq_items' => [
            [
                'question' => 'Bagaimana cara mendaftarkan unit usaha baru?',
                'answer'   => 'Master Admin dapat menambahkan unit usaha baru melalui menu Master Management > Unit Usaha, mengisi data dasar beserta kategorinya (ritel atau jasa), lalu membuat akun Admin Unit penanggung jawabnya.',
            ],
            [
                'question' => 'Apakah laporan keuangan bisa digabung antar unit usaha?',
                'answer'   => 'Bisa. Master Admin punya menu Statistik Usaha dan Dokumen Resmi lintas unit yang merangkum data dari seluruh unit usaha, selain bisa diekspor per unit lewat menu Export Data.',
            ],
            [
                'question' => 'Berapa jumlah admin yang bisa mengakses satu unit usaha?',
                'answer'   => 'Tidak ada batasan jumlah admin. Satu unit usaha bisa memiliki lebih dari satu Admin Unit, dan seluruh akunnya dibuat serta dikelola oleh Master Admin.',
            ],
            [
                'question' => 'Apakah data transaksi tersimpan aman?',
                'answer'   => 'Ya. Setiap akun hanya bisa aktif di satu sesi login — login di perangkat lain otomatis mengakhiri sesi sebelumnya — dan seluruh aktivitas serta perubahan data tercatat dalam Audit Log yang bisa ditelusuri kapan saja.',
            ],
        ],
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key (dengan Cache)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $item = static::find($key);
            return $item !== null ? $item->value : $default;
        });
    }

    /**
     * Ambil pengaturan berbentuk DAFTAR (Fitur Unggulan, Cara Kerja, FAQ
     * landing page, dst) yang disimpan sebagai JSON string lewat set().
     * Dipakai bersama oleh App\Livewire\Master\Settings\Index (form admin)
     * & resources/views/landing.blade.php (tampilan publik) supaya logika
     * decode + fallback tidak ditulis ulang di dua tempat. Kalau key belum
     * pernah disimpan ATAU isinya rusak/bukan array, jatuh balik ke
     * LANDING_LIST_DEFAULTS[$key] (atau array kosong kalau key itu sendiri
     * tidak dikenal).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getList(string $key): array
    {
        $default = static::LANDING_LIST_DEFAULTS[$key] ?? [];

        $raw = static::get($key);
        if (! is_string($raw) || trim($raw) === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) && ! empty($decoded) ? array_values($decoded) : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan
     */
    public static function set(string $key, mixed $value): void
    {
        // Konversi boolean ke string jika diperlukan
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("setting_{$key}");
    }
}