<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_auth();
$userId = current_user_id();
$today = current_date();
$monthStart = (new DateTimeImmutable($today))->modify('first day of this month')->format('Y-m-d');
$monthEnd = (new DateTimeImmutable($today))->modify('last day of this month')->format('Y-m-d');

$salesQuery = db()->prepare('SELECT COALESCE(SUM(subtotal), 0) AS total, COUNT(*) AS count FROM sales WHERE user_id = ? AND sale_date = ?');
$salesQuery->execute([$userId, $today]);
$todaySales = $salesQuery->fetch();
$expenseQuery = db()->prepare('SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE user_id = ? AND expense_date = ?');
$expenseQuery->execute([$userId, $today]);
$todayExpenses = $expenseQuery->fetchColumn();

$monthSalesQuery = db()->prepare('SELECT COALESCE(SUM(subtotal), 0) AS total, COUNT(*) AS count FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
$monthSalesQuery->execute([$userId, $monthStart, $monthEnd]);
$monthSales = $monthSalesQuery->fetch();
$monthExpenseQuery = db()->prepare('SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?');
$monthExpenseQuery->execute([$userId, $monthStart, $monthEnd]);
$monthExpenses = $monthExpenseQuery->fetchColumn();
$monthProfitQuery = db()->prepare('SELECT CAST(COALESCE((SELECT SUM(subtotal) FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?), 0) - COALESCE((SELECT SUM(amount) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?), 0) AS DECIMAL(18,2))');
$monthProfitQuery->execute([$userId, $monthStart, $monthEnd, $userId, $monthStart, $monthEnd]);
$profitRupiah = (string) $monthProfitQuery->fetchColumn();

$dailySalesQuery = db()->prepare('SELECT sale_date AS day, SUM(subtotal) AS total FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ? GROUP BY sale_date');
$dailySalesQuery->execute([$userId, (new DateTimeImmutable($today))->modify('-6 days')->format('Y-m-d'), $today]);
$dailySales = [];
foreach ($dailySalesQuery->fetchAll() as $row) { $dailySales[$row['day']] = $row['total']; }
$recentQuery = db()->prepare('SELECT id, invoice_no, sale_date, description, quantity, unit_price, subtotal FROM sales WHERE user_id = ? ORDER BY sale_date DESC, id DESC LIMIT 5');
$recentQuery->execute([$userId]);
$recentSales = $recentQuery->fetchAll();

render_header('Ringkasan');
page_heading('Ikhtisar usaha', 'Ringkasan', 'Pantau hasil pencatatan harian dan bulan berjalan.');
render_notice();
?>
<div class="period-banner"><span class="period-icon">◷</span><span>Hari ini, <?= e((new DateTimeImmutable($today))->format('d F Y')) ?></span><span class="period-divider"></span><span>Bulan berjalan: <?= e((new DateTimeImmutable($today))->format('F Y')) ?></span></div>
<section class="metric-grid" aria-label="Ringkasan keuangan">
    <article class="metric-card metric-accent"><div class="metric-label">Omzet bulan ini <span class="metric-symbol">↗</span></div><div class="metric-value"><?= e(format_rupiah($monthSales['total'])) ?></div><div class="metric-caption"><?= (int) $monthSales['count'] ?> transaksi tercatat</div></article>
    <article class="metric-card"><div class="metric-label">Pengeluaran bulan ini <span class="metric-symbol metric-symbol-warm">↘</span></div><div class="metric-value"><?= e(format_rupiah((string) $monthExpenses)) ?></div><div class="metric-caption">Total nominal periode berjalan</div></article>
        <article class="metric-card"><div class="metric-label">Laba sederhana <span class="metric-symbol metric-symbol-green">◇</span></div><div class="metric-value <?= str_starts_with($profitRupiah, '-') ? 'negative' : '' ?>"><?= e(format_rupiah($profitRupiah)) ?></div><div class="metric-caption">Omzet dikurangi pengeluaran tercatat</div></article>
    <article class="metric-card"><div class="metric-label">Penjualan hari ini <span class="metric-symbol metric-symbol-blue">▤</span></div><div class="metric-value"><?= e(format_rupiah($todaySales['total'])) ?></div><div class="metric-caption"><?= (int) $todaySales['count'] ?> transaksi hari ini</div></article>
</section>
<div class="dashboard-grid">
    <section class="panel daily-panel"><div class="panel-heading"><div><p class="eyebrow">Aktivitas</p><h2>Penjualan 7 hari terakhir</h2></div><a class="text-link" href="<?= e(url('reports/index.php')) ?>">Lihat laporan <span aria-hidden="true">→</span></a></div>
        <div class="bar-chart" role="img" aria-label="Ringkasan penjualan harian tujuh hari terakhir">
            <?php
            $days = [];
            for ($offset = 6; $offset >= 0; $offset--) {
                $day = (new DateTimeImmutable($today))->modify('-' . $offset . ' days')->format('Y-m-d');
                $days[] = ['date' => $day, 'total' => (string) ($dailySales[$day] ?? '0')];
            }
            $maxValue = $days ? max(array_map(static fn(array $day): float => (float) $day['total'], $days)) : 0.0;
            foreach ($days as $day):
                $height = $maxValue > 0 ? max(6, (int) round(((float) $day['total'] / $maxValue) * 100)) : 6;
            ?>
            <div class="bar-column"><span class="bar-value"><?= e((int) $day['total'] > 0 ? format_rupiah($day['total']) : '—') ?></span><div class="bar-track"><div class="bar-fill <?= $day['date'] === $today ? 'bar-fill-today' : '' ?>" style="height: <?= $height ?>%"></div></div><span class="bar-label"><?= e((new DateTimeImmutable($day['date']))->format('D')) ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="panel month-panel"><div class="panel-heading"><div><p class="eyebrow">Periode berjalan</p><h2>Posisi bulan ini</h2></div><span class="status-chip">Bulan ini</span></div>
        <div class="summary-line"><span>Total penjualan</span><strong><?= e(format_rupiah($monthSales['total'])) ?></strong></div>
        <div class="summary-line"><span>Total pengeluaran</span><strong><?= e(format_rupiah((string) $monthExpenses)) ?></strong></div>
        <div class="summary-line summary-total"><span>Laba operasional sederhana</span><strong><?= e(format_rupiah($profitRupiah)) ?></strong></div>
        <p class="panel-footnote">Nilai mengikuti tanggal dan data yang Anda catat. Belum mencakup HPP, pajak, atau persediaan.</p>
    </section>
</div>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Terbaru</p><h2>Penjualan terakhir</h2></div><a class="text-link" href="<?= e(url('sales/index.php')) ?>">Semua transaksi <span aria-hidden="true">→</span></a></div>
    <?php if (!$recentSales): ?><div class="empty-state"><div class="empty-icon">＋</div><h3>Belum ada transaksi</h3><p>Catat penjualan pertama untuk mulai melihat ringkasan usaha.</p><a class="button button-primary" href="<?= e(url('sales/create.php')) ?>">Catat penjualan</a></div>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>No. transaksi</th><th>Tanggal</th><th>Deskripsi</th><th>Qty</th><th class="align-right">Subtotal</th></tr></thead><tbody>
    <?php foreach ($recentSales as $sale): ?><tr><td><a class="row-link" href="<?= e(url('sales/show.php?id=' . (int) $sale['id'])) ?>"><?= e($sale['invoice_no']) ?></a></td><td><?= e((new DateTimeImmutable($sale['sale_date']))->format('d M Y')) ?></td><td><?= e($sale['description']) ?></td><td><?= (int) $sale['quantity'] ?></td><td class="align-right amount"><?= e(format_rupiah($sale['subtotal'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php render_footer(); ?>
