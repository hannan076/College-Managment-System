<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventsphere");
if ($conn->connect_error) die("DB error");
$error = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass = md5($_POST['password']);
    $stmt = $conn->prepare("SELECT user_id FROM user_records WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($uid);
        $stmt->fetch();
        $_SESSION['user_id'] = $uid;
        $_SESSION['login_success'] = true;
        header("Location: profile.php");
        exit;
    } else $error = "Invalid email or password";
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #0b0b0b;
            --accent: #ff6a00;
            --muted: #bdbdbd;
        }

        body {
            background: #0b0b0b;
            color: #eee;
        }

        .glass {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
        }

        .floating-label {
            position: relative;
            margin-bottom: 22px;
        }

        .floating-label input {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 14px;
            color: #fff;
            border-radius: 10px;
            width: 100%;
        }

        .floating-label label {
            position: absolute;
            left: 14px;
            top: 14px;
            color: var(--muted);
            transition: .2s;
            pointer-events: none;
        }

        .floating-label input:focus+label,
        .floating-label input:not(:placeholder-shown)+label {
            transform: translateY(-28px) scale(.9);
            color: var(--accent);
        }

        .accent-btn {
            background: linear-gradient(90deg, var(--accent), #ff8a33);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 10px 18px;
        }

        .is-invalid {
            border: 2px solid #ff3b3b !important;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass">
                    <h3 class="mb-3 text-center">Login</h3>
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <form method="post">
                        <div class="floating-label"><input type="email" name="email" placeholder=" "><label>Email</label></div>
                        <div class="floating-label"><input type="password" name="password" placeholder=" "><label>Password</label></div>
                        <button class="accent-btn w-100" name="login">Login</button>
                        <div class="mt-3 text-center">No account? <a href="register.php" style="color:var(--accent)">Register</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_SESSION['login_success'])) {
        unset($_SESSION['login_success']); ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login successful',
                timer: 1500,
                showConfirmButton: false,
                background: '#0b0b0b',
                color: '#fff'
            });
        </script>
    <?php } ?>
</body>

</html>