<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap_env.php';
require_once dirname(__DIR__) . '/app/db.php';

if (PHP_SAPI !== 'cli' || !isset($argv[1]) || !ctype_digit($argv[1]) || (int) $argv[1] < 1) {
    fwrite(STDERR, "Penggunaan: php tools/seed_demo_data.php <id-pengguna>\n");
    exit(1);
}
$userId = (int) $argv[1];
$pdo = db();
$checkUser = $pdo->prepare('SELECT id FROM users WHERE id = ?');
$checkUser->execute([$userId]);
if (!$checkUser->fetch()) {
    fwrite(STDERR, "Pengguna tidak ditemukan.\n");
    exit(1);
}

$sales = [
    ['SIM-20261015-001', '2026-10-15', 'Nasi ayam (simulasi)', 3, '18000.00'],
    ['SIM-20261015-002', '2026-10-15', 'Es teh (simulasi)', 2, '5000.00'],
    ['SIM-20261016-001', '2026-10-16', 'Mie goreng (simulasi)', 4, '15000.00'],
    ['SIM-20261018-001', '2026-10-18', 'Paket makan (simulasi)', 2, '25000.00'],
];
$expenses = [
    ['2026-10-15', 'Bahan baku', 'Belanja bahan (simulasi)', '22000.00', 'Data uji'],
    ['2026-10-16', 'Operasional', 'Gas memasak (simulasi)', '18000.00', 'Data uji'],
    ['2026-10-18', 'Kemasan', 'Wadah makanan (simulasi)', '9000.00', 'Data uji'],
];
$pdo->beginTransaction();
try {
    $insertSale = $pdo->prepare('INSERT IGNORE INTO sales (user_id, invoice_no, sale_date, description, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($sales as $row) {
        $insertSale->execute(array_merge([$userId], $row));
    }
    $insertExpense = $pdo->prepare('INSERT INTO expenses (user_id, expense_date, category, description, amount, notes)
        SELECT ?, ?, ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS
        (SELECT 1 FROM expenses WHERE user_id = ? AND expense_date = ? AND description = ?)');
    foreach ($expenses as $row) {
        $insertExpense->execute([$userId, ...$row, $userId, $row[0], $row[2]]);
    }
    $pdo->commit();
    fwrite(STDOUT, "Data simulasi penjualan dan pengeluaran tersedia untuk pengujian.\n");
} catch (Throwable $error) {
    $pdo->rollBack();
    throw $error;
}
