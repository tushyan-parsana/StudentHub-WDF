<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'storage.php';

$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'registrations.json';
$records = [];
$error = '';

try {
    $records = readJsonRecords($filePath);
} catch (RuntimeException $exception) {
    $error = 'Stored records could not be read.';
    error_log('Registration records read failed: ' . $exception->getMessage());
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stored Registrations | StudentHub</title>
    <link rel="stylesheet" href="styles/registration.css">
</head>
<body>
    <main class="registration-layout">
        <section class="registration-branding">
            <h1>StudentHub</h1>
            <p>Intermediate extension: display records saved in JSON.</p>
        </section>
        <section class="registration-panel">
            <section class="registration-card">
                <h2>Stored student registrations</h2>
                <?php if ($error): ?>
                    <p class="error-message"><?= h($error) ?></p>
                <?php elseif (!$records): ?>
                    <p class="form-intro">No registrations have been stored yet.</p>
                <?php else: ?>
                    <div style="overflow-x:auto">
                        <table>
                            <thead><tr><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Registered</th></tr></thead>
                            <tbody>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?= h((string) ($record['studentId'] ?? '')) ?></td>
                                    <td><?= h((string) ($record['fullName'] ?? '')) ?></td>
                                    <td><?= h((string) ($record['email'] ?? '')) ?></td>
                                    <td><?= h((string) ($record['course'] ?? '')) ?></td>
                                    <td><?= h((string) ($record['year'] ?? '')) ?></td>
                                    <td><?= h((string) ($record['registeredAt'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <a class="back-link" href="registration.html">Back to registration</a>
            </section>
        </section>
    </main>
</body>
</html>
