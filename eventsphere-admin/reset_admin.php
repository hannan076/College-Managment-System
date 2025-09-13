<?php
require 'db.php';

$email = "admin@example.com";
$password = "ChangeMe123";

// Naya hash generate karo
$hash = password_hash($password, PASSWORD_BCRYPT);

// Purana record delete kardo (agar hai)
$pdo->prepare("DELETE FROM users WHERE email=?")->execute([$email]);

// Naya record insert karo
$stmt = $pdo->prepare("INSERT INTO users (email, password, role, is_active, created_at) VALUES (?, ?, 'admin', 1, NOW())");
$stmt->execute([$email, $hash]);

echo "✅ Admin reset ho gaya.<br>Email: $email<br>Password: $password<br>Hash: $hash";
