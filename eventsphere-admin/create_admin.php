<?php
require 'db.php';

// ek hi bar run karo, phir delete kar dena
$email = "admin@example.com";
$password = "ChangeMe123"; // apni pasand ka password daalo

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (email,password,role) VALUES (?,?, 'admin')");
$stmt->execute([$email,$hash]);

echo "Admin created: $email";
