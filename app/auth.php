<?php
declare(strict_types=1);

function auth_key(): string
{
    $key = (string) (getenv('APP_KEY') ?: '');
    if (strlen($key) < 32) {
        throw new RuntimeException('APP_KEY belum diatur dengan nilai acak minimal 32 karakter.');
    }
    return $key;
}

function current_user(): ?array
{
    static $resolved = false;
    static $user = null;
    if ($resolved) {
        return $user;
    }
    $resolved = true;
    $token = $_COOKIE['umkm_auth'] ?? '';
    if (!is_string($token) || substr_count($token, '.') !== 4) {
        return null;
    }
    [$idText, $versionText, $expiresText, $nonce, $signature] = explode('.', $token, 5);
    if (!ctype_digit($idText) || !ctype_digit($versionText) || !ctype_digit($expiresText)
        || !preg_match('/^[a-f0-9]{48}$/', $nonce) || !preg_match('/^[a-f0-9]{64}$/', $signature)) {
        return null;
    }
    $payload = $idText . '.' . $versionText . '.' . $expiresText . '.' . $nonce;
    $expected = hash_hmac('sha256', $payload, auth_key());
    if (!hash_equals($expected, $signature) || (int) $expiresText < time()) {
        return null;
    }
    $statement = db()->prepare('SELECT id, name, username, auth_version FROM users WHERE id = ? LIMIT 1');
    $statement->execute([(int) $idText]);
    $candidate = $statement->fetch();
    if (!$candidate || (int) $candidate['auth_version'] !== (int) $versionText) {
        return null;
    }
    $user = $candidate;
    return $user;
}

function set_auth_cookie(int $userId, int $version): void
{
    $expires = time() + 604800;
    $nonce = bin2hex(random_bytes(24));
    $payload = $userId . '.' . $version . '.' . $expires . '.' . $nonce;
    $token = $payload . '.' . hash_hmac('sha256', $payload, auth_key());
    setcookie('umkm_auth', $token, [
        'expires' => $expires,
        'path' => '/',
        'secure' => request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clear_auth_cookie(): void
{
    setcookie('umkm_auth', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function require_auth(): void
{
    if (current_user() === null) {
        redirect('login.php');
    }
}
