<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

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
        $date = DateTime::createFromFormat('!d-m-Y', $eventDate);
    }
    if (!$date || !preg_match('/^(?:\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4})$/', $eventDate)) {
        $errors[] = 'Enter a complete date in DD-MM-YYYY or YYYY-MM-DD format.';
    } else {
        $eventDate = $date->format('Y-m-d');
    }
    if (!is_numeric($eventFees) || (float) $eventFees < 0 || (float) $eventFees > 100000) {
        $errors[] = 'Enter event fees between ₹0 and ₹100000.';
    }
}

if (!$errors) {
    try {
        $studentCheck = $conn->prepare(
            'SELECT student_id FROM students
             WHERE student_id = :student_id LIMIT 1'
        );
        $studentCheck->execute([':student_id' => $studentId]);

        if (!$studentCheck->fetch()) {
            $errors[] = 'That student ID is not registered.';
        }

        if (!$errors) {
            $conn->beginTransaction();

            $eventCheck = $conn->prepare(
                'SELECT event_id FROM events
                 WHERE event_title = :event_title
                   AND event_organiser = :event_organiser
                   AND event_date = :event_date
                   AND event_fees = :event_fees
                 LIMIT 1'
            );
            $eventCheck->execute([
                ':event_title' => $eventTitle,
                ':event_organiser' => $eventOrganiser,
                ':event_date' => $eventDate,
                ':event_fees' => round((float) $eventFees, 2)
            ]);
            $event = $eventCheck->fetch();

            if (!$event) {
                $eventInsert = $conn->prepare(
                    'INSERT INTO events
                     (event_title, event_organiser, event_date, event_fees)
                     VALUES (:event_title, :event_organiser, :event_date, :event_fees)'
                );
                $eventInsert->execute([
                    ':event_title' => $eventTitle,
                    ':event_organiser' => $eventOrganiser,
                    ':event_date' => $eventDate,
                    ':event_fees' => round((float) $eventFees, 2)
                ]);
                $eventId = (int) $conn->lastInsertId();
            } else {
                $eventId = (int) $event['event_id'];
            }

            $registrationInsert = $conn->prepare(
                'INSERT INTO event_registration (student_id, event_id)
                 VALUES (:student_id, :event_id)'
            );
            $registrationInsert->execute([
                ':student_id' => $studentId,
                ':event_id' => $eventId
            ]);

            $conn->commit();
            $success = true;
        }
    } catch (PDOException $exception) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        if (($exception->errorInfo[0] ?? '') === '23000') {
            $errors[] = 'This student is already registered for that event.';
        } else {
            $errors[] = 'Event registration could not be saved. Please try again.';
            error_log('Event registration database insert failed: ' . $exception->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Result | StudentHub</title>
    <link rel="stylesheet" href="<?= h(frontendUrl('frontend/src/styles/registration.css')) ?>">
</head>
<body>
    <main class="registration-layout">
        <section class="registration-branding">
            <h1>StudentHub</h1>
            <p>Event registration stored securely in the StudentHub database.</p>
        </section>
        <section class="registration-panel">
            <section class="registration-card" aria-live="polite">
                <h2><?= $success ? 'Registration successful' : 'Registration unsuccessful' ?></h2>
                <?php if ($success): ?>
                    <p class="success-message">Your event registration was saved successfully.</p>
                    <a class="back-link" href="<?= h(frontendUrl('frontend/src/calender.html')) ?>">Register for another event</a>
                <?php else: ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?= h($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a class="back-link" href="<?= h(frontendUrl('frontend/src/calender.html')) ?>">Back to event registration</a>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
