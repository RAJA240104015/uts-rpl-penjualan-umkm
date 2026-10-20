<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT id, invoice_no, sale_date, description, quantity, unit_price FROM sales WHERE id = ? AND user_id = ?');
$statement->execute([$id ?: 0, current_user_id()]);
$sale = $statement->fetch();
if (!$sale) { http_response_code(404); exit('Transaksi tidak ditemukan.'); }
$values = ['sale_date' => $sale['sale_date'], 'description' => $sale['description'], 'quantity' => (string) $sale['quantity'], 'unit_price' => (string) (int) $sale['unit_price']];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    foreach ($values as $key => $default) { $values[$key] = trim((string) ($_POST[$key] ?? $default)); }
    $quantity = positive_integer($values['quantity'], 1000000);
    $price = positive_integer($values['unit_price'], 1000000000000);
    if (!is_valid_date($values['sale_date']) || $values['sale_date'] > current_date()) { $errors['sale_date'] = 'Tanggal harus valid dan tidak boleh melewati hari ini.'; }
    if ($values['description'] === '' || mb_strlen($values['description']) > 160) { $errors['description'] = 'Deskripsi wajib diisi (maksimal 160 karakter).'; }
    if ($quantity === null) { $errors['quantity'] = 'Kuantitas harus bilangan bulat positif.'; }
    if ($price === null) { $errors['unit_price'] = 'Harga harus bilangan rupiah positif yang valid.'; }
    if (!$errors) {
        $update = db()->prepare('UPDATE sales SET sale_date = ?, description = ?, quantity = ?, unit_price = ? WHERE id = ? AND user_id = ?');
        $update->execute([$values['sale_date'], $values['description'], $quantity, (string) $price . '.00', $sale['id'], current_user_id()]);
        redirect('sales/index.php?notice=sales-updated');
    }
}
render_header('Ubah penjualan');
page_heading('Perbarui transaksi', 'Ubah penjualan', 'Nomor transaksi: ' . $sale['invoice_no']);
?>
<section class="panel form-panel"><form method="post" class="form-grid">
<?= csrf_field() ?>
<label>Tanggal<input type="date" name="sale_date" max="<?= e(current_date()) ?>" value="<?= e($values['sale_date']) ?>" required><?php field_error($errors, 'sale_date'); ?></label>
<label class="span-2">Deskripsi barang atau layanan<input name="description" maxlength="160" value="<?= e($values['description']) ?>" required><?php field_error($errors, 'description'); ?></label>
<label>Kuantitas<input type="number" name="quantity" min="1" max="1000000" step="1" value="<?= e($values['quantity']) ?>" required><?php field_error($errors, 'quantity'); ?></label>
<label>Harga satuan (rupiah)<input type="number" name="unit_price" min="1" max="1000000000000" step="1" value="<?= e($values['unit_price']) ?>" required><?php field_error($errors, 'unit_price'); ?></label>
<div class="form-actions span-2"><a class="button button-quiet" href="<?= e(url('sales/show.php?id=' . (int) $sale['id'])) ?>">Batal</a><button class="button button-primary" type="submit">Simpan perubahan</button></div>
</form></section>
<?php render_footer(); ?>
