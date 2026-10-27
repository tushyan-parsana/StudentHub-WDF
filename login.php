<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

function postValue(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

$email = strtolower(postValue('email'));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Submit the login form to continue.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $error = 'Enter a valid email address and password.';
} else {
    try {
        $statement = $conn->prepare(
            'SELECT student_id, password_hash
             FROM student_login
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute([':email' => $email]);
        $student = $statement->fetch();

        if (!$student || !password_verify($password, $student['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            session_start();
            $_SESSION['student_id'] = $student['student_id'];
            $conn->prepare(
                'UPDATE student_login SET last_login = CURRENT_TIMESTAMP
                 WHERE student_id = :student_id'
            )->execute([':student_id' => $student['student_id']]);
            header('Location: dashboard.html');
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
                <a class="forgot-password" href="login.html">Back to login</a>
            </div>
        </section>
    </main>
</body>
</html>
