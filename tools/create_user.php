<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap_env.php';
require_once dirname(__DIR__) . '/app/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function ask(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string) fgets(STDIN));
}

$name = ask('Nama pemilik akun: ');
$username = strtolower(ask('Username: '));
$password = ask('Password (minimal 12 karakter): ');
if ($name === '' || mb_strlen($name) > 100 || !preg_match('/^[a-z0-9._-]{3,50}$/', $username) || strlen($password) < 12) {
    fwrite(STDERR, "Data tidak valid. Nama wajib diisi, username 3-50 karakter, dan password minimal 12 karakter.\n");
    exit(1);
}
try {
    $statement = db()->prepare('INSERT INTO users (name, username, password_hash) VALUES (?, ?, ?)');
    $statement->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, 'Akun lokal berhasil dibuat dengan id ' . db()->lastInsertId() . ".\n");
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        fwrite(STDERR, "Username sudah digunakan.\n");
        exit(1);
    }
    throw $error;
}
