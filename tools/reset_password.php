<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/bootstrap_env.php';
require_once dirname(__DIR__) . '/app/db.php';
if (PHP_SAPI !== 'cli' || !isset($argv[1]) || !preg_match('/^[a-z0-9._-]{3,50}$/', $argv[1])) {
    fwrite(STDERR, "Penggunaan: php tools/reset_password.php <username>\n");
    exit(1);
}
fwrite(STDOUT, 'Password baru (minimal 12 karakter): ');
$password = trim((string) fgets(STDIN));
if (strlen($password) < 12) {
    fwrite(STDERR, "Password minimal 12 karakter.\n");
    exit(1);
}
$statement = db()->prepare('UPDATE users SET password_hash = ?, auth_version = auth_version + 1 WHERE username = ?');
$statement->execute([password_hash($password, PASSWORD_DEFAULT), strtolower($argv[1])]);
if ($statement->rowCount() !== 1) {
    fwrite(STDERR, "Username tidak ditemukan.\n");
    exit(1);
}
fwrite(STDOUT, "Password berhasil diperbarui. Sesi lama telah dicabut.\n");
