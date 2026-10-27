<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

if (!empty($_SESSION['student_id'])) {
    $conn->prepare(
        'UPDATE student_login
         SET remember_token_hash = NULL, remember_expires_at = NULL
         WHERE student_id = :student_id'
    )->execute([':student_id' => $_SESSION['student_id']]);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '',
        (bool) $params['secure'], (bool) $params['httponly']);
}

session_destroy();
clearRememberCookie();
header('Location: ' . frontendUrl('frontend/src/login.html'));
exit;
