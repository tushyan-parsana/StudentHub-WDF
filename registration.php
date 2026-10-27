<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

function postValue(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = false;
$studentId = postValue('studentId');
$fullName = postValue('fullName');
$email = strtolower(postValue('email'));
$mobile = postValue('mobile');
$gender = postValue('gender');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$confirmPassword = is_string($_POST['confirmPassword'] ?? null) ? $_POST['confirmPassword'] : '';
$termsAccepted = isset($_POST['terms']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errors[] = 'Submit the registration form to continue.';
} else {
    if (!preg_match('/^[A-Za-z0-9-]{2,30}$/', $studentId)) {
        $errors[] = 'Student ID must contain 2-30 letters, numbers, or hyphens.';
    }
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,49}$/", $fullName)) {
        $errors[] = 'Enter a valid full name with at least 3 letters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile number must contain exactly 10 digits.';
    }
    if (!in_array($gender, ['female', 'male', 'other'], true)) {
        $errors[] = 'Select a gender.';
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password)) {
        $errors[] = 'Password must contain 8+ characters, uppercase, lowercase, a number, and a special character.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    if (!$termsAccepted) {
        $errors[] = 'Accept the terms and conditions to continue.';
    }
}

if (!$errors) {
    try {
        $duplicateCheck = $conn->prepare(
            'SELECT student_id, email
             FROM students
             WHERE student_id = :student_id OR email = :email
             LIMIT 2'
        );
        $duplicateCheck->execute([
            ':student_id' => $studentId,
            ':email' => $email
        ]);

        foreach ($duplicateCheck->fetchAll() as $record) {
            if (($record['student_id'] ?? '') === $studentId) {
                $errors[] = 'That student ID is already registered.';
            }
            if (strtolower((string) ($record['email'] ?? '')) === $email) {
                $errors[] = 'That email address is already registered.';
            }
        }

        if (!$errors) {
            $conn->beginTransaction();

            $studentInsert = $conn->prepare(
                'INSERT INTO students (student_id, full_name, email, mobile, gender)
                 VALUES (:student_id, :full_name, :email, :mobile, :gender)'
            );
            $studentInsert->execute([
                ':student_id' => $studentId,
                ':full_name' => $fullName,
                ':email' => $email,
                ':mobile' => $mobile,
                ':gender' => ucfirst($gender)
            ]);

            $loginInsert = $conn->prepare(
                'INSERT INTO student_login (student_id, email, password_hash)
                 VALUES (:student_id, :email, :password_hash)'
            );
            $loginInsert->execute([
                ':student_id' => $studentId,
                ':email' => $email,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $conn->commit();
            $success = true;
        }
    } catch (PDOException $exception) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        if ($exception->errorInfo[0] === '23000') {
            $errors[] = 'That student ID or email address is already registered.';
        } else {
            $errors[] = 'Registration could not be saved. Please try again.';
            error_log('Student registration database insert failed: ' . $exception->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Result | StudentHub</title>
    <link rel="stylesheet" href="styles/registration.css">
</head>
<body>
    <main class="registration-layout">
        <section class="registration-branding" aria-label="StudentHub introduction">
            <h1>StudentHub</h1>
            <p>Practical 9: Secure registration with database insert, duplicate checks, and password hashing.</p>
        </section>
        <section class="registration-panel">
            <section class="registration-card" aria-live="polite">
                <h2><?= $success ? 'Registration successful' : 'Registration unsuccessful' ?></h2>
                <?php if ($success): ?>
                    <p class="success-message">Your account was validated and stored securely.</p>
                    <a class="back-link" href="login.html">Continue to login</a>
                <?php else: ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?= h($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a class="back-link" href="registration.html">Back to registration form</a>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
