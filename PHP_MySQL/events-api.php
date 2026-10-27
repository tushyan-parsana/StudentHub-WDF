<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'mysqli-config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'audit.php';

$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if (in_array($origin, ['http://localhost:5500', 'http://127.0.0.1:5500'], true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (!sessionIsValid()) {
    respond(['success' => false, 'message' => 'Login is required.'], 401);
}

$role = $_SESSION['role'] ?? 'student';
$studentId = (string) ($_SESSION['student_id'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];
$transactionStarted = false;

function inputData(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);

    if (is_array($data) && $data) {
        return $data;
    }

    return is_array($_POST) ? $_POST : [];
}

function eventData(array $data): array
{
    $title = trim((string) ($data['event_title'] ?? ''));
    $organiser = trim((string) ($data['event_organiser'] ?? ''));
    $date = trim((string) ($data['event_date'] ?? ''));
    $fees = (string) ($data['event_fees'] ?? '');
    $category = trim((string) ($data['category'] ?? 'General'));
    $status = strtolower(trim((string) ($data['status'] ?? 'open')));
    $errors = [];

    if (!preg_match("/^[A-Za-z][A-Za-z0-9 .,&()'-]{1,99}$/", $title)) {
        $errors[] = 'Enter a valid event title.';
    }
    if (!preg_match("/^[A-Za-z][A-Za-z .&'-]{1,79}$/", $organiser)) {
        $errors[] = 'Enter a valid event organiser.';
    }
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
        $parsedDate = DateTime::createFromFormat('!d-m-Y', $date);
    }
    if (!$parsedDate || !preg_match('/^(?:\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4})$/', $date)) {
        $errors[] = 'Enter a complete event date in DD-MM-YYYY or YYYY-MM-DD format.';
    } else {
        $date = $parsedDate->format('Y-m-d');
    }
    if (!is_numeric($fees) || (float) $fees < 0 || (float) $fees > 100000) {
        $errors[] = 'Event fees must be between 0 and 100000.';
    }
    if (!preg_match('/^[A-Za-z][A-Za-z &-]{1,39}$/', $category)) {
        $errors[] = 'Enter a valid event category.';
    }
    if (!in_array($status, ['open', 'closed'], true)) {
        $errors[] = 'Select a valid event status.';
    }

    return [
        'data' => [$title, $organiser, $date, round((float) $fees, 2), $category, $status],
        'errors' => $errors
    ];
}

function savePoster(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The poster upload failed.');
    }
    if ((int) $file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Poster size must not exceed 2 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Poster must be a valid JPG, PNG, or WebP image.');
    }

    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'event-posters';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Poster upload directory could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Poster could not be saved.');
    }
    return 'uploads/event-posters/' . $filename;
}

function removePoster(?string $path): void
{
    if ($path && str_starts_with($path, 'uploads/event-posters/')) {
        @unlink(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    }
}

try {
    if ($method === 'GET') {
        if ($role === 'admin') {
            $statement = $mysqli->prepare(
                'SELECT e.event_id, e.event_title, e.event_organiser,
                    e.event_date, e.event_fees, e.category, e.status,
                    e.poster_path,
                        COUNT(er.registration_id) AS registrations_count
                 FROM events AS e
                 LEFT JOIN event_registration AS er
                    ON er.event_id = e.event_id
                 GROUP BY e.event_id, e.event_title, e.event_organiser,
                          e.event_date, e.event_fees, e.category, e.status,
                          e.poster_path
                 ORDER BY e.event_date ASC, e.event_id DESC'
            );
            $statement->execute();
        } else {
            $statement = $mysqli->prepare(
                'SELECT e.event_id, e.event_title, e.event_organiser,
                    e.event_date, e.event_fees, e.category, e.status,
                    e.poster_path,
                        COUNT(all_reg.registration_id) AS registrations_count,
                        CASE WHEN own_reg.registration_id IS NULL
                             THEN 0 ELSE 1 END AS registered,
                        own_reg.registration_id
                 FROM events AS e
                 LEFT JOIN event_registration AS all_reg
                    ON all_reg.event_id = e.event_id
                 LEFT JOIN event_registration AS own_reg
                    ON own_reg.event_id = e.event_id
                   AND own_reg.student_id = ?
                 GROUP BY e.event_id, e.event_title, e.event_organiser,
                          e.event_date, e.event_fees, e.category, e.status,
                          e.poster_path, own_reg.registration_id
                 ORDER BY e.event_date ASC, e.event_id DESC'
            );
            $statement->bind_param('s', $studentId);
            $statement->execute();
        }

        $result = $statement->get_result();
        respond(['success' => true, 'events' => $result->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($role === 'admin' && $method === 'POST') {
        $data = $_POST;
        $validated = eventData($data);
        if ($validated['errors']) {
            respond(['success' => false, 'message' => implode(' ', $validated['errors'])], 422);
        }

        [$title, $organiser, $date, $fees, $category, $status] = $validated['data'];
        $posterPath = savePoster($_FILES['poster'] ?? null);
        $eventId = (int) ($data['event_id'] ?? 0);
        $oldPoster = null;
        $mysqli->begin_transaction();
        $transactionStarted = true;

        if (($data['action'] ?? 'create') === 'update' && $eventId > 0) {
            $existing = $mysqli->prepare('SELECT poster_path FROM events WHERE event_id = ? LIMIT 1');
            $existing->bind_param('i', $eventId);
            $existing->execute();
            $record = $existing->get_result()->fetch_assoc();
            if (!$record) {
                throw new RuntimeException('Event was not found.');
            }
            $oldPoster = $record['poster_path'];
            $posterPath = $posterPath ?? $oldPoster;
            $statement = $mysqli->prepare(
                'UPDATE events
                 SET event_title = ?, event_organiser = ?, event_date = ?,
                     event_fees = ?, category = ?, status = ?, poster_path = ?
                 WHERE event_id = ?'
            );
            $statement->bind_param('sssdsssi', $title, $organiser, $date, $fees, $category, $status, $posterPath, $eventId);
            $statement->execute();
            $message = 'Event updated successfully.';
        } else {
            $statement = $mysqli->prepare(
                'INSERT INTO events
                 (event_title, event_organiser, event_date, event_fees, category, status, poster_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param('sssdsss', $title, $organiser, $date, $fees, $category, $status, $posterPath);
            $statement->execute();
            $eventId = $mysqli->insert_id;
            $message = 'Event added successfully.';
        }

        $mysqli->commit();
        $transactionStarted = false;
        if ($posterPath && $oldPoster && $posterPath !== $oldPoster) {
            removePoster($oldPoster);
        }
        writeAuditLog($mysqli, $eventId ? 'update' : 'create', 'event', (string) ($eventId ?: $mysqli->insert_id), ['title' => $title, 'status' => $status]);
        respond(['success' => true, 'message' => $message], $eventId ? 200 : 201);
    }

    if ($role === 'admin' && $method === 'PUT') {
        $data = inputData();
        $eventId = (int) ($data['event_id'] ?? 0);
        $validated = eventData($data);
        if ($eventId < 1 || $validated['errors']) {
            respond(['success' => false, 'message' => implode(' ', $validated['errors'] ?: ['A valid event ID is required.'])], 422);
        }

        [$title, $organiser, $date, $fees, $category, $status] = $validated['data'];
        $statement = $mysqli->prepare(
            'UPDATE events
            SET event_title = ?, event_organiser = ?, event_date = ?, event_fees = ?,
                category = ?, status = ?
             WHERE event_id = ?'
        );
        $statement->bind_param('sssdssi', $title, $organiser, $date, $fees, $category, $status, $eventId);
        $statement->execute();
        writeAuditLog($mysqli, 'update', 'event', (string) $eventId, ['title' => $title, 'status' => $status]);
        respond(['success' => true, 'message' => 'Event updated successfully.']);
    }

    if ($role === 'admin' && $method === 'DELETE') {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        if ($eventId < 1) {
            respond(['success' => false, 'message' => 'A valid event ID is required.'], 422);
        }

        $existing = $mysqli->prepare('SELECT poster_path FROM events WHERE event_id = ? LIMIT 1');
        $existing->bind_param('i', $eventId);
        $existing->execute();
        $oldPoster = $existing->get_result()->fetch_assoc()['poster_path'] ?? null;
        $statement = $mysqli->prepare('DELETE FROM events WHERE event_id = ?');
        $statement->bind_param('i', $eventId);
        $statement->execute();
        removePoster($oldPoster);
        writeAuditLog($mysqli, 'delete', 'event', (string) $eventId);
        respond(['success' => true, 'message' => 'Event deleted successfully.']);
    }

    if ($role === 'student' && $method === 'POST') {
        $data = inputData();
        $eventId = (int) ($data['event_id'] ?? 0);

        if ($eventId < 1 && isset($data['event_title'])) {
            $validated = eventData($data);
            if ($validated['errors']) {
                respond(['success' => false, 'message' => implode(' ', $validated['errors'])], 422);
            }

            [$title, $organiser, $date, $fees, $category, $status] = $validated['data'];
            $mysqli->begin_transaction();
            $transactionStarted = true;

            $eventCheck = $mysqli->prepare(
                'SELECT event_id FROM events
                 WHERE event_title = ? AND event_organiser = ?
                   AND event_date = ? AND event_fees = ?
                 LIMIT 1'
            );
            $eventCheck->bind_param('sssd', $title, $organiser, $date, $fees);
            $eventCheck->execute();
            $event = $eventCheck->get_result()->fetch_assoc();

            if ($event) {
                $eventId = (int) $event['event_id'];
            } else {
                $eventInsert = $mysqli->prepare(
                    'INSERT INTO events
                     (event_title, event_organiser, event_date, event_fees, category, status)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $eventInsert->bind_param('sssdss', $title, $organiser, $date, $fees, $category, $status);
                $eventInsert->execute();
                $eventId = $mysqli->insert_id;
            }
        }

        if ($eventId < 1) {
            respond(['success' => false, 'message' => 'Choose an event or enter new event details.'], 422);
        }

        $statement = $mysqli->prepare(
            'INSERT INTO event_registration (student_id, event_id)
             VALUES (?, ?)'
        );
        $statement->bind_param('si', $studentId, $eventId);
        $statement->execute();
        if ($transactionStarted) {
            $mysqli->commit();
            $transactionStarted = false;
        }
        respond(['success' => true, 'message' => 'You are registered for this event.'], 201);
    }

    if ($role === 'student' && $method === 'PUT') {
        $data = inputData();
        $registrationId = (int) ($data['registration_id'] ?? 0);
        $eventId = (int) ($data['event_id'] ?? 0);
        if ($registrationId < 1 || $eventId < 1) {
            respond(['success' => false, 'message' => 'A valid registration and event are required.'], 422);
        }

        $statement = $mysqli->prepare(
            'UPDATE event_registration
             SET event_id = ?
             WHERE registration_id = ? AND student_id = ?'
        );
        $statement->bind_param('iis', $eventId, $registrationId, $studentId);
        $statement->execute();
        respond(['success' => true, 'message' => 'Your event registration was updated.']);
    }

    if ($role === 'student' && $method === 'DELETE') {
        $registrationId = (int) ($_GET['registration_id'] ?? 0);
        if ($registrationId < 1) {
            respond(['success' => false, 'message' => 'A valid registration is required.'], 422);
        }

        $statement = $mysqli->prepare(
            'DELETE FROM event_registration
             WHERE registration_id = ? AND student_id = ?'
        );
        $statement->bind_param('is', $registrationId, $studentId);
        $statement->execute();
        respond(['success' => true, 'message' => 'Your event registration was cancelled.']);
    }

    respond(['success' => false, 'message' => 'This operation is not allowed for your role.'], 403);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    if ($transactionStarted) {
        $mysqli->rollback();
    }
    $message = $exception instanceof mysqli_sql_exception && $exception->getCode() === 1062
        ? 'This event registration already exists.'
        : ($exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'The event operation could not be completed.');
    error_log('Event API failed: ' . $exception->getMessage());
    respond(['success' => false, 'message' => $message], 500);
}
