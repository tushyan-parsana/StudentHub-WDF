<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .
    DIRECTORY_SEPARATOR . 'studentHub' . DIRECTORY_SEPARATOR . 'config.php';

function postValue(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

$studentId = postValue('studentId');
$eventTitle = postValue('eventTitle');
$eventOrganiser = postValue('eventOrganiser');
$eventDate = postValue('eventDate');
$eventFees = postValue('eventFees');
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errors[] = 'Submit the event registration form to continue.';
} elseif (!preg_match('/^[A-Za-z0-9-]{2,30}$/', $studentId)) {
    $errors[] = 'Enter a valid student ID.';
} elseif ($eventTitle === '' || $eventOrganiser === '') {
    $errors[] = 'Event title and organiser are required.';
} elseif (!DateTime::createFromFormat('Y-m-d', $eventDate)) {
    $errors[] = 'Enter a valid event date.';
} elseif (!is_numeric($eventFees) || (float) $eventFees < 0) {
    $errors[] = 'Enter valid event fees.';
}

if (!$errors) {
    try {
        $statement = $conn->prepare(
            'INSERT INTO event_registration
                (student_id, event_title, event_organiser, event_date, event_fees)
             VALUES
                (:student_id, :event_title, :event_organiser, :event_date, :event_fees)'
        );
        $statement->execute([
            ':student_id' => $studentId,
            ':event_title' => $eventTitle,
            ':event_organiser' => $eventOrganiser,
            ':event_date' => $eventDate,
            ':event_fees' => (float) $eventFees
        ]);
        $success = true;
    } catch (PDOException $exception) {
        $errors[] = $exception->getCode() === '23000'
            ? 'The student ID was not found. Create a student account first.'
            : 'Event registration could not be saved. Please try again.';
        error_log('Event registration failed: ' . $exception->getMessage());
    }
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
    <title>Event Registration Result | StudentHub</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
    <main class="content">
        <section class="event-registration-section">
            <section class="event-registration-form" aria-live="polite">
                <h2><?= $success ? 'Registration successful' : 'Registration unsuccessful' ?></h2>
                <?php if ($success): ?>
                    <p class="event-registration-status success">
                        Your event registration was saved successfully.
                    </p>
                    <a href="calender.html">Register for another event</a>
                <?php else: ?>
                    <div class="event-registration-status error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= h($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a href="calender.html">Back to event registration</a>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
