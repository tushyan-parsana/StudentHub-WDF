<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

function postValue(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

$email = strtolower(postValue('email'));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$rememberMe = isset($_POST['remember_me']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Submit the login form to continue.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $error = 'Enter a valid email address and password.';
} else {
    try {
        $statement = $conn->prepare(
            'SELECT sl.student_id, sl.password_hash, s.full_name, s.role
             FROM student_login AS sl
             INNER JOIN students AS s ON s.student_id = sl.student_id
             WHERE sl.email = :email
             LIMIT 1'
        );
        $statement->execute([':email' => $email]);
        $student = $statement->fetch();

        if (!$student || !password_verify($password, $student['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['full_name'] = $student['full_name'];
            $_SESSION['role'] = $student['role'];
            $_SESSION['last_activity'] = time();

            if ($rememberMe) {
                $rememberToken = bin2hex(random_bytes(32));
                $conn->prepare(
                    'UPDATE student_login
                     SET remember_token_hash = :token_hash,
                         remember_expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
                     WHERE student_id = :student_id'
                )->execute([
                    ':token_hash' => hash('sha256', $rememberToken),
                    ':student_id' => $student['student_id']
                ]);
                setcookie('studenthub_remember', $rememberToken, [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            $conn->prepare(
                'UPDATE student_login SET last_login = CURRENT_TIMESTAMP
                 WHERE student_id = :student_id'
            )->execute([':student_id' => $student['student_id']]);
            $destination = $student['role'] === 'admin'
                ? 'frontend/src/admin.html'
                : 'frontend/src/dashboard.html';
            header('Location: ' . frontendUrl($destination));
            exit;
        }
    } catch (PDOException $exception) {
        $error = 'Login is temporarily unavailable. Please try again.';
        error_log('Student login failed: ' . $exception->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Result | StudentHub</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <main class="container">
        <section class="right-panel">
            <div class="login-card">
                <h2>Login unsuccessful</h2>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <a class="forgot-password" href="<?= htmlspecialchars(frontendUrl('frontend/src/login.html'), ENT_QUOTES, 'UTF-8') ?>">Back to login</a>
            </div>
        </section>
    </main>
</body>
</html>
