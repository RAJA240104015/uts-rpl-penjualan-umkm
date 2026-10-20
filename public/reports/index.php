<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$defaultFrom = (new DateTimeImmutable(current_date()))->modify('first day of this month')->format('Y-m-d');
$from = trim((string) ($_GET['from'] ?? $defaultFrom));
$to = trim((string) ($_GET['to'] ?? current_date()));
$error = '';
if (!valid_period($from, $to)) {
    $error = 'Rentang tanggal tidak valid. Pastikan tanggal awal tidak melewati tanggal akhir.';
    $from = $defaultFrom;
    $to = current_date();
}
$userId = current_user_id();
$salesSummary = db()->prepare('SELECT COALESCE(SUM(subtotal), 0) AS total, COUNT(*) AS count FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
$salesSummary->execute([$userId, $from, $to]);
$sales = $salesSummary->fetch();
$expenseSummary = db()->prepare('SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS count FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?');
$expenseSummary->execute([$userId, $from, $to]);
$expenses = $expenseSummary->fetch();
$profitQuery = db()->prepare('SELECT CAST(COALESCE((SELECT SUM(subtotal) FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?), 0) - COALESCE((SELECT SUM(amount) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?), 0) AS DECIMAL(18,2))');
$profitQuery->execute([$userId, $from, $to, $userId, $from, $to]);
$profit = (string) $profitQuery->fetchColumn();
$dailyQuery = db()->prepare('SELECT work_date, SUM(sales_total) AS sales_total, SUM(expenses_total) AS expenses_total, SUM(sales_total) - SUM(expenses_total) AS profit FROM (
    SELECT sale_date AS work_date, subtotal AS sales_total, 0 AS expenses_total FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?
    UNION ALL
    SELECT expense_date AS work_date, 0 AS sales_total, amount AS expenses_total FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?
) AS movements GROUP BY work_date ORDER BY work_date DESC');
$dailyQuery->execute([$userId, $from, $to, $userId, $from, $to]);
$daily = $dailyQuery->fetchAll();
$monthlyQuery = db()->prepare('SELECT DATE_FORMAT(work_date, "%Y-%m") AS period, SUM(sales_total) AS sales_total, SUM(expenses_total) AS expenses_total, SUM(sales_total) - SUM(expenses_total) AS profit FROM (
    SELECT sale_date AS work_date, subtotal AS sales_total, 0 AS expenses_total FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?
    UNION ALL
    SELECT expense_date AS work_date, 0 AS sales_total, amount AS expenses_total FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?
) AS movements GROUP BY DATE_FORMAT(work_date, "%Y-%m") ORDER BY period DESC');
$monthlyQuery->execute([$userId, $from, $to, $userId, $from, $to]);
$monthly = $monthlyQuery->fetchAll();
$salesRowsQuery = db()->prepare('SELECT id, invoice_no, sale_date, description, quantity, unit_price, subtotal FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ? ORDER BY sale_date DESC, id DESC');
$salesRowsQuery->execute([$userId, $from, $to]);
$salesRows = $salesRowsQuery->fetchAll();
$expenseRowsQuery = db()->prepare('SELECT id, expense_date, category, description, amount FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ? ORDER BY expense_date DESC, id DESC');
$expenseRowsQuery->execute([$userId, $from, $to]);
$expenseRows = $expenseRowsQuery->fetchAll();
render_header('Laporan keuangan');
page_heading('Analisis periode', 'Laporan keuangan', 'Pilih rentang tanggal untuk membandingkan penjualan dan pengeluaran.');
if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
<section class="panel filter-panel"><form method="get" class="filter-form"><label>Dari tanggal<input type="date" name="from" value="<?= e($from) ?>" required></label><label>Sampai tanggal<input type="date" name="to" value="<?= e($to) ?>" required></label><button class="button button-secondary" type="submit">Terapkan</button><a class="button button-quiet" href="<?= e(url('reports/index.php')) ?>">Reset</a><a class="button button-quiet hide-print" href="<?= e(url('reports/export.php?from=' . rawurlencode($from) . '&to=' . rawurlencode($to))) ?>">Unduh CSV</a><button class="button button-quiet hide-print" type="button" data-print> Cetak laporan</button></form></section>
<section class="metric-grid report-metrics"><article class="metric-card metric-accent"><div class="metric-label">Total penjualan</div><div class="metric-value"><?= e(format_rupiah($sales['total'])) ?></div><div class="metric-caption"><?= (int) $sales['count'] ?> transaksi</div></article><article class="metric-card"><div class="metric-label">Total pengeluaran</div><div class="metric-value"><?= e(format_rupiah($expenses['total'])) ?></div><div class="metric-caption"><?= (int) $expenses['count'] ?> catatan biaya</div></article><article class="metric-card"><div class="metric-label">Laba operasional sederhana</div><div class="metric-value <?= str_starts_with($profit, '-') ? 'negative' : '' ?>"><?= e(format_rupiah($profit)) ?></div><div class="metric-caption">Penjualan dikurangi pengeluaran</div></article></section>
<div class="report-grid"><section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Harian</p><h2>Ringkasan per tanggal</h2></div></div><?php if (!$daily): ?><p class="muted">Tidak ada data pada periode ini.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th class="align-right">Penjualan</th><th class="align-right">Pengeluaran</th><th class="align-right">Laba sederhana</th></tr></thead><tbody><?php foreach ($daily as $row): ?><tr><td><?= e((new DateTimeImmutable($row['work_date']))->format('d M Y')) ?></td><td class="align-right"><?= e(format_rupiah($row['sales_total'])) ?></td><td class="align-right"><?= e(format_rupiah($row['expenses_total'])) ?></td><td class="align-right amount"><?= e(format_rupiah($row['profit'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Bulanan</p><h2>Ringkasan per bulan</h2></div></div><?php if (!$monthly): ?><p class="muted">Tidak ada data pada periode ini.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Bulan</th><th class="align-right">Penjualan</th><th class="align-right">Pengeluaran</th><th class="align-right">Laba sederhana</th></tr></thead><tbody><?php foreach ($monthly as $row): ?><tr><td><?= e((new DateTimeImmutable($row['period'] . '-01'))->format('F Y')) ?></td><td class="align-right"><?= e(format_rupiah($row['sales_total'])) ?></td><td class="align-right"><?= e(format_rupiah($row['expenses_total'])) ?></td><td class="align-right amount"><?= e(format_rupiah($row['profit'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></div>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Rincian</p><h2>Penjualan dan pengeluaran periode</h2></div></div><div class="report-detail-grid"><div><h3>Penjualan (<?= count($salesRows) ?>)</h3><?php if (!$salesRows): ?><p class="muted">Belum ada transaksi.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Deskripsi</th><th class="align-right">Subtotal</th></tr></thead><tbody><?php foreach ($salesRows as $row): ?><tr><td><?= e((new DateTimeImmutable($row['sale_date']))->format('d M')) ?></td><td><?= e($row['description']) ?></td><td class="align-right"><?= e(format_rupiah($row['subtotal'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div><div><h3>Pengeluaran (<?= count($expenseRows) ?>)</h3><?php if (!$expenseRows): ?><p class="muted">Belum ada pengeluaran.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Kategori dan deskripsi</th><th class="align-right">Nominal</th></tr></thead><tbody><?php foreach ($expenseRows as $row): ?><tr><td><?= e((new DateTimeImmutable($row['expense_date']))->format('d M')) ?></td><td><?= e($row['category'] . ': ' . $row['description']) ?></td><td class="align-right"><?= e(format_rupiah($row['amount'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div></section>
<p class="report-caveat">Laba operasional sederhana hanya mengurangkan pengeluaran yang dicatat dari total penjualan; perhitungan ini belum memodelkan HPP, pajak, persediaan, atau komponen akuntansi lain.</p>
<?php render_footer(); ?>
