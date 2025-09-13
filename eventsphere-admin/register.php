<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// DB config
$conn = new mysqli("localhost", "root", "", "eventsphere");
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);

// --- AJAX email check
if (isset($_GET['action']) && $_GET['action'] === 'check_email') {
    $email = trim($_GET['email'] ?? '');
    $stmt = $conn->prepare("SELECT user_id FROM user_records WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    echo json_encode(['exists' => $stmt->num_rows > 0]);
    exit;
}

$errors = [];
$old = ['name' => '', 'email' => '', 'contact' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];
    $old = compact('name', 'email', 'contact', 'username');

    // === VALIDATION ===
    if ($name === '') $errors['name'] = "Name is required";
    elseif (strlen($name) < 3) $errors['name'] = "Name must be at least 3 characters";

    if ($email === '') $errors['email'] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format";
    else {
        $stmt = $conn->prepare("SELECT user_id FROM user_records WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['email'] = "Email already exists";
    }

    if ($contact === '') $errors['contact'] = "Contact is required";
    elseif (!ctype_digit($contact)) $errors['contact'] = "Contact must contain only digits";
    elseif (strlen($contact) > 11) $errors['contact'] = "Contact must be 11 digits or less";

    if ($username === '') $errors['username'] = "Username is required";
    else {
        $stmt = $conn->prepare("SELECT user_id FROM user_records WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['username'] = "Username already taken";
    }

    if ($password === '') $errors['password'] = "Password is required";
    if ($c_password === '') $errors['c_password'] = "Confirm password is required";
    elseif ($password !== '' && $c_password !== $password) $errors['c_password'] = "Confirm password not matched";

    // === INSERT ===
    if (empty($errors)) {
        $hash = md5($password); // project requirement
        $stmt = $conn->prepare("INSERT INTO user_records(name,email,contact,username,password) VALUES(?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $email, $contact, $username, $hash);
        if ($stmt->execute()) {
            $_SESSION['registered_success'] = true;
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: profile.php");
            exit;
        } else $errors['general'] = "Database error: could not register.";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Register</title>
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

        .accent-btn {
            background: linear-gradient(90deg, var(--accent), #ff8a33);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 10px 18px;
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

        .is-invalid {
            border: 2px solid #ff3b3b !important;
        }

        .is-valid {
            border: 2px solid #28a745 !important;
        }

        .error-text {
            color: #ff3b3b;
            font-size: .9rem;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="glass">
                    <h3 class="mb-3 text-center">Create Account</h3>
                    <form id="regForm" method="post">
                        <div class="floating-label">
                            <input type="text" id="name" name="name" placeholder=" " value="<?= $old['name'] ?>">
                            <label for="name">Full Name</label>
                            <div class="error-text" id="err-name"><?= $errors['name'] ?? '' ?></div>
                        </div>
                        <div class="floating-label">
                            <input type="email" id="email" name="email" placeholder=" " value="<?= $old['email'] ?>">
                            <label for="email">Email</label>
                            <div class="error-text" id="err-email"><?= $errors['email'] ?? '' ?></div>
                        </div>
                        <div class="floating-label">
                            <input type="text" id="contact" name="contact" placeholder=" " value="<?= $old['contact'] ?>">
                            <label for="contact">Contact Number</label>
                            <div class="error-text" id="err-contact"><?= $errors['contact'] ?? '' ?></div>
                        </div>
                        <div class="floating-label">
                            <input type="text" id="username" name="username" placeholder=" " value="<?= $old['username'] ?>">
                            <label for="username">Username</label>
                            <div class="error-text" id="err-username"><?= $errors['username'] ?? '' ?></div>
                        </div>
                        <div class="floating-label">
                            <input type="password" id="password" name="password" placeholder=" ">
                            <label for="password">Password</label>
                            <div class="error-text" id="err-password"><?= $errors['password'] ?? '' ?></div>
                        </div>
                        <div class="floating-label">
                            <input type="password" id="c_password" name="c_password" placeholder=" ">
                            <label for="c_password">Confirm Password</label>
                            <div class="error-text" id="err-c_password"><?= $errors['c_password'] ?? '' ?></div>
                        </div>
                        <button class="accent-btn w-100" name="register">Register</button>
                        <div class="mt-3 text-center">Already registered? <a href="user_login.php" style="color:var(--accent)">Login</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('regForm');

        function setError(f, m) {
            const el = document.getElementById(f);
            el.classList.add('is-invalid');
            el.classList.remove('is-valid');
            document.getElementById('err-' + f).innerText = m;
        }

        function setSuccess(f) {
            const el = document.getElementById(f);
            el.classList.add('is-valid');
            el.classList.remove('is-invalid');
            document.getElementById('err-' + f).innerText = '';
        }

        form.addEventListener('submit', e => {
            let ok = true;

            // Name
            if (!name.value.trim()) {
                setError('name', 'Name is required');
                ok = false;
            } else if (name.value.trim().length < 3) {
                setError('name', 'Name must be at least 3 characters');
                ok = false;
            } else setSuccess('name');

            // Email
            if (!email.value.trim()) {
                setError('email', 'Email is required');
                ok = false;
            } else setSuccess('email');

            // Contact
            const contactVal = contact.value.trim();
            if (!contactVal) {
                setError('contact', 'Contact is required');
                ok = false;
            } else if (!/^\d+$/.test(contactVal)) {
                setError('contact', 'Contact must contain only digits');
                ok = false;
            } else if (contactVal.length > 11) {
                setError('contact', 'Contact must be 11 digits or less');
                ok = false;
            } else {
                setSuccess('contact');
            }

            // Username
            if (!username.value.trim()) {
                setError('username', 'Username is required');
                ok = false;
            } else setSuccess('username');

            // Password
            if (!password.value) {
                setError('password', 'Password is required');
                ok = false;
            } else setSuccess('password');

            // Confirm password
            if (!c_password.value) {
                setError('c_password', 'Confirm password is required');
                ok = false;
            } else if (c_password.value !== password.value) {
                setError('c_password', 'Confirm password not matched');
                ok = false;
            } else setSuccess('c_password');

            if (!ok) e.preventDefault();
        });
    </script>
</body>

</html>
