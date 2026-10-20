<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function format_rupiah(string|int|null $amount): string
{
    $value = (string) ($amount ?? '0');
    if (!preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $value, $matches)) {
        return 'Rp 0';
    }
    $sign = $matches[1] === '-' ? '-' : '';
    $whole = ltrim($matches[2], '0');
    $whole = $whole === '' ? '0' : $whole;
    $formatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole) ?: '0';
    $fraction = str_pad($matches[3] ?? '', 2, '0');
    return 'Rp ' . $sign . $formatted . ($fraction !== '00' ? ',' . $fraction : '');
}

function csv_safe_cell(string $value): string
{
    return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
}

function is_valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function valid_period(string $from, string $to): bool
{
    return is_valid_date($from) && is_valid_date($to) && $from <= $to;
}

function csrf_token(): string
{
    $name = 'umkm_csrf';
    $token = $_COOKIE[$name] ?? '';
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        $token = bin2hex(random_bytes(32));
        setcookie($name, $token, [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => request_is_secure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$name] = $token;
    }
    return $token;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function require_valid_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $cookie = $_COOKIE['umkm_csrf'] ?? '';
    if (!is_string($sent) || !is_string($cookie) || $sent === '' || !hash_equals($cookie, $sent)) {
        http_response_code(419);
        exit('Permintaan tidak dapat diverifikasi. Muat ulang halaman, lalu coba kembali.');
    }
}

function request_is_secure(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function current_date(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
}

function status_notice(): ?array
{
    $messages = [
        'sales-created' => ['success', 'Transaksi penjualan berhasil dicatat.'],
        'sales-updated' => ['success', 'Transaksi penjualan berhasil diperbarui.'],
        'sales-deleted' => ['success', 'Transaksi penjualan berhasil dihapus.'],
        'expense-created' => ['success', 'Pengeluaran berhasil dicatat.'],
        'expense-updated' => ['success', 'Pengeluaran berhasil diperbarui.'],
        'expense-deleted' => ['success', 'Pengeluaran berhasil dihapus.'],
        'logged-out' => ['success', 'Anda telah keluar dari aplikasi.'],
    ];
    $key = $_GET['notice'] ?? '';
    return is_string($key) && isset($messages[$key]) ? $messages[$key] : null;
}

function render_notice(): void
{
    $notice = status_notice();
    if ($notice === null) {
        return;
    }
    printf('<div class="notice notice-%s" role="status">%s</div>', e($notice[0]), e($notice[1]));
}

function positive_integer(string $value, int $maximum): ?int
{
    if (!preg_match('/^[0-9]+$/', $value)) {
        return null;
    }
    $normalized = ltrim($value, '0');
    $normalized = $normalized === '' ? '0' : $normalized;
    if (strlen($normalized) > strlen((string) $maximum) || (strlen($normalized) === strlen((string) $maximum) && strcmp($normalized, (string) $maximum) > 0)) {
        return null;
    }
    $number = (int) $normalized;
    return $number > 0 ? $number : null;
}

function current_user_id(): int
{
    return (int) (current_user()['id'] ?? 0);
}
