<?php
/**
 * Database Connection - Railway MySQL (Direct)
 */

// Railway MySQL connection details
$host = 'acela.proxy.rlwy.net';
$port = '58875';
$dbname = 'railway';
$username = 'root';
$password = 'BYAUeRnQsexIYYLrephmWXhLlCBuMLcS';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>