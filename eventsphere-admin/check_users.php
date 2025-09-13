<?php
require 'db.php';

$stmt = $pdo->query("SELECT user_id, email, role, is_active FROM users");
$rows = $stmt->fetchAll();
echo "<pre>";
print_r($rows);
echo "</pre>";