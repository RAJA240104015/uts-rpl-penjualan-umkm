# Catatan proses Git lokal

## Repository dan keamanan

Repository dibuat lokal pada `E:\zKULIAH\RPL\UTS` dengan branch utama `main`. Branch fitur lokal adalah `feature/laporan-keuangan`. Working tree bersih setelah penggabungan lokal. Perintah `git remote -v` tidak menampilkan remote; repository GitHub dan pull request belum dibuat. Berkas `.env` dan `work/` terabaikan oleh `.gitignore`; `.env` tidak tercatat dalam `git ls-files`.

## Riwayat commit

| Commit | Pesan |
|---|---|
| `04bc271` | `chore: initialize PHP project foundation` |
| `e90f7aa` | `feat: add MySQL schema and simulated data` |
| `1e379e7` | `feat: add authentication and dashboard` |
| `d7aa4e8` | `feat: add sales and expense recording` |
| `927d2cc` | `feat: add financial reports and CSV export` |
| `2354435` | `docs: add analysis diagrams and evidence plan` |
| `d9242b4` | `test: verify financial logic and prepare Vercel routing` |
| `75511af` | `docs: add git restore demonstration baseline` |
| `89b66ea` | `docs: add conflict simulation baseline` |
| `50d7387` | `docs: describe financial report feature branch` |
| `84b131f` | `docs: record report verification on main` |
| `9f2dfbb` | `merge: resolve report status documentation conflict` |
| `1e89bee` | `docs: record verified Git workflow and remote status` |
| `bb94199` | `docs: update branch feature verification status` |
| `fabb94a` | `feat: harden financial CSV export against formulas` |
| `c74b23f` | `test: cover minus-prefixed CSV formulas` |
| `e19d757` | `docs: record complete local history audit` |
| `d0b35fc` | `fix: initialize CSRF token before page output` |
| `HEAD` | `docs: record final local verification` |

## Pemulihan perubahan dengan `git restore`

File `docs/git-restore-demo.md` awalnya berisi kalimat dasar yang telah di-commit. Kalimatnya kemudian diubah sementara; `git diff -- docs/git-restore-demo.md` memperlihatkan perubahan. Perintah `git restore -- docs/git-restore-demo.md` memulihkan isi dasar, lalu `git diff --exit-code -- docs/git-restore-demo.md` mengembalikan kode keluar 0. File akhir kembali sama dengan commit dasar.

## Konflik dan penyelesaian

Pada commit `50d7387`, branch `feature/laporan-keuangan` mengubah baris status laporan. Pada commit `84b131f`, branch `main` mengubah baris yang sama dengan isi berbeda. Perintah `git merge main` ketika berada di feature branch menghasilkan `CONFLICT (content)` pada `docs/git-conflict-demo.md` dan status `UU`. Penanda konflik dibaca lalu dihapus dengan menggabungkan konteks fitur dan verifikasi lokal. `git diff --check` selesai tanpa temuan, penyelesaian di-commit sebagai `9f2dfbb`, kemudian branch `main` diperbarui memakai fast-forward dari feature branch. Setelah itu branch fitur disinkronkan, mengubah pengamanan ekspor CSV, dan di-commit sebagai `fabb94a`; pengujian validasi/rumus/CSV lulus dan commit tersebut juga digabungkan ke `main` secara fast-forward.

## Bukti yang masih perlu dilengkapi

Screenshot terminal dan GitHub tidak disimpan pada sesi ini. Remote GitHub, push, pull request, dan penggabungan pull request online belum dilakukan. Lihat `screenshots/README.md` untuk daftar screenshot autentik yang harus ditambahkan setelah repository dan akses akun tersedia.

## Pemeriksaan routing lokal

Router API dijalankan melalui PHP CLI dengan `REQUEST_URI` untuk `/`, `/login.php`, dan rute tak dikenal. Dashboard dan halaman masuk dirender tanpa peringatan PHP; rute tak dikenal ditolak. Setelah uji ini, inisialisasi token CSRF dipindahkan sebelum keluaran HTML agar header dapat dikirim lebih andal pada runtime serverless.
