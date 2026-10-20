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
$statement = db()->prepare('SELECT id, expense_date, category, description, amount, notes FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ? ORDER BY expense_date DESC, id DESC');
$statement->execute([current_user_id(), $from, $to]);
$expenses = $statement->fetchAll();
$totalQuery = db()->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?');
$totalQuery->execute([current_user_id(), $from, $to]);
$total = (string) $totalQuery->fetchColumn();
render_header('Pengeluaran');
page_heading('Biaya usaha', 'Pengeluaran', 'Catat biaya yang dibayarkan agar laporan periode lebih lengkap.', 'Catat pengeluaran', 'expenses/create.php');
render_notice();
if ($error !== ''): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
<section class="panel filter-panel"><form method="get" class="filter-form"><label>Dari tanggal<input type="date" name="from" value="<?= e($from) ?>" required></label><label>Sampai tanggal<input type="date" name="to" value="<?= e($to) ?>" required></label><button class="button button-secondary" type="submit">Terapkan</button><a class="button button-quiet" href="<?= e(url('expenses/index.php')) ?>">Reset</a></form></section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Daftar pengeluaran</p><h2><?= count($expenses) ?> catatan</h2></div><div class="total-badge">Total periode <strong><?= e(format_rupiah($total)) ?></strong></div></div>
<?php if (!$expenses): ?><div class="empty-state compact"><div class="empty-icon">−</div><h3>Belum ada pengeluaran pada periode ini</h3><p>Ubah tanggal atau catat pengeluaran baru.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Keterangan</th><th class="align-right">Nominal</th><th></th></tr></thead><tbody>
<?php foreach ($expenses as $expense): ?><tr><td><?= e((new DateTimeImmutable($expense['expense_date']))->format('d M Y')) ?></td><td><span class="category-chip"><?= e($expense['category']) ?></span></td><td><?= e($expense['description']) ?></td><td class="muted"><?= e($expense['notes'] ?: '—') ?></td><td class="align-right amount amount-expense"><?= e(format_rupiah($expense['amount'])) ?></td><td><a class="text-link" href="<?= e(url('expenses/edit.php?id=' . (int) $expense['id'])) ?>">Ubah</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php render_footer(); ?>
