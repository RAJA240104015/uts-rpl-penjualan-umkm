<?php
declare(strict_types=1);

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';

if ($path === '/') {
    $path = '/index.php';
}

$pages = [
    '/index.php' => 'index.php',
    '/login.php' => 'login.php',
    '/logout.php' => 'logout.php',
    '/sales/index.php' => 'sales/index.php',
    '/sales/create.php' => 'sales/create.php',
    '/sales/edit.php' => 'sales/edit.php',
    '/sales/show.php' => 'sales/show.php',
    '/sales/delete.php' => 'sales/delete.php',
    '/expenses/index.php' => 'expenses/index.php',
    '/expenses/create.php' => 'expenses/create.php',
    '/expenses/edit.php' => 'expenses/edit.php',
    '/expenses/delete.php' => 'expenses/delete.php',
    '/reports/index.php' => 'reports/index.php',
    '/reports/export.php' => 'reports/export.php',
];

if (!isset($pages[$path])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Halaman tidak ditemukan.';
    exit;
}

require dirname(__DIR__) . '/public/' . $pages[$path];
