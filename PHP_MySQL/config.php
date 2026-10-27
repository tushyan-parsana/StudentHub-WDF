<?php
declare(strict_types=1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "StudentHub";

$conn = new PDO(
    "mysql:host={$servername};dbname={$dbname};charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);