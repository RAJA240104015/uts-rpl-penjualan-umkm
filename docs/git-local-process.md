# Catatan proses Git

## Repository dan keamanan

Repository lokal berada di E:\zKULIAH\RPL\UTSS, dengan branch utama main dan branch fitur feature/laporan-keuangan. Remote origin mengarah ke repository publik https://github.com/RAJA240104015/uts-rpl-penjualan-umkm. Berkas .env serta folder kerja lokal diabaikan oleh .gitignore; .env tidak tercatat dalam Git. Berkas yang dikirim ke repository publik diambil dari git ls-files, sehingga .env lokal tidak ikut.

Repository README awal yang metadata commit-nya memakai alamat email akun dijadikan privat dan diubah nama menjadi arsip privat. Repository publik baru dibuat setelah pengaturan alamat email privat GitHub diaktifkan. Metadata author dan committer pada commit publik diperiksa melalui GitHub; semuanya memakai alamat noreply, bukan email kampus.

Riwayat publik disusun menjadi commit bertahap yang bermakna. Source diunggah setelah README, skema database, autentikasi/dashboard/penjualan, pengeluaran/laporan, serta analisis dan diagram selesai. Feature branch dan pull request ditambahkan setelahnya. Riwayat publik ini tidak menyalin rantai commit lokal lama karena pemisahan tersebut diperlukan untuk membersihkan metadata email sebelum publikasi.

## Ringkasan riwayat lokal

| Commit | Pesan |
|---|---|
| 04bc271 | chore: initialize PHP project foundation |
| e90f7aa | feat: add MySQL schema and simulated data |
| 1e379e7 | feat: add authentication and dashboard |
| d7aa4e8 | feat: add sales and expense recording |
| 927d2cc | feat: add financial reports and CSV export |
| 2354435 | docs: add analysis diagrams and evidence plan |
| d9242b4 | test: verify financial logic and prepare Vercel routing |
| 75511af | docs: add git restore demonstration baseline |
| 89b66ea | docs: add conflict simulation baseline |
| 50d7387 | docs: describe financial report feature branch |
| 84b131f | docs: record report verification on main |
| 9f2dfbb | merge: resolve report status documentation conflict |
| fabb94a | feat: harden financial CSV export against formulas |
| c74b23f | test: cover minus-prefixed CSV formulas |
| d0b35fc | fix: initialize CSRF token before page output |
| 3c52638 | docs: record public GitHub repository location |
| 9a4bc28 | test: verify report date range boundaries |
| 2164031 | docs: include report range verification command |

## Repository GitHub, branch, dan pull request

- Repository publik: https://github.com/RAJA240104015/uts-rpl-penjualan-umkm.
- Branch utama: main.
- Branch fitur: feature/laporan-keuangan.
- Pull request #1: https://github.com/RAJA240104015/uts-rpl-penjualan-umkm/pull/1.
- Pull request menambahkan uji rentang laporan inklusif dan dokumentasi perintah uji; GitHub menyatakan tidak ada konflik lalu PR digabungkan ke main pada 21 Oktober 2026.
- Merge commit PR: 7a50f50048fd75a9e488d10977d93cbc640d6b50.
- Commit publik mencakup source aplikasi, skema SQL, data uji simulasi, dokumentasi desain, serta diagram ERD, context, proses bisnis, dan SDLC.

## Pemulihan perubahan dengan git restore

File docs/git-restore-demo.md mula-mula berisi kalimat dasar yang sudah di-commit. Kalimatnya diubah sementara; git diff -- docs/git-restore-demo.md memperlihatkan perubahan. Perintah git restore -- docs/git-restore-demo.md memulihkan isi dasar, lalu git diff --exit-code -- docs/git-restore-demo.md menghasilkan kode keluar 0. File akhir sama dengan versi commit dasar.

## Konflik dan penyelesaian

Pada commit 50d7387, branch feature/laporan-keuangan mengubah baris status laporan. Pada commit 84b131f, branch main mengubah baris yang sama dengan isi berbeda. Perintah git merge main dari branch fitur menghasilkan CONFLICT (content) pada docs/git-conflict-demo.md dengan status UU. Penanda konflik dibaca dan diselesaikan dengan menggabungkan konteks fitur dan hasil verifikasi. git diff --check tidak menemukan masalah, penyelesaian dicatat dalam commit 9f2dfbb, lalu branch main diperbarui secara fast-forward.

Konflik tersebut dilakukan di Git lokal, bukan dibuat ulang di GitHub. Screenshot terminal untuk kondisi konflik dan resolusinya belum tersedia pada folder screenshot.

## Pengujian lokal

Pemeriksaan agregasi dan keamanan CSV dijalankan dengan php tests/financial_logic.php. Uji batas tanggal laporan dijalankan dengan php tests/financial_range.php; data sementara dibuat untuk tanggal sebelum, awal, akhir, dan sesudah periode. Hasil yang diharapkan adalah penjualan Rp2.500, pengeluaran Rp550, dan laba sederhana Rp1.950 pada rentang inklusif. Seluruh baris uji di-rollback.

## Bukti yang belum tersedia

Tangkapan browser aplikasi lokal (login, dashboard, penjualan, pengeluaran, laporan, dan tampilan ponsel) serta halaman publik GitHub (repository, feature branch, dan PR) sudah disimpan pada folder screenshot lokal. Berkas PNG sengaja tidak diunggah ke repository publik. Data pada screenshot aplikasi berlabel simulasi; akun uji dan catatan tambahannya dihapus setelah pengujian.

Screenshot terminal untuk PHP, `git log`, pemulihan `git restore`, dan konflik sebelum/sesudah penyelesaian, serta screenshot struktur database aktual masih belum tersedia. Deployment Vercel produksi: https://uts-rpl-penjualan-umkm.vercel.app. Halaman login dan CSS sudah diverifikasi dapat diakses; operasi yang menyimpan data memerlukan konfigurasi MySQL/MariaDB produksi. Screenshot terminal Git restore dan konflik lokal belum tersedia.