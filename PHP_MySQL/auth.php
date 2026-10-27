<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

const SESSION_TIMEOUT = 900;
const FRONTEND_FALLBACK = 'http://localhost:5500';

function frontendOrigin(): string
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $origin = $referer !== '' ? parse_url($referer, PHP_URL_SCHEME) . '://' . parse_url($referer, PHP_URL_HOST) : '';
    $port = $referer !== '' ? parse_url($referer, PHP_URL_PORT) : null;

    if (!in_array($origin, ['http://localhost', 'http://127.0.0.1'], true)) {
        return FRONTEND_FALLBACK;
    }

    return $origin . ($port ? ':' . $port : '');
}

function frontendUrl(string $path): string
{
    return rtrim(frontendOrigin(), '/') . '/' . ltrim($path, '/');
}

function clearRememberCookie(): void
{
    setcookie('studenthub_remember', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function restoreRememberedSession(): void
{
    global $conn;

    if (isset($_SESSION['student_id']) || empty($_COOKIE['studenthub_remember'])) {
        return;
    }

    $statement = $conn->prepare(
        'SELECT sl.student_id, s.full_name, s.role
         FROM student_login AS sl
         INNER JOIN students AS s ON s.student_id = sl.student_id
         WHERE sl.remember_token_hash = :token_hash
           AND sl.remember_expires_at > CURRENT_TIMESTAMP
         LIMIT 1'
    );
    $statement->execute([':token_hash' => hash('sha256', (string) $_COOKIE['studenthub_remember'])]);
    $student = $statement->fetch();

    if (!$student) {
        clearRememberCookie();
        return;
    }

    session_regenerate_id(true);
    $_SESSION['student_id'] = $student['student_id'];
    $_SESSION['full_name'] = $student['full_name'];
    $_SESSION['role'] = $student['role'];
    $_SESSION['last_activity'] = time();
}

function sessionIsValid(): bool
{
    if (empty($_SESSION['student_id'])) {
        return false;
    }

    if (time() - (int) ($_SESSION['last_activity'] ?? 0) > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        clearRememberCookie();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

try {
    restoreRememberedSession();
} catch (PDOException $exception) {
    clearRememberCookie();
    error_log('Remember-me restore failed: ' . $exception->getMessage());
}
