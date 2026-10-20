# Aturan perhitungan keuangan

Aplikasi mencatat satu deskripsi barang atau layanan pada setiap transaksi penjualan.

- Subtotal penjualan = kuantitas × harga satuan. Nilai subtotal dihitung oleh kolom generated pada MySQL agar selalu konsisten.
- Total penjualan periode = jumlah subtotal penjualan milik pengguna pada tanggal awal dan akhir yang dipilih, termasuk kedua tanggal tersebut.
- Total pengeluaran periode = jumlah nominal pengeluaran milik pengguna pada rentang tanggal yang sama.
- Laba operasional sederhana = total penjualan − total pengeluaran.

Perhitungan laba adalah ringkasan sederhana dari data yang dicatat. Sistem belum memodelkan harga pokok penjualan, persediaan, pajak, piutang, penyusutan, atau komponen akuntansi lain. Data pada `database/sample-data.sql` diberi label simulasi dan bukan transaksi nyata.
