<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
require_valid_csrf();
$user = current_user();
if ($user !== null) {
    $statement = db()->prepare('UPDATE users SET auth_version = auth_version + 1 WHERE id = ?');
    $statement->execute([(int) $user['id']]);
}
clear_auth_cookie();
redirect('login.php?notice=logged-out');
