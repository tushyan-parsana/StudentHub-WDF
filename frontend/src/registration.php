<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'storage.php';

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
$course = postValue('course');
$year = postValue('year');
$gender = postValue('gender');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$confirmPassword = is_string($_POST['confirmPassword'] ?? null) ? $_POST['confirmPassword'] : '';
$termsAccepted = isset($_POST['terms']);
$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'registrations.json';

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
    if (!in_array($course, ['bca', 'btech', 'bba', 'other'], true)) {
        $errors[] = 'Select a valid course.';
    }
    if (!in_array($year, ['1', '2', '3', '4'], true)) {
        $errors[] = 'Select a valid study year.';
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
        $records = readJsonRecords($filePath);
        foreach ($records as $record) {
            if (($record['studentId'] ?? '') === $studentId) {
                $errors[] = 'That student ID is already registered.';
            }
            if (strtolower((string) ($record['email'] ?? '')) === $email) {
                $errors[] = 'That email address is already registered.';
            }
        }

        if (!$errors) {
            appendJsonRecord($filePath, [
                'studentId' => $studentId,
                'fullName' => $fullName,
                'email' => $email,
                'mobile' => $mobile,
                'course' => strtoupper($course),
                'year' => (int) $year,
                'gender' => ucfirst($gender),
                'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
                'registeredAt' => date(DATE_ATOM)
            ]);
            $success = true;
        }
    } catch (JsonException | RuntimeException $exception) {
        $errors[] = 'Registration could not be saved. Please try again.';
        error_log('Student registration storage failed: ' . $exception->getMessage());
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
            <p>Practical 7: PHP form processing with server-side validation and JSON storage.</p>
        </section>
        <section class="registration-panel">
            <section class="registration-card" aria-live="polite">
                <h2><?= $success ? 'Registration successful' : 'Registration unsuccessful' ?></h2>
                <?php if ($success): ?>
                    <p class="success-message">Your account was validated and stored securely.</p>
                    <a class="back-link" href="registration-records.php">View stored JSON records</a>
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
