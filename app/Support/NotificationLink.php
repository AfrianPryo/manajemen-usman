<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Menentukan halaman tujuan ("Lihat detail") sebuah notifikasi untuk AKUN
 * YANG SEDANG MEMBUKANYA, dihitung saat notifikasi diklik.
 *
 * Kenapa tidak lagi memakai kolom `url` yang tersimpan: notifikasi alert
 * (stok, aset, transaksi pending) dikirim ke SEMUA user, tetapi `url`-nya
 * dulu diisi dari header Referer milik user yang kebetulan memicu alert.
 * Akibatnya tautan menunjuk ke halaman umum (bukan item terkait), dan bagi
 * penerima lain bisa berupa halaman Master (403 untuk Admin Unit) atau
 * halaman unit yang salah. Sekarang tujuan dibangun dari ID item yang ada
 * di data notifikasi, sesuai peran & unit penerima -- sehingga notifikasi
 * lama yang sudah tersimpan pun ikut benar.
 *
 * Halaman tujuan (Inventaris/Aset/Transaksi) menerima parameter `?cari=`
 * yang mengisi kolom pencarian dengan kode/nomor unik item, jadi item yang
 * dimaksud langsung tampil di daftar.
 */
class NotificationLink
{
    /**
     * Nama route per sumber data. Diperiksa berurutan lewat Route::has(),
     * jadi kalau nama route di aplikasi berbeda cukup tambahkan di sini.
     * Awalan "master." / "unit." ditambahkan otomatis sesuai peran.
     */
    private const RESOURCES = [
        'product'     => ['inventory', 'products'],
        'asset'       => ['assets', 'asset'],
        'transaction' => ['transactions'],
        'recurring'   => ['recurring-transactions', 'recurring-transaction'],
    ];

    /**
     * Murah (tanpa query): apakah notifikasi ini punya tujuan sama sekali?
     * Dipakai view untuk memutuskan tombol "Lihat detail" perlu tampil.
     */
    public static function hasTarget(array $data): bool
    {
        if (($data['type'] ?? null) === 'password_reset_request') {
            return true;
        }

        foreach (['product_id', 'asset_id', 'transaction_id', 'recurring_transaction_id'] as $key) {
            if (! empty($data[$key])) {
                return true;
            }
        }

        $url = $data['url'] ?? null;

        return is_string($url) && $url !== '' && $url !== '#';
    }

    /**
     * @return array{url: ?string, message: ?string} `message` terisi bila
     *         url null, menjelaskan kenapa tujuan tidak bisa dibuka.
     */
    public static function resolve(array $data, User $user): array
    {
        if (($data['type'] ?? null) === 'password_reset_request') {
            return $user->isMasterAdmin()
                ? self::found(self::route('master', ['users']))
                : self::missing('Halaman tujuan hanya tersedia untuk Admin Master.');
        }

        $target = match (true) {
            ! empty($data['product_id'])                => ['product', Product::find($data['product_id']), 'code'],
            ! empty($data['asset_id'])                  => ['asset', Asset::find($data['asset_id']), 'asset_tag'],
            ! empty($data['transaction_id'])            => ['transaction', FinanceTransaction::find($data['transaction_id']), 'reference_no'],
            ! empty($data['recurring_transaction_id'])  => ['recurring', RecurringTransaction::find($data['recurring_transaction_id']), null],
            default                                     => null,
        };

        if ($target !== null) {
            [$resource, $record, $searchField] = $target;

            if (! $record) {
                return self::missing('Data terkait sudah tidak tersedia (mungkin telah dihapus).');
            }

            if (! $user->isMasterAdmin() && (int) $record->unit_id !== (int) $user->unit_id) {
                return self::missing('Data ini milik unit lain, Anda tidak memiliki akses ke halamannya.');
            }

            $query = $searchField ? ['cari' => $record->{$searchField}] : [];
            $url = self::routeFor($user, self::RESOURCES[$resource], $query);

            if ($url) {
                return self::found($url);
            }
        }

        // Cadangan: notifikasi yang memang membawa url sendiri (mis. dari
        // fitur lain) atau resource yang route-nya tidak ditemukan di atas.
        $stored = self::safeStoredUrl($data['url'] ?? null, $user);

        return $stored
            ? self::found($stored)
            : self::missing('Halaman tujuan tidak tersedia untuk akun Anda.');
    }

    /**
     * Route untuk peran user: master-admin selalu ke halaman master, admin
     * unit ke halaman unit miliknya (butuh slug unit sebagai parameter).
     */
    private static function routeFor(User $user, array $names, array $query): ?string
    {
        if ($user->isMasterAdmin()) {
            return self::route('master', $names, [], $query);
        }

        $slug = $user->unit?->slug;

        return $slug ? self::route('unit', $names, ['unit' => $slug], $query) : null;
    }

    private static function route(string $scope, array $names, array $params = [], array $query = []): ?string
    {
        foreach ($names as $name) {
            $routeName = "{$scope}.{$name}.index";

            if (Route::has($routeName)) {
                return route($routeName, $params + $query);
            }
        }

        return null;
    }

    /**
     * url tersimpan hanya dipercaya kalau berasal dari aplikasi ini, bukan
     * endpoint internal Livewire (hanya menerima POST -> 405), dan bukan
     * halaman Master untuk akun non-master.
     */
    private static function safeStoredUrl(mixed $url, User $user): ?string
    {
        if (! is_string($url) || $url === '' || $url === '#') {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && $parts['host'] !== parse_url(url('/'), PHP_URL_HOST)) {
            return null;
        }

        $path = '/' . ltrim($parts['path'] ?? '/', '/');

        if (str_contains($path, '/livewire')) {
            return null;
        }

        if (! $user->isMasterAdmin() && preg_match('#^/master(/|$)#', $path)) {
            return null;
        }

        return $url;
    }

    private static function found(?string $url): array
    {
        return ['url' => $url, 'message' => null];
    }

    private static function missing(string $message): array
    {
        return ['url' => null, 'message' => $message];
    }
}
