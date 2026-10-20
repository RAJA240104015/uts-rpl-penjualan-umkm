-- DATA SIMULASI: gunakan hanya pada basis data pengembangan.
-- Jalankan setelah membuat pengguna. Ganti angka 1 dengan id pengguna lokal.
-- Tanggal dan nilai ini bukan transaksi UMKM nyata.
INSERT INTO sales (user_id, invoice_no, sale_date, description, quantity, unit_price) VALUES
(1, 'SIM-20261015-001', '2026-10-15', 'Nasi ayam (simulasi)', 3, 18000.00),
(1, 'SIM-20261015-002', '2026-10-15', 'Es teh (simulasi)', 2, 5000.00),
(1, 'SIM-20261016-001', '2026-10-16', 'Mie goreng (simulasi)', 4, 15000.00),
(1, 'SIM-20261018-001', '2026-10-18', 'Paket makan (simulasi)', 2, 25000.00);

INSERT INTO expenses (user_id, expense_date, category, description, amount, notes) VALUES
(1, '2026-10-15', 'Bahan baku', 'Belanja bahan (simulasi)', 22000.00, 'Data uji'),
(1, '2026-10-16', 'Operasional', 'Gas memasak (simulasi)', 18000.00, 'Data uji'),
(1, '2026-10-18', 'Kemasan', 'Wadah makanan (simulasi)', 9000.00, 'Data uji');
