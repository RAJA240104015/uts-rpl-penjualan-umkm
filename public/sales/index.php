<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$userId = current_user_id();
$defaultFrom = (new DateTimeImmutable(current_date()))->modify('first day of this month')->format('Y-m-d');
$from = trim((string) ($_GET['from'] ?? $defaultFrom));
$to = trim((string) ($_GET['to'] ?? current_date()));
$error = '';
if (!valid_period($from, $to)) {
    $error = 'Rentang tanggal tidak valid. Pastikan tanggal awal tidak melewati tanggal akhir.';
    $from = $defaultFrom;
    $to = current_date();
}
$statement = db()->prepare('SELECT id, invoice_no, sale_date, description, quantity, unit_price, subtotal FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ? ORDER BY sale_date DESC, id DESC');
$statement->execute([$userId, $from, $to]);
$sales = $statement->fetchAll();
$totalQuery = db()->prepare('SELECT COALESCE(SUM(subtotal), 0) FROM sales WHERE user_id = ? AND sale_date BETWEEN ? AND ?');
$totalQuery->execute([current_user_id(), $from, $to]);
$total = (string) $totalQuery->fetchColumn();
render_header('Penjualan');
page_heading('Catatan transaksi', 'Penjualan', 'Catat satu barang atau layanan untuk setiap transaksi.', 'Catat penjualan', 'sales/create.php');
render_notice();
if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
<section class="panel filter-panel"><form method="get" class="filter-form"><label>Dari tanggal<input type="date" name="from" value="<?= e($from) ?>" required></label><label>Sampai tanggal<input type="date" name="to" value="<?= e($to) ?>" required></label><button class="button button-secondary" type="submit">Terapkan</button><a class="button button-quiet" href="<?= e(url('sales/index.php')) ?>">Reset</a></form></section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Daftar transaksi</p><h2><?= count($sales) ?> transaksi</h2></div><div class="total-badge">Subtotal periode <strong><?= e(format_rupiah($total)) ?></strong></div></div>
<?php if (!$sales): ?><div class="empty-state compact"><div class="empty-icon">▤</div><h3>Belum ada penjualan pada periode ini</h3><p>Ubah rentang tanggal atau catat transaksi baru.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>No. transaksi</th><th>Tanggal</th><th>Deskripsi</th><th>Jumlah</th><th class="align-right">Harga satuan</th><th class="align-right">Subtotal</th><th></th></tr></thead><tbody>
<?php foreach ($sales as $sale): ?><tr><td><a class="row-link" href="<?= e(url('sales/show.php?id=' . (int) $sale['id'])) ?>"><?= e($sale['invoice_no']) ?></a></td><td><?= e((new DateTimeImmutable($sale['sale_date']))->format('d M Y')) ?></td><td><?= e($sale['description']) ?></td><td><?= (int) $sale['quantity'] ?></td><td class="align-right"><?= e(format_rupiah($sale['unit_price'])) ?></td><td class="align-right amount"><?= e(format_rupiah($sale['subtotal'])) ?></td><td><a class="text-link" href="<?= e(url('sales/edit.php?id=' . (int) $sale['id'])) ?>">Ubah</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php render_footer(); ?>
