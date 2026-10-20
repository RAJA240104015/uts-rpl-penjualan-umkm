<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$errors = [];
$values = ['sale_date' => current_date(), 'description' => '', 'quantity' => '1', 'unit_price' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    foreach ($values as $key => $default) { $values[$key] = trim((string) ($_POST[$key] ?? $default)); }
    $description = $values['description'];
    $quantity = positive_integer($values['quantity'], 1000000);
    $price = positive_integer($values['unit_price'], 1000000000000);
    if (!is_valid_date($values['sale_date']) || $values['sale_date'] > current_date()) { $errors['sale_date'] = 'Tanggal harus valid dan tidak boleh melewati hari ini.'; }
    if ($description === '' || mb_strlen($description) > 160) { $errors['description'] = 'Deskripsi wajib diisi (maksimal 160 karakter).'; }
    if ($quantity === null) { $errors['quantity'] = 'Kuantitas harus bilangan bulat antara 1 dan 1.000.000.'; }
    if ($price === null) { $errors['unit_price'] = 'Harga harus bilangan rupiah positif yang valid.'; }
    if (!$errors) {
        $invoice = 'INV-' . (new DateTimeImmutable($values['sale_date']))->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $statement = db()->prepare('INSERT INTO sales (user_id, invoice_no, sale_date, description, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([current_user_id(), $invoice, $values['sale_date'], $description, $quantity, (string) $price . '.00']);
        redirect('sales/index.php?notice=sales-created');
    }
}
render_header('Catat penjualan');
page_heading('Transaksi baru', 'Catat penjualan', 'Subtotal dihitung otomatis dari kuantitas dan harga satuan.');
?>
<section class="panel form-panel"><form method="post" class="form-grid">
<?= csrf_field() ?>
<label>Tanggal<input type="date" name="sale_date" max="<?= e(current_date()) ?>" value="<?= e($values['sale_date']) ?>" required><?php field_error($errors, 'sale_date'); ?></label>
<label class="span-2">Deskripsi barang atau layanan<input name="description" maxlength="160" value="<?= e($values['description']) ?>" required placeholder="Contoh: Nasi ayam"><small>Setiap transaksi mencatat satu deskripsi.</small><?php field_error($errors, 'description'); ?></label>
<label>Kuantitas<input type="number" name="quantity" min="1" max="1000000" step="1" value="<?= e($values['quantity']) ?>" required><?php field_error($errors, 'quantity'); ?></label>
<label>Harga satuan (rupiah)<input type="number" name="unit_price" min="1" max="1000000000000" step="1" value="<?= e($values['unit_price']) ?>" required placeholder="Contoh: 15000"><?php field_error($errors, 'unit_price'); ?></label>
<div class="span-2 form-summary"><span>Subtotal</span><strong data-sale-subtotal>Rp 0</strong><small>Hasil hitung pratinjau. Server menghitung ulang saat disimpan.</small></div>
<div class="form-actions span-2"><a class="button button-quiet" href="<?= e(url('sales/index.php')) ?>">Batal</a><button class="button button-primary" type="submit">Simpan transaksi</button></div>
</form></section>
<?php render_footer(); ?>
