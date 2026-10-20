<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/bootstrap_env.php';
require_once dirname(__DIR__) . '/app/db.php';
require_once dirname(__DIR__) . '/app/helpers.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

expect(positive_integer('1', 1000000) === 1, 'Nilai positif valid harus diterima.');
expect(positive_integer('1000000', 1000000) === 1000000, 'Batas atas yang diizinkan harus diterima.');
expect(positive_integer('-1', 1000000) === null, 'Kuantitas negatif harus ditolak.');
expect(positive_integer('0', 1000000) === null, 'Kuantitas nol harus ditolak.');
expect(positive_integer('1.5', 1000000) === null, 'Kuantitas pecahan harus ditolak.');
expect(positive_integer('1000001', 1000000) === null, 'Kuantitas di atas batas harus ditolak.');
expect(format_rupiah('174000.00') === 'Rp 174.000', 'Format Rupiah bulat salah.');
expect(format_rupiah('1234.50') === 'Rp 1.234,50', 'Format pecahan Rupiah salah.');
expect(csv_safe_cell('=1+1') === "'=1+1", 'Nilai CSV berbentuk formula harus dinetralkan.');
expect(csv_safe_cell('+SUM(A1:A2)') === "'+SUM(A1:A2)", 'Formula CSV yang diawali tanda tambah harus dinetralkan.');
expect(csv_safe_cell('-SUM(A1:A2)') === "'-SUM(A1:A2)", 'Formula CSV yang diawali tanda minus harus dinetralkan.');
expect(csv_safe_cell('@SUM(A1:A2)') === "'@SUM(A1:A2)", 'Formula CSV yang diawali @ harus dinetralkan.');
expect(csv_safe_cell('Produk normal') === 'Produk normal', 'Teks CSV biasa harus tetap sama.');

$userId = isset($argv[1]) && ctype_digit($argv[1]) ? (int) $argv[1] : 1;
$pdo = db();
$pdo->beginTransaction();
try {
    $invoice = 'TEST-' . strtoupper(bin2hex(random_bytes(5)));
    $insertSale = $pdo->prepare('INSERT INTO sales (user_id, invoice_no, sale_date, description, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
    $insertSale->execute([$userId, $invoice, current_date(), 'Uji rumus (rollback)', 2, '12000.00']);
    $saleId = (int) $pdo->lastInsertId();
    $readSubtotal = $pdo->prepare('SELECT subtotal FROM sales WHERE id = ?');
    $readSubtotal->execute([$saleId]);
    expect($readSubtotal->fetchColumn() === '24000.00', 'Subtotal 2 x 12.000 harus Rp24.000.');

    $updateSale = $pdo->prepare('UPDATE sales SET unit_price = ? WHERE id = ?');
    $updateSale->execute(['13000.00', $saleId]);
    $readSubtotal->execute([$saleId]);
    expect($readSubtotal->fetchColumn() === '26000.00', 'Subtotal harus berubah menjadi Rp26.000 setelah harga diperbarui.');

    $insertExpense = $pdo->prepare('INSERT INTO expenses (user_id, expense_date, category, description, amount) VALUES (?, ?, ?, ?, ?)');
    $insertExpense->execute([$userId, current_date(), 'Uji', 'Uji biaya (rollback)', '4000.00']);
    $salesTotal = $pdo->prepare('SELECT CAST(COALESCE(SUM(subtotal), 0) AS DECIMAL(18,2)) FROM sales WHERE user_id = ?');
    $salesTotal->execute([$userId]);
    $expensesTotal = $pdo->prepare('SELECT CAST(COALESCE(SUM(amount), 0) AS DECIMAL(18,2)) FROM expenses WHERE user_id = ?');
    $expensesTotal->execute([$userId]);
    $salesValue = $salesTotal->fetchColumn();
    $expensesValue = $expensesTotal->fetchColumn();
    $profit = $pdo->query('SELECT CAST(' . $pdo->quote((string) $salesValue) . ' - ' . $pdo->quote((string) $expensesValue) . ' AS DECIMAL(18,2))')->fetchColumn();
    expect($salesValue === '200000.00', 'Total penjualan setelah transaksi uji harus Rp200.000.');
    expect($expensesValue === '53000.00', 'Total pengeluaran setelah transaksi uji harus Rp53.000.');
    expect($profit === '147000.00', 'Laba sederhana setelah transaksi uji harus Rp147.000.');
    fwrite(STDOUT, "Validasi input dan keamanan CSV: lulus\nSubtotal sebelum ubah: Rp24.000\nSubtotal sesudah ubah: Rp26.000\nAgregasi database: penjualan " . format_rupiah((string) $salesValue) . ", pengeluaran " . format_rupiah((string) $expensesValue) . ", laba " . format_rupiah((string) $profit) . "\n");
    $pdo->rollBack();
    fwrite(STDOUT, "Baris sementara di-rollback; database tidak berubah.\n");
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $error;
}
