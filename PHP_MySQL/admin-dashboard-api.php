<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'mysqli-config.php';

$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if (in_array($origin, ['http://localhost:5500', 'http://127.0.0.1:5500'], true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Content-Type: application/json; charset=utf-8');

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (!sessionIsValid() || ($_SESSION['role'] ?? '') !== 'admin') {
    respond(['success' => false, 'message' => 'Administrator access is required.'], 403);
}

try {
    $counts = [];
    foreach ([
        'students' => 'SELECT COUNT(*) FROM students',
        'events' => 'SELECT COUNT(*) FROM events',
        'registrations' => 'SELECT COUNT(*) FROM event_registration',
        'open_events' => "SELECT COUNT(*) FROM events WHERE status = 'open'"
    ] as $key => $query) {
        $counts[$key] = (int) $mysqli->query($query)->fetch_row()[0];
    }

    $latestRegistrations = $mysqli->query(
        'SELECT er.registration_id, er.student_id, s.full_name,
                e.event_title, er.registered_at
         FROM event_registration AS er
         INNER JOIN students AS s ON s.student_id = er.student_id
         INNER JOIN events AS e ON e.event_id = er.event_id
         ORDER BY er.registered_at DESC
         LIMIT 8'
    )->fetch_all(MYSQLI_ASSOC);

    $auditLogs = $mysqli->query(
        'SELECT audit_id, actor_student_id, action, entity_type,
                entity_id, details, created_at
         FROM audit_logs
         ORDER BY created_at DESC
         LIMIT 10'
    )->fetch_all(MYSQLI_ASSOC);

    respond([
        'success' => true,
        'counts' => $counts,
        'latestRegistrations' => $latestRegistrations,
        'auditLogs' => $auditLogs
    ]);
} catch (mysqli_sql_exception $exception) {
    error_log('Admin dashboard query failed: ' . $exception->getMessage());
    respond(['success' => false, 'message' => 'Dashboard data could not be loaded.'], 500);
}
