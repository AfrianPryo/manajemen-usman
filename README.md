<div align="center">

# Manajemen USMAN

**Sistem manajemen unit usaha terpadu berbasis web untuk SMK, dibangun di atas Laravel + Livewire.**
Mengelola keuangan, inventaris, pembelian, dokumen resmi, pelanggan, dan laporan lintas unit usaha dalam satu platform.

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

</div>

---

## Daftar Isi

- [Tentang Sistem](#tentang-sistem)
- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Akun Default](#akun-default)
- [Struktur Peran (Role)](#struktur-peran-role)
- [Jadwal Otomatis](#jadwal-otomatis)
- [Deployment ke VPS](#deployment-ke-vps)

---

## Tentang Sistem

**Manajemen USMAN** adalah platform manajemen unit usaha untuk SMK yang memiliki beberapa unit bisnis
(Teaching Factory, Bengkel, Fotokopi, Alfamart, dsb.). Sistem ini memisahkan peran **Master Admin**
(pengelola pusat) dan **Admin Unit** (pengelola per unit), dengan kontrol akses berbasis peran
menggunakan Spatie Laravel Permission.

Landing page, nama aplikasi, logo, dan teks dapat dikonfigurasi langsung dari menu **Pengaturan**
tanpa menyentuh kode.

---

## Fitur

### Master Admin

| Modul | Deskripsi |
|---|---|
| **Dashboard** | Ringkasan omzet, transaksi, stok, dan statistik seluruh unit |
| **Statistik Usaha** | Grafik performa lintas unit, perbandingan pendapatan & pengeluaran |
| **Unit Usaha** | Kelola data unit (nama, logo, departemen, kategori: ritel/jasa) |
| **Admin** | Tambah/edit/nonaktifkan admin unit, reset password |
| **Vendor** | Daftar vendor/supplier lintas unit |
| **Pelanggan** | Manajemen pelanggan lintas unit |
| **Transaksi** | Pantau transaksi keuangan seluruh unit |
| **Transaksi Berulang** | Automasi transaksi rutin (auto-approve / konfirmasi WA) |
| **Inventaris** | Pantau stok produk seluruh unit, alert stok rendah/habis |
| **Pembelian (PO)** | Buat & pantau Purchase Order ke vendor |
| **Aset Unit** | Inventarisasi aset fisik per unit |
| **Dokumen Resmi** | Generate surat resmi bernomor otomatis (PDF/DOCX) |
| **Export Data** | Export transaksi, inventaris, log ke Excel |
| **Manajemen Layanan** | Pantau pesanan layanan unit jasa (bengkel, dll.) |
| **Log Aktivitas** | Riwayat login & aktivitas sistem |
| **Audit Log** | Catatan perubahan data (create/update/delete) |
| **Arsip Log** | Export log ke Excel sebelum dihapus otomatis |
| **Pengumuman** | Kirim pengumuman ke admin unit (+ notifikasi WhatsApp Fonnte) |
| **Notifikasi** | Pusat notifikasi sistem |
| **Pengaturan** | Konfigurasi nama app, logo, landing page, WhatsApp, modul aktif |
| **Tutorial** | Panduan penggunaan sistem untuk Master Admin |

### Admin Unit

| Modul | Deskripsi |
|---|---|
| **Dashboard** | Ringkasan omzet, stok, dan notifikasi unit sendiri |
| **Transaksi** | Catat pemasukan & pengeluaran unit |
| **Transaksi Berulang** | Konfirmasi / tolak transaksi rutin |
| **Inventaris** | Kelola stok produk unit |
| **Pembelian** | Buat Purchase Order ke vendor |
| **Aset** | Inventarisasi aset unit |
| **Pelanggan** | Data pelanggan unit |
| **Dokumen Resmi** | Buat & unduh surat resmi |
| **Export Data** | Export data unit ke Excel |
| **Manajemen Layanan** | *(Khusus unit kategori jasa)* Kelola pesanan layanan |
| **Log Aktivitas** | Riwayat aktivitas di unit sendiri |
| **Notifikasi** | Pusat notifikasi unit |
| **Profil** | Ganti password, setup nomor WhatsApp |
| **Tutorial** | Panduan penggunaan untuk Admin Unit |

### Keamanan

- Single active session (satu akun, satu sesi aktif)
- Blokir IP/perangkat mencurigakan otomatis
- Force password change pada login pertama
- Session expiry yang dapat dikonfigurasi
- Audit trail semua perubahan data
- Rotasi log otomatis (arsip lalu hapus setelah retensi)

---

## Tech Stack

| Kategori | Teknologi |
|---|---|
| **Backend** | PHP 8.3+, Laravel 13.x |
| **Frontend** | Livewire 4.x, Tailwind CSS 4.x, Vite 8 |
| **UI Icons** | Blade Heroicons 2.x |
| **Animasi** | GSAP 3, Lenis (smooth scroll), Three.js |
| **Database** | MySQL 8+ |
| **PDF / DOCX** | DomPDF, PHPWord |
| **Excel** | Maatwebsite Excel (PhpSpreadsheet) |
| **Roles** | Spatie Laravel Permission 8.x |
| **WhatsApp** | Fonnte API |
| **Queue** | Laravel Queue (database driver, upgradeable ke Redis) |

---

## Persyaratan Sistem

- **PHP** 8.3 atau lebih baru
- **Composer** 2.x
- **Node.js** 18+ dan **npm**
- **MySQL** 8.0+
- **Web server**: Nginx atau Apache
- **PHP Extensions**: `pdo_mysql`, `mbstring`, `openssl`, `gd` atau `imagick`, `zip`, `xml`, `fileinfo`
- Akses ke **cron job** (untuk scheduler otomatis)

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/your-username/manajemen-usman.git
cd manajemen-usman
```

### 2. Install Dependensi PHP

```bash
composer install --optimize-autoloader --no-dev
```

> Untuk development, gunakan `composer install` tanpa `--no-dev`.

### 3. Install Dependensi Frontend

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuai kebutuhan — lihat bagian [Konfigurasi](#konfigurasi).

### 5. Siapkan Database

```bash
php artisan migrate
php artisan db:seed
```

> `db:seed` membuat akun Master Admin dan kategori keuangan default.
> Data contoh unit usaha **hanya** di-seed di environment `local` dan `testing`.

### 6. Build Assets

```bash
# Development (hot reload)
npm run dev

# Production
npm run build
```

### 7. Buat Storage Link

```bash
php artisan storage:link
```

---

## Konfigurasi

Salin `.env.example` ke `.env` lalu sesuaikan:

```env
# === APLIKASI ===
APP_NAME="Manajemen USMAN"
APP_ENV=production          # local | production
APP_DEBUG=false             # WAJIB false di production
APP_URL=https://domain.sch.id

# === DATABASE ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database

# === CACHE & SESSION ===
CACHE_STORE=file            # file | redis
SESSION_DRIVER=file         # file | redis
QUEUE_CONNECTION=database   # database | redis

# === EMAIL (opsional) ===
MAIL_MAILER=log             # log (dev) | smtp (production)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@domain.com
MAIL_FROM_NAME="${APP_NAME}"

# === WHATSAPP — FONNTE ===
FONNTE_TOKEN=token_fonnte_anda

# === MASTER ADMIN (hanya saat pertama setup) ===
# Kosongkan untuk generate otomatis — tampil sekali di console.
# HAPUS baris ini setelah db:seed pertama kali dijalankan.
MASTER_ADMIN_PASSWORD=password_rahasia_awal
```

---

## Akun Default

Setelah `php artisan db:seed`:

| Field | Nilai |
|---|---|
| **Username** | `admin.master` |
| **Password** | Nilai `MASTER_ADMIN_PASSWORD` di `.env`, atau auto-generate (tampil di console) |
| **Role** | `master-admin` |

> ⚠️ Saat login pertama kali, admin **wajib** mengganti password dan mengisi nomor WhatsApp aktif.

Admin Unit **tidak di-seed otomatis** — dibuat melalui menu **Master Admin → Admin → Tambah Admin**.

---

## Struktur Peran (Role)

| Role | Akses |
|---|---|
| `master-admin` | Seluruh sistem, lintas unit |
| `unit-admin` | Hanya data unit sendiri |

---

## Jadwal Otomatis

Tambahkan entri cron di server:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

| Waktu | Command | Fungsi |
|---|---|---|
| Setiap hari 00:00 | `model:prune` | Bersihkan data kadaluarsa |
| Setiap hari 01:00 | `recurring:process` | Proses transaksi berulang jatuh tempo |
| Setiap hari 02:00 | `logs:archive` | Arsipkan log lama ke Excel, lalu hapus dari DB |
| Setiap 15 menit | `report:routine-send` | Kirim laporan rutin otomatis via WhatsApp |

---

## Deployment ke VPS

```bash
# 1. Clone dan install
git clone https://github.com/your-username/manajemen-usman.git /var/www/manajemen-usman
cd /var/www/manajemen-usman
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 2. Konfigurasi .env
cp .env.example .env
php artisan key:generate
# Edit .env: APP_ENV=production, APP_DEBUG=false, DB_*, dll.

# 3. Migrasi & seed
php artisan migrate --force
php artisan db:seed --force

# 4. Storage & optimize
php artisan storage:link
php artisan optimize

# 5. Permission folder
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**Contoh konfigurasi Nginx:**

```nginx
server {
    listen 80;
    server_name domain.sch.id;
    root /var/www/manajemen-usman/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

> Untuk HTTPS gunakan Certbot: `certbot --nginx -d domain.sch.id`

---

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">
  Dibuat untuk mendukung pengelolaan unit usaha SMK yang lebih efisien dan terorganisir.
</div>