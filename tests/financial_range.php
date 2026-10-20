<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap_env.php';
require_once dirname(__DIR__) . '/app/db.php';
require_once dirname(__DIR__) . '/app/helpers.php';

function expect_range(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = db();
$pdo->beginTransaction();

try {
    $suffix = strtoupper(bin2hex(random_bytes(5)));
    $username = 'range-test-' . strtolower($suffix);
    $passwordHash = password_hash('temporary-' . $suffix, PASSWORD_DEFAULT);
    $createUser = $pdo->prepare('INSERT INTO users (name, username, password_hash) VALUES (?, ?, ?)');
    $createUser->execute(['Pengguna uji rentang', $username, $passwordHash]);
    $userId = (int) $pdo->lastInsertId();

    $start = new DateTimeImmutable(current_date());
    $startDate = $start->format('Y-m-d');
    $endDate = $start->modify('+1 day')->format('Y-m-d');
    $beforeDate = $start->modify('-1 day')->format('Y-m-d');
    $afterDate = $start->modify('+2 days')->format('Y-m-d');
    $invoicePrefix = 'TR-' . $suffix . '-';

    $sales = [
        ['before', $beforeDate, '10000.00'],
        ['start', $startDate, '1000.00'],
        ['end', $endDate, '1500.00'],
        ['after', $afterDate, '20000.00'],
    ];
    $insertSale = $pdo->prepare('INSERT INTO sales (user_id, invoice_no, sale_date, description, quantity, unit_price) VALUES (?, ?, ?, ?, 1, ?)');
    foreach ($sales as [$label, $date, $price]) {
        $insertSale->execute([$userId, $invoicePrefix . $label, $date, 'Uji batas tanggal (rollback)', $price]);
    }

    $expenses = [
        ['before', $beforeDate, '8000.00'],
        ['start', $startDate, '250.00'],
        ['end', $endDate, '300.00'],
        ['after', $afterDate, '9000.00'],
    ];
    $insertExpense = $pdo->prepare('INSERT INTO expenses (user_id, expense_date, category, description, amount) VALUES (?, ?, ?, ?, ?)');
    foreach ($expenses as [$label, $date, $amount]) {
        $insertExpense->execute([$userId, $date, 'Uji rentang', 'Uji batas tanggal ' . $label, $amount]);
    }

    $salesTotalQuery = $pdo->prepare('SELECT CAST(COALESCE(SUM(subtotal), 0) AS DECIMAL(18,2)) FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
    $salesTotalQuery->execute([$userId, $startDate, $endDate]);
    $salesTotal = $salesTotalQuery->fetchColumn();

    $expensesTotalQuery = $pdo->prepare('SELECT CAST(COALESCE(SUM(amount), 0) AS DECIMAL(18,2)) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?');
    $expensesTotalQuery->execute([$userId, $startDate, $endDate]);
    $expensesTotal = $expensesTotalQuery->fetchColumn();

    $daysQuery = $pdo->prepare('SELECT COUNT(DISTINCT sale_date) FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
    $daysQuery->execute([$userId, $startDate, $endDate]);
    $includedDays = (int) $daysQuery->fetchColumn();

    $profitQuery = $pdo->prepare('SELECT CAST(? - ? AS DECIMAL(18,2))');
    $profitQuery->execute([$salesTotal, $expensesTotal]);
    $profit = $profitQuery->fetchColumn();

    expect_range($salesTotal === '2500.00', 'Rentang inklusif harus memuat penjualan pada tanggal awal dan akhir saja.');
    expect_range($expensesTotal === '550.00', 'Rentang inklusif harus memuat pengeluaran pada tanggal awal dan akhir saja.');
    expect_range($includedDays === 2, 'Laporan harus hanya memuat dua tanggal pada rentang yang dipilih.');
    expect_range($profit === '1950.00', 'Laba rentang tanggal harus sama dengan penjualan dikurangi pengeluaran.');

    fwrite(STDOUT, "Uji rentang laporan: lulus\n");
    fwrite(STDOUT, "Tanggal awal dan akhir ikut dihitung; transaksi sebelum dan sesudah rentang dikecualikan.\n");
    fwrite(STDOUT, "Penjualan Rp2.500, pengeluaran Rp550, laba sederhana Rp1.950.\n");
    $pdo->rollBack();
    fwrite(STDOUT, "Seluruh baris uji di-rollback; basis data tidak berubah.\n");
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $error;
}