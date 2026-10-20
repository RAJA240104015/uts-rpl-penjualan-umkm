<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap_env.php';
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Terjadi kendala</title><body><main><h1>Terjadi kendala saat memproses permintaan.</h1><p>Periksa konfigurasi aplikasi atau coba kembali.</p></main></body></html>';
});
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
