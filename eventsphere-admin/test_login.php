<?php
require 'db.php';
$email = 'admin@example.com';
$plain = 'ChangeMe123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$u = $stmt->fetch();

if (!$u) { die("❌ User not found"); }

echo "User found: ".$u['email']."<br>";
echo "Hash in DB: ".$u['password']."<br>";

if (password_verify($plain, $u['password'])) {
    echo "✅ Password match!";
} else {
    echo "❌ Password mismatch!";
}
?>
