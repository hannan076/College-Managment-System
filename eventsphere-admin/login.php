<?php
require 'db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['password']) && $u['role']==='admin'){
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['role']    = $u['role'];
        header("Location: index.php"); exit;
    } else {
        $error = "Invalid login";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-card">
  <h2 class="brand">EventSphere Admin</h2>
  <?php if($error): ?><div class="card"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <input class="input" type="email" name="email" placeholder="Email" required><br><br>
    <input class="input" type="password" name="password" placeholder="Password" required><br><br>
    <button class="btn">Login</button>
  </form>
</div>
</body>
</html>
