<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Key berisi kredensial: nilainya dienkripsi (Crypt, APP_KEY) saat
     * disimpan ke tabel & cache, dan didekripsi otomatis saat dibaca lewat
     * get()/getMany(). Nilai lama yang masih plaintext tetap terbaca dan
     * otomatis terenkripsi saat pengaturannya disimpan ulang.
     */
    protected const ENCRYPTED_KEYS = ['wa_api_key'];
    protected const ENCRYPTED_PREFIX = 'enc:v1:';

    protected static function decodeValue(string $key, mixed $value): mixed
    {
        if (! in_array($key, static::ENCRYPTED_KEYS, true)
            || ! is_string($value)
            || ! str_starts_with($value, static::ENCRYPTED_PREFIX)) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(static::ENCRYPTED_PREFIX)));
        } catch (DecryptException $e) {
            Log::error("Setting: gagal mendekripsi '{$key}' (APP_KEY berubah?). Isi ulang di Pengaturan.");

            return null;
        }
    }

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
        // Jarak vertikal (rem) antara judul baris atas & bawah hero di desktop,
        // yaitu margin atas+bawah pada kurung tempat elemen ASCII berada.
        // Nilai bawaan 2 = tampilan asli sebelum jarak ini bisa diatur.
        'landing_hero_title_gap'    => '2',
        // Lebar jarak antar tanda kurung ( ) di hero (rem, desktop) &
        // pergeseran elemen ASCII dari posisi bawaannya (px; X positif =
        // ke kanan, Y positif = ke bawah). Bawaan = tampilan asli.
        'landing_hero_bracket_width' => '14',
        'landing_hero_ascii_x'       => '0',
        'landing_hero_ascii_y'       => '0',

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
     * Ambil nilai pengaturan berdasarkan key (dengan Cache).
     *
     * Yang di-cache HANYA kondisi baris di tabel: ['v' => nilai] bila baris
     * ada, atau ['missing' => true] bila belum ada. Nilai $default TIDAK
     * pernah ikut di-cache -- dulu default dari pemanggil pertama "menang"
     * selamanya untuk semua pemanggil lain (mis. app_name punya beberapa
     * default berbeda), dan null yang ter-cache bisa memicu TypeError pada
     * properti bertipe string. Penanda 'missing' tetap mencegah query
     * berulang untuk key yang belum pernah disimpan.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $entry = Cache::rememberForever(static::cacheKey($key), function () use ($key) {
            $item = static::find($key);

            return $item !== null ? ['v' => $item->value] : ['missing' => true];
        });

        return is_array($entry) && array_key_exists('v', $entry) ? static::decodeValue($key, $entry['v']) : $default;
    }

    /**
     * Key cache setting. Prefix "setting_v3_" sengaja dibedakan dari "v2"
     * karena entri lama bisa berisi nilai DEFAULT yang ikut ter-cache
     * (perilaku lama yang sudah diperbaiki) -- entri itu tidak boleh terbaca.
     */
    protected static function cacheKey(string $key): string
    {
        return "setting_v3_{$key}";
    }

    /**
     * Ambil BEBERAPA pengaturan sekaligus dengan satu panggilan cache
     * (Cache::many) alih-alih satu lookup per key. Semantiknya identik
     * dengan get(): nilai yang sudah ada di cache dipakai apa adanya, dan
     * key yang belum ada di cache jatuh ke get() (yang mengisi cache dari
     * database atau memakai default).
     *
     * @param  array<string, mixed>  $defaults  [key => nilai default]
     * @return array<string, mixed>             [key => nilai]
     */
    public static function getMany(array $defaults): array
    {
        $cacheKeys = [];
        foreach (array_keys($defaults) as $key) {
            $cacheKeys[$key] = static::cacheKey($key);
        }

        $cached = Cache::many(array_values($cacheKeys));

        $result = [];
        foreach ($defaults as $key => $default) {
            $entry = $cached[$cacheKeys[$key]] ?? null;

            if (is_array($entry) && array_key_exists('v', $entry)) {
                $result[$key] = static::decodeValue($key, $entry['v']);
            } elseif (is_array($entry) && ($entry['missing'] ?? false)) {
                $result[$key] = $default;
            } else {
                $result[$key] = static::get($key, $default);
            }
        }

        return $result;
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

        $value = (string) $value;

        if (in_array($key, static::ENCRYPTED_KEYS, true) && $value !== '') {
            $value = static::ENCRYPTED_PREFIX . Crypt::encryptString($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(static::cacheKey($key));
        Cache::forget("setting_v2_{$key}"); // sisa key format sebelumnya, jika masih ada
        Cache::forget("setting_{$key}"); // sisa key format lama, jika masih ada
    }
}