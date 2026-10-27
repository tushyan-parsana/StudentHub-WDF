<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'mysqli-config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'audit.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$allowedOrigin = 'http://localhost:5500';
$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
if (in_array($origin, [$allowedOrigin, 'http://127.0.0.1:5500'], true)) {
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

if (($_SESSION['role'] ?? '') !== 'admin') {
    respond(['success' => false, 'message' => 'Administrator access is required.'], 403);
}

function inputData(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);

    return is_array($data) ? $data : [];
}

function validateStudent(array $data): array
{
    $studentId = trim((string) ($data['student_id'] ?? ''));
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $mobile = trim((string) ($data['mobile'] ?? ''));
    $gender = ucfirst(strtolower(trim((string) ($data['gender'] ?? ''))));
    $role = strtolower(trim((string) ($data['role'] ?? 'student')));

    $errors = [];
    if (!preg_match('/^[A-Za-z0-9-]{2,30}$/', $studentId)) {
        $errors[] = 'Student ID must contain 2-30 letters, numbers, or hyphens.';
    }
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,49}$/", $fullName)) {
        $errors[] = 'Enter a valid full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile number must contain exactly 10 digits.';
    }
    if (!in_array($gender, ['Female', 'Male', 'Other'], true)) {
        $errors[] = 'Select a valid gender.';
    }
    if (!in_array($role, ['student', 'admin'], true)) {
        $errors[] = 'Select a valid role.';
    }

    return [
        'data' => compact('studentId', 'fullName', 'email', 'mobile', 'gender', 'role'),
        'errors' => $errors
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $search = trim((string) ($_GET['search'] ?? ''));
        $gender = ucfirst(strtolower(trim((string) ($_GET['gender'] ?? ''))));
        $gender = in_array($gender, ['Female', 'Male', 'Other'], true) ? $gender : '';
        $searchLike = '%' . $search . '%';

        $statement = $mysqli->prepare(
            'SELECT student_id, full_name, email, mobile, gender, role, registered_at
             FROM students
             WHERE (? = "" OR student_id LIKE ? OR full_name LIKE ? OR email LIKE ?)
               AND (? = "" OR gender = ?)
             ORDER BY registered_at DESC, student_id ASC
             LIMIT 100'
        );
        $statement->bind_param('ssssss', $search, $searchLike, $searchLike, $searchLike, $gender, $gender);
        $statement->execute();
        $result = $statement->get_result();
        respond(['success' => true, 'students' => $result->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($method === 'POST') {
        $validated = validateStudent(inputData());
        if ($validated['errors']) {
            respond(['success' => false, 'message' => implode(' ', $validated['errors'])], 422);
        }

        $student = $validated['data'];
        $statement = $mysqli->prepare(
            'INSERT INTO students (student_id, full_name, email, mobile, gender, role)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param('ssssss', $student['studentId'], $student['fullName'], $student['email'], $student['mobile'], $student['gender'], $student['role']);
        $statement->execute();
        writeAuditLog($mysqli, 'create', 'student', $student['studentId'], ['email' => $student['email']]);
        respond(['success' => true, 'message' => 'Student added successfully.'], 201);
    }

    if ($method === 'PUT') {
        $validated = validateStudent(inputData());
        if ($validated['errors']) {
            respond(['success' => false, 'message' => implode(' ', $validated['errors'])], 422);
        }

        $student = $validated['data'];
        $statement = $mysqli->prepare(
            'UPDATE students
             SET full_name = ?, email = ?, mobile = ?, gender = ?, role = ?
             WHERE student_id = ?'
        );
        $statement->bind_param('ssssss', $student['fullName'], $student['email'], $student['mobile'], $student['gender'], $student['role'], $student['studentId']);
        $statement->execute();

        if ($statement->affected_rows === 0 && $statement->errno !== 0) {
            throw new RuntimeException('Student update failed.');
        }

        $loginUpdate = $mysqli->prepare(
            'UPDATE student_login SET email = ? WHERE student_id = ?'
        );
        $loginUpdate->bind_param('ss', $student['email'], $student['studentId']);
        $loginUpdate->execute();
        writeAuditLog($mysqli, 'update', 'student', $student['studentId'], ['email' => $student['email']]);
        respond(['success' => true, 'message' => 'Student updated successfully.']);
    }

    if ($method === 'DELETE') {
        $studentId = trim((string) ($_GET['student_id'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9-]{2,30}$/', $studentId)) {
            respond(['success' => false, 'message' => 'A valid student ID is required.'], 422);
        }

        $statement = $mysqli->prepare('DELETE FROM students WHERE student_id = ?');
        $statement->bind_param('s', $studentId);
        $statement->execute();
        writeAuditLog($mysqli, 'delete', 'student', $studentId);
        respond(['success' => true, 'message' => 'Student deleted successfully.']);
    }

    respond(['success' => false, 'message' => 'Method not allowed.'], 405);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $message = $exception instanceof mysqli_sql_exception && $exception->getCode() === 1062
        ? 'Student ID or email already exists.'
        : 'The student operation could not be completed.';
    error_log('Student CRUD failed: ' . $exception->getMessage());
    respond(['success' => false, 'message' => $message], 500);
}
