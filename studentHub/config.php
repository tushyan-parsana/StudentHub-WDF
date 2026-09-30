<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "StudentHub";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Welcome to StudentHub</h1>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>