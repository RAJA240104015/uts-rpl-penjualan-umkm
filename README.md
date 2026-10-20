# Sistem Pencatatan Penjualan dan Laporan Keuangan UMKM

Aplikasi PHP dan MySQL sederhana untuk mencatat penjualan serta pengeluaran dan melihat ringkasan keuangan. Nama objek UMKM belum ditentukan; dokumen proyek memperlakukan studi kasus sebagai **UMKM studi kasus** sampai observasi atau wawancara dikonfirmasi.

## Fitur

- Login dengan password yang di-hash, cookie autentikasi bertanda tangan, dan logout.
- Dashboard omzet, pengeluaran, laba operasional sederhana, jumlah transaksi, ringkasan harian dan bulanan.
- Tambah, lihat, ubah, dan hapus penjualan serta pengeluaran.
- Laporan dengan rentang tanggal, ringkasan harian/bulanan, cetak, dan CSV.
- Validasi server-side, prepared statements, token CSRF, dan pembatasan data per pengguna.

## Teknologi

PHP 8.2+, PDO MySQL, MySQL/MariaDB, HTML, CSS, dan JavaScript. Aplikasi tidak memerlukan Composer.

## Menjalankan secara lokal

1. Pasang/aktifkan PHP dengan ekstensi `pdo_mysql` dan MySQL atau MariaDB.
2. Buat database dengan menjalankan `database/schema.sql` melalui klien MySQL.
3. Salin `.env.example` ke `.env`; isi koneksi database dan tetapkan `APP_KEY` acak minimal 32 karakter. Jangan publikasikan `.env`.
4. Buat pengguna pertama melalui `php tools/create_user.php`. Program meminta nama, username, dan password secara interaktif.
5. (Opsional) Isi data simulasi dengan `php tools/seed_demo_data.php <id-pengguna>`. Data yang dibuat hanya untuk uji.
6. Jalankan server lokal: `php -S 127.0.0.1:8000 -t public`.
7. Buka `http://127.0.0.1:8000`.

Pemeriksaan agregasi keuangan pada database lokal (menyisipkan satu catatan sementara di dalam transaksi lalu melakukan rollback):

```powershell
php tests/financial_logic.php
php tests/financial_range.php
```

Contoh konfigurasi lokal XAMPP: `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=uts_umkm`, `DB_USER=root`. Isi `DB_PASSWORD` sesuai konfigurasi lokal. Jangan gunakan akun database tanpa pembatasan pada produksi.

## Skema dan perhitungan

Skema sumber terdapat pada `database/schema.sql`. Kamus data dan relasi dijelaskan dalam `docs/rancangan-sistem.md`. Rumus sistem ada di `docs/rumus-keuangan.md`. Kolom uang menggunakan `DECIMAL` MySQL dan subtotal dihitung sebagai generated column. Laba operasional sederhana sama dengan total penjualan dikurangi total pengeluaran pada rentang tanggal terpilih.

## Konfigurasi produksi

Konfigurasi Vercel disiapkan memakai community runtime `vercel-php@0.6.2` (PHP 8.2) dan sebuah router yang hanya mengizinkan halaman PHP aplikasi. PHP bukan runtime resmi bawaan Vercel. Deployment tetap memerlukan project Vercel dan database MySQL jarak jauh yang dapat dijangkau dari function; database lokal XAMPP tidak dapat menjadi database publik. Atur `APP_KEY` dan semua `DB_*` sebagai environment variables pada hosting; aktifkan TLS sesuai persyaratan penyedia database. Jangan mengirim `.env` ke repository. Runtime belum diuji melalui deployment, dan koneksi database produksi belum tersedia.

## Batasan proyek

Satu baris penjualan mewakili satu deskripsi barang/layanan. Aplikasi ini bukan inventaris, e-commerce, point of sale, atau sistem akuntansi lengkap. Objek studi kasus dan kebutuhan lapangan harus dikonfirmasi melalui observasi/wawancara; tidak ada wawancara yang diklaim telah dilakukan.

## Status Git dan layanan jarak jauh

Repository lokal `main` memiliki riwayat commit bermakna; proses branch fitur, penguatan ekspor CSV, simulasi `git restore`, dan konflik merge dicatat pada `docs/git-local-process.md`. Repository publik GitHub: https://github.com/RAJA240104015/uts-rpl-penjualan-umkm. Impor source, branch remote, dan pull request sedang disiapkan. Screenshot autentik yang belum tersimpan didata pada `screenshots/README.md`.
