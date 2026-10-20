<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$errors = [];
$values = ['expense_date' => current_date(), 'category' => '', 'description' => '', 'amount' => '', 'notes' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    foreach ($values as $key => $default) { $values[$key] = trim((string) ($_POST[$key] ?? $default)); }
    $amount = positive_integer($values['amount'], 1000000000000);
    if (!is_valid_date($values['expense_date']) || $values['expense_date'] > current_date()) { $errors['expense_date'] = 'Tanggal harus valid dan tidak boleh melewati hari ini.'; }
    if ($values['category'] === '' || mb_strlen($values['category']) > 80) { $errors['category'] = 'Kategori wajib diisi (maksimal 80 karakter).'; }
    if ($values['description'] === '' || mb_strlen($values['description']) > 160) { $errors['description'] = 'Deskripsi wajib diisi (maksimal 160 karakter).'; }
    if ($amount === null) { $errors['amount'] = 'Nominal harus bilangan rupiah positif yang valid.'; }
    if (mb_strlen($values['notes']) > 5000) { $errors['notes'] = 'Keterangan maksimal 5.000 karakter.'; }
    if (!$errors) {
        $insert = db()->prepare('INSERT INTO expenses (user_id, expense_date, category, description, amount, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute([current_user_id(), $values['expense_date'], $values['category'], $values['description'], (string) $amount . '.00', $values['notes'] !== '' ? $values['notes'] : null]);
        redirect('expenses/index.php?notice=expense-created');
    }
}
render_header('Catat pengeluaran');
page_heading('Biaya baru', 'Catat pengeluaran', 'Simpan tanggal, kategori, nominal, dan keterangan biaya.');
?>
<section class="panel form-panel"><form method="post" class="form-grid">
<?= csrf_field() ?>
<label>Tanggal<input type="date" name="expense_date" max="<?= e(current_date()) ?>" value="<?= e($values['expense_date']) ?>" required><?php field_error($errors, 'expense_date'); ?></label>
<label>Kategori<input name="category" maxlength="80" value="<?= e($values['category']) ?>" required placeholder="Contoh: Bahan baku"><?php field_error($errors, 'category'); ?></label>
<label class="span-2">Deskripsi<input name="description" maxlength="160" value="<?= e($values['description']) ?>" required placeholder="Contoh: Pembelian sayuran"><?php field_error($errors, 'description'); ?></label>
<label>Nominal (rupiah)<input type="number" name="amount" min="1" max="1000000000000" step="1" value="<?= e($values['amount']) ?>" required placeholder="Contoh: 25000"><?php field_error($errors, 'amount'); ?></label>
<label class="span-2">Keterangan<textarea name="notes" rows="3" maxlength="5000" placeholder="Catatan tambahan (opsional)"><?= e($values['notes']) ?></textarea><?php field_error($errors, 'notes'); ?></label>
<div class="form-actions span-2"><a class="button button-quiet" href="<?= e(url('expenses/index.php')) ?>">Batal</a><button class="button button-primary" type="submit">Simpan pengeluaran</button></div>
</form></section>
<?php render_footer(); ?>
