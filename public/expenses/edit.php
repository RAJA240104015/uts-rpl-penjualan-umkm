<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT id, expense_date, category, description, amount, notes FROM expenses WHERE id = ? AND user_id = ?');
$statement->execute([$id ?: 0, current_user_id()]);
$expense = $statement->fetch();
if (!$expense) { http_response_code(404); exit('Pengeluaran tidak ditemukan.'); }
$values = ['expense_date' => $expense['expense_date'], 'category' => $expense['category'], 'description' => $expense['description'], 'amount' => (string) (int) $expense['amount'], 'notes' => $expense['notes'] ?? ''];
$errors = [];
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
        $update = db()->prepare('UPDATE expenses SET expense_date = ?, category = ?, description = ?, amount = ?, notes = ? WHERE id = ? AND user_id = ?');
        $update->execute([$values['expense_date'], $values['category'], $values['description'], (string) $amount . '.00', $values['notes'] !== '' ? $values['notes'] : null, $expense['id'], current_user_id()]);
        redirect('expenses/index.php?notice=expense-updated');
    }
}
render_header('Ubah pengeluaran');
page_heading('Perbarui catatan biaya', 'Ubah pengeluaran', $expense['description']);
?>
<section class="panel form-panel"><form method="post" class="form-grid" id="expense-edit">
<?= csrf_field() ?>
<label>Tanggal<input type="date" name="expense_date" max="<?= e(current_date()) ?>" value="<?= e($values['expense_date']) ?>" required><?php field_error($errors, 'expense_date'); ?></label>
<label>Kategori<input name="category" maxlength="80" value="<?= e($values['category']) ?>" required><?php field_error($errors, 'category'); ?></label>
<label class="span-2">Deskripsi<input name="description" maxlength="160" value="<?= e($values['description']) ?>" required><?php field_error($errors, 'description'); ?></label>
<label>Nominal (rupiah)<input type="number" name="amount" min="1" max="1000000000000" step="1" value="<?= e($values['amount']) ?>" required><?php field_error($errors, 'amount'); ?></label>
<label class="span-2">Keterangan<textarea name="notes" rows="3" maxlength="5000"><?= e($values['notes']) ?></textarea><?php field_error($errors, 'notes'); ?></label>
</form><div class="edit-actions"><a class="button button-quiet" href="<?= e(url('expenses/index.php')) ?>">Batal</a><form method="post" action="<?= e(url('expenses/delete.php')) ?>" class="delete-action" data-confirm="Hapus catatan pengeluaran ini? Perubahan tidak dapat dibatalkan."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $expense['id'] ?>"><button class="button button-danger" type="submit">Hapus pengeluaran</button></form><button class="button button-primary" type="submit" form="expense-edit">Simpan perubahan</button></div></section>
<?php render_footer(); ?>
