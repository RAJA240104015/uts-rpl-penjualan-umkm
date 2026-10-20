<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
if (current_user() !== null) {
    redirect('index.php');
}

$errors = [];
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $username = strtolower(trim((string) ($_POST['username'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($username === '' || strlen($username) > 50 || $password === '') {
        $errors['form'] = 'Masukkan username dan password.';
    } else {
        $statement = db()->prepare('SELECT id, name, username, password_hash, auth_version FROM users WHERE username = ? LIMIT 1');
        $statement->execute([$username]);
        $account = $statement->fetch();
        if ($account && password_verify($password, $account['password_hash'])) {
            if (password_needs_rehash($account['password_hash'], PASSWORD_DEFAULT)) {
                $update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $account['id']]);
            }
            set_auth_cookie((int) $account['id'], (int) $account['auth_version']);
            redirect('index.php');
        }
        $errors['form'] = 'Username atau password tidak sesuai.';
    }
}

render_header('Masuk');
?>
<section class="login-layout">
    <div class="login-story">
        <p class="eyebrow">Pencatatan UMKM</p>
        <h1>Catatan usaha yang rapi, keputusan yang lebih jelas.</h1>
        <p class="muted">Simpan transaksi penjualan dan pengeluaran, lalu lihat ringkasannya dalam satu tempat.</p>
        <div class="story-note"><span class="story-dot"></span><span>Ringkasan sederhana untuk kegiatan harian.</span></div>
    </div>
    <div class="login-card">
        <p class="eyebrow">Selamat datang</p>
        <h2>Masuk ke akun</h2>
        <p class="muted">Gunakan akun yang dibuat oleh pengelola aplikasi.</p>
        <?php if (isset($errors['form'])): ?><div class="notice notice-error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="form-stack" autocomplete="on">
            <?= csrf_field() ?>
            <label>Username<input name="username" autocomplete="username" required maxlength="50" value="<?= e($username) ?>"></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button button-primary button-wide" type="submit">Masuk</button>
        </form>
        <p class="form-footnote">Akun dibuat melalui utilitas lokal pengelola.</p>
    </div>
</section>
<?php render_footer(); ?>
