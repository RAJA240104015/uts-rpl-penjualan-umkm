<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT id, invoice_no, sale_date, description, quantity, unit_price, subtotal, created_at, updated_at FROM sales WHERE id = ? AND user_id = ?');
$statement->execute([$id ?: 0, current_user_id()]);
$sale = $statement->fetch();
if (!$sale) { http_response_code(404); exit('Transaksi tidak ditemukan.'); }
render_header('Detail penjualan');
page_heading('Rincian transaksi', 'Detail penjualan', $sale['invoice_no'], 'Ubah transaksi', 'sales/edit.php?id=' . (int) $sale['id']);
?>
<section class="panel detail-panel"><div class="detail-grid"><div><span class="detail-label">Tanggal</span><strong><?= e((new DateTimeImmutable($sale['sale_date']))->format('d F Y')) ?></strong></div><div><span class="detail-label">Deskripsi</span><strong><?= e($sale['description']) ?></strong></div><div><span class="detail-label">Kuantitas</span><strong><?= (int) $sale['quantity'] ?></strong></div><div><span class="detail-label">Harga satuan</span><strong><?= e(format_rupiah($sale['unit_price'])) ?></strong></div></div><div class="detail-total"><span>Subtotal</span><strong><?= e(format_rupiah($sale['subtotal'])) ?></strong></div><div class="form-actions"><a class="button button-quiet" href="<?= e(url('sales/index.php')) ?>">Kembali</a><form method="post" action="<?= e(url('sales/delete.php')) ?>" data-confirm="Hapus transaksi ini? Perubahan tidak dapat dibatalkan."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $sale['id'] ?>"><button class="button button-danger" type="submit">Hapus transaksi</button></form></div></section>
<?php render_footer(); ?>
