# Laporan Audit: Bug, Error, dan Cacat Sistem

Berdasarkan hasil penelusuran pada direktori kerja proyek, ditemukan sejumlah perbaikan (patch) yang belum di-commit namun sudah ada di proyek. Berikut adalah daftar bug, error, dan cacat sistem yang berhasil diidentifikasi:

## 1. Kehilangan Data Log secara Permanen (MassPrunable)
- **Masalah**: Model `AuditLog` sebelumnya menggunakan trait `MassPrunable` bawaan Laravel yang diatur untuk langsung menghapus log yang usianya lebih dari 90 hari melalui perintah `model:prune`.
- **Dampak**: Semua riwayat aktivitas login dan audit yang sudah usang terhapus tanpa jejak (tidak ada proses backup atau pengarsipan), yang melanggar standar kepatuhan sistem audit.
- **Status Patch**: Telah dinonaktifkan dan diganti dengan mekanisme rotasi arsip log ke file Excel (melalui `LogArchiveService` dan `logs:archive`).

## 2. Tabel Log yang Terus Membengkak / Tidak Dirotasi
- **Masalah**: Tidak adanya mekanisme rotasi (log rotation) yang aman untuk tabel log yang krusial. 
- **Dampak**: Tabel `auth_logs` dan `audit_logs` akan membebani database seiring berjalannya waktu jika dibiarkan (atau justru dihapus total karena bug nomor 1 di atas).
- **Status Patch**: Sistem pengarsipan berbasis file (log-archives) telah diimplementasikan, memisahkan ekspor file bulanan Excel yang diverifikasi sebelum datanya dihapus dari database.

## 3. Celah Keamanan Sesi Aktif (Tidak Ada Idle Timeout)
- **Masalah**: Sistem tidak memiliki fitur *idle timeout* (batas waktu tidak aktif) untuk pengguna yang sedang login. Sesi hanya bergantung pada umur kedaluwarsa absolut (absolute lifetime) cookie.
- **Dampak**: Sangat berisiko, terutama untuk Admin Unit yang sering menggunakan perangkat kasir atau perangkat bersama (shared device). Jika pengguna lupa logout, sesi akan terus terbuka.
- **Status Patch**: Telah ditambahkan middleware `EnsureSessionNotExpired` yang melacak aktivitas terakhir pengguna dan secara otomatis mengeluarkan paksa pengguna yang tidak aktif.

## 4. Transaksi Berulang (Recurring) Tidak Pernah Berjalan
- **Masalah**: Command untuk memproses transaksi berulang (`recurring:process`) sebelumnya memang sudah ditulis, namun **tidak pernah diregistrasikan** di dalam penjadwal tugas (`routes/console.php`).
- **Dampak**: Sistem transaksi berulang (seperti auto-approve tagihan rutin) cacat dan tidak pernah berjalan secara otomatis di latar belakang.
- **Status Patch**: Command `recurring:process` telah dijadwalkan ulang untuk berjalan otomatis setiap pukul 01:00 dini hari.

## 5. Laporan Rutin Otomatis (WhatsApp) Gagal Terkirim
- **Masalah**: Sama seperti transaksi berulang, jadwal pengiriman laporan otomatis via WhatsApp ke Master Admin (`report:routine-send`) terlewat untuk didaftarkan di kernel scheduler.
- **Dampak**: Laporan harian/berkala yang dijanjikan sistem tidak pernah dieksekusi.
- **Status Patch**: Telah didaftarkan untuk dipicu (trigger) setiap 15 menit melalui `Schedule::command('report:routine-send')->everyFifteenMinutes()`.

## 6. Desain Flaw pada Pembelian Master Admin (Master Purchasing)
- **Masalah**: Halaman Master Purchasing sebelumnya memungkinkan "Master Admin" untuk membuat atau mencatat "Form Pembelian" antar unit. Ini adalah desain arsitektur yang kurang tepat karena pencatatan pembelian harus terikat langsung ke stok fisik dan keuangan pada unit yang berbelanja.
- **Dampak**: Risiko data terpusat tidak sinkron (desync) dengan pergerakan stok nyata di lapangan.
- **Status Patch**: Modul *Master Purchasing* dibuat menjadi **Read-Only** khusus untuk Master Admin. Pembelian sebenarnya diwajibkan untuk dikelola langsung pada panel masing-masing Unit. Master hanya dapat memantau rekapan total untuk negosiasi vendor terpusat.

## 7. Cacat Filter Ekspor Audit Log
- **Masalah**: Class `AuditLogExport` sebelumnya secara default mengecualikan event-event otentikasi (seperti `USER_LOGIN`, `USER_LOGOUT`) dari hasil ekspor. 
- **Dampak**: Ketika log diarsipkan dan dihapus dari database, event-event penting ini justru tidak ikut diekspor, mengakibatkan hilangnya jejak otentikasi.
- **Status Patch**: Telah diperbaiki dengan menambahkan penanda `includeAuthEvents` saat pemanggilan fungsi arsip.

