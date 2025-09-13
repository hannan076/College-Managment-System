<?php
// db.php - edit your DB username/password
$host = '127.0.0.1';
$db   = 'eventsphere';
$user = 'root';       // agar root use kar rahe ho
$pass = '';           // yaha apna MySQL root password daalo
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo 'DB Connection failed: ' . $e->getMessage();
    exit;
}
