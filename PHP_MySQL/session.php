<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

$origin = frontendOrigin();
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if (!sessionIsValid()) {
    http_response_code(401);
    echo json_encode(['authenticated' => false]);
    exit;
}

echo json_encode([
    'authenticated' => true,
    'studentId' => $_SESSION['student_id'],
    'fullName' => $_SESSION['full_name'] ?? '',
    'role' => $_SESSION['role'] ?? 'student'
]);
