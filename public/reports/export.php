<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$from = trim((string) ($_GET['from'] ?? ''));
$to = trim((string) ($_GET['to'] ?? ''));
if (!valid_period($from, $to)) { http_response_code(400); exit('Rentang tanggal tidak valid.'); }
$pdo = db();
$sales = $pdo->prepare('SELECT sale_date AS work_date, "Penjualan" AS movement, invoice_no AS reference, description, quantity, unit_price, subtotal AS amount FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
$sales->execute([current_user_id(), $from, $to]);
$expenses = $pdo->prepare('SELECT expense_date AS work_date, "Pengeluaran" AS movement, category AS reference, description, "" AS quantity, "" AS unit_price, amount FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?');
$expenses->execute([current_user_id(), $from, $to]);
$rows = array_merge($sales->fetchAll(), $expenses->fetchAll());
usort($rows, static fn(array $a, array $b): int => [$b['work_date'], $b['movement'], $b['reference']] <=> [$a['work_date'], $a['movement'], $a['reference']]);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan-umkm-' . $from . '-sampai-' . $to . '.csv"');
header('X-Content-Type-Options: nosniff');
$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Tanggal', 'Jenis', 'Nomor/Kategori', 'Deskripsi', 'Kuantitas', 'Harga Satuan (Rp)', 'Nominal (Rp)']);
foreach ($rows as $row) {
    fputcsv($output, [$row['work_date'], csv_safe_cell($row['movement']), csv_safe_cell((string) $row['reference']), csv_safe_cell((string) $row['description']), $row['quantity'], $row['unit_price'], $row['amount']]);
}
fclose($output);
