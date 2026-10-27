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

$studentId = postValue('studentId');
$eventTitle = postValue('eventTitle');
$eventOrganiser = postValue('eventOrganiser');
$eventDate = postValue('eventDate');
$eventFees = postValue('eventFees');
$errors = [];
$success = false;
$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'event-registrations.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errors[] = 'Submit the event registration form to continue.';
} else {
    if (!preg_match('/^[A-Za-z0-9-]{2,30}$/', $studentId)) {
        $errors[] = 'Enter a valid student ID.';
    }
    if ($eventTitle === '' || !preg_match('/^[\p{L}0-9][\p{L}0-9 .,&()\'-]{1,99}$/u', $eventTitle)) {
        $errors[] = 'Enter a valid event title.';
    }
    if ($eventOrganiser === '' || !preg_match('/^[\p{L}][\p{L} .&\'-]{1,79}$/u', $eventOrganiser)) {
        $errors[] = 'Enter a valid event organiser.';
    }
    $date = DateTime::createFromFormat('!Y-m-d', $eventDate);
    if (!$date || $date->format('Y-m-d') !== $eventDate) {
        $errors[] = 'Enter a valid event date.';
    }
    if (!is_numeric($eventFees) || (float) $eventFees < 0 || (float) $eventFees > 100000) {
        $errors[] = 'Enter event fees between ₹0 and ₹100000.';
    }
}

if (!$errors) {
    try {
        appendJsonRecord($filePath, [
            'studentId' => $studentId,
            'eventTitle' => $eventTitle,
            'eventOrganiser' => $eventOrganiser,
            'eventDate' => $eventDate,
            'eventFees' => round((float) $eventFees, 2),
            'registeredAt' => date(DATE_ATOM)
        ]);
        $success = true;
    } catch (JsonException | RuntimeException $exception) {
        $errors[] = 'Event registration could not be saved. Please try again.';
        error_log('Event registration storage failed: ' . $exception->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Result | StudentHub</title>
    <link rel="stylesheet" href="styles/registration.css">
</head>
<body>
    <main class="registration-layout">
        <section class="registration-branding">
            <h1>StudentHub</h1>
            <p>Practical 7: server-side validation and JSON file storage.</p>
        </section>
        <section class="registration-panel">
            <section class="registration-card" aria-live="polite">
                <h2><?= $success ? 'Registration successful' : 'Registration unsuccessful' ?></h2>
                <?php if ($success): ?>
                    <p class="success-message">Your event registration was saved successfully.</p>
                    <a class="back-link" href="calender.html">Register for another event</a>
                <?php else: ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?= h($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a class="back-link" href="calender.html">Back to event registration</a>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
