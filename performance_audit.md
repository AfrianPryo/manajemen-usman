# Audit Performa: Penyebab Sistem Berat & Rekomendasi Solusi

Berdasarkan pengecekan struktur kode dan *queries*, berikut adalah audit penyebab aplikasi terasa lambat (berat) dan langkah-langkah yang perlu dilakukan untuk optimasinya:

## 1. *Data Loading* Tanpa Paginasi (Bottleneck Terbesar)
- **Masalah**: Pada `app/Livewire/Master/Dashboard.php`, sistem mengambil data **seluruh** unit usaha dan **seluruh** admin secara bersamaan ke dalam memori menggunakan `get()` tanpa dibatasi `paginate()` atau dilimit secara ketat.
  ```php
  // Master/Dashboard.php (Baris 576)
  $units = Unit::with('users')->...->get();
  $users = User::with(['unit', 'roles'])->...->get();
  ```
- **Dampak**: Saat jumlah admin atau unit mencapai ratusan atau ribuan, hal ini akan memakan **RAM server yang sangat besar** (Memory Exhaustion). Selain itu, karena seluruh data di-*looping* (`@foreach`) untuk merender baris tabel HTML (`<tr>`), browser klien akan membeku (*freeze/lag*) saat memuat ukuran DOM yang masif.
- **Solusi**: 
  - Gunakan `->paginate(10)` atau `->paginate(20)` alih-alih `->get()`.
  - Jika digunakan untuk opsi *dropdown* (misal `<select>`), gunakan *Lazy Loading / Searchable Dropdown* (seperti Select2 atau Livewire-Select) agar tidak memuat 1000+ data sekaligus.

## 2. Beban Agregasi Analytics
- **Masalah**: Halaman *Statistik Usaha* (`Master\Analytics\Index.php`) dan *Dashboard* mengeksekusi banyak agregasi berat (`SUM`, `COUNT`, `GROUP BY`) pada tabel `finance_transactions`. Walaupun saat ini sudah di-*patch* menggunakan `Cache::remember` dengan TTL pendek (120 detik), *query* tersebut masih berpotensi menyebabkan *slow query* saat tabel transaksi mencapai ratusan ribu baris.
- **Dampak**: Waktu muat awal (sebelum tersimpan di *cache*) memakan waktu lama, dan memblokir I/O database.
- **Solusi**: Pastikan ada **Database Indexing** (indeks kolom) pada tabel `finance_transactions` untuk kolom-kolom yang sering difilter: `(unit_id, transaction_date)`, `type`, dan `status`.

## 3. Overhead *Livewire Rendering* pada Tabel Besar
- **Masalah**: Karena Master Dashboard merender ratusan baris data dari `$users`, Livewire juga harus mengelola perubahan statenya setiap kali ada *action* yang di-*trigger* (misal: membuka modal, mengganti tab, atau mengetik pada form).
- **Dampak**: Responsivitas *website* berkurang karena *payload* HTML yang di-*diff* (dibandingkan) sangat besar pada setiap siklus re-render.
- **Solusi**: Pisahkan komponen data-data tebal (seperti tabel Users) menjadi Livewire Component tersendiri (misal `<livewire:master.dashboard.users-table />`). Hal ini agar ketika Master Dashboard merender grafik atau notifikasi, tabel Users tidak ikut dirender ulang secara paksa, sehingga meringankan beban.

## 4. Query N+1 Terselubung (Sebelum Patch)
- **Masalah (Telah di-*Patch*)**: Sebelumnya, kode pada Analytics menggunakan pola `Unit::all()->map(...)` lalu mengeksekusi 3 *query* berbeda untuk setiap Unit, yang mana sangat memberatkan (*N+1 problem* = ~90 query untuk 30 unit). Ini sekarang sudah dikompresi menjadi 3 *query* `GROUP BY`.
- **Saran**: Lakukan peninjauan mendalam di *view* blade (seperti `resources/views/livewire/...`) untuk memastikan tidak ada pemanggilan relasi malas (*lazy-loading* / misal: `{{ $transaction->unit->name }}`) pada data yang luput dari inisiasi `with()`.

