<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli('localhost', 'root', '', 'StudentHub');

if ($mysqli->connect_errno) {
    http_response_code(500);
    exit('Database connection failed.');
}

$mysqli->set_charset('utf8mb4');
