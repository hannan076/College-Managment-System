<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'eventsphere';
$conn = new mysqli($db_host,$db_user,$db_pass,$db_name);
if ($conn->connect_error) die('DB Err');

$uid = intval($_SESSION['user_id']);

// fetch user
$stmt = $conn->prepare("SELECT user_id, name, email, contact, username, created_at FROM users WHERE user_id = ?");
$stmt->bind_param('i',$uid);
$stmt->execute();
$stmt->bind_result($user_id,$name,$email,$contact,$username,$created_at);
$stmt->fetch();
$stmt->close();

// handle update via POST (edit)
$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nname = trim($_POST['name'] ?? '');
    $ncontact = trim($_POST['contact'] ?? '');
    // basic validation
    if ($nname === '' || strlen($nname) < 3) {
        $update_msg = 'Name is required and must be >=3 chars';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, contact = ? WHERE user_id = ?");
        $stmt->bind_param('ssi', $nname, $ncontact, $uid);
        if ($stmt->execute()) {
            $update_msg = 'updated';
            $name = $nname; $contact = $ncontact;
            $_SESSION['profile_updated'] = true;
        } else {
            $update_msg = 'update_failed';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile - EventSphere</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body{ background:#0b0b0b; color:#eee; }
    .card{ background:linear-gradient(180deg,#0f0f0f,#0b0b0b); border-radius:12px; padding:20px; border:1px solid rgba(255,255,255,0.03); }
    .accent-btn { background:#ff6a00; color:#111; border:none; padding:8px 14px; border-radius:8px; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($name); ?></h4>
            <div>
              <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
              <button class="btn btn-sm accent-btn" data-bs-toggle="modal" data-bs-target="#editModal">Edit Profile</button>
            </div>
          </div>

          <div style="color:#bdbdbd">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($contact ?: '—'); ?></p>
            <p><strong>Joined:</strong> <?php echo htmlspecialchars($created_at); ?></p>
          </div>
          <hr style="border-color:rgba(255,255,255,0.04)">
          <h5>Available Events</h5>
          <p style="color:#bdbdbd">(Events listing will appear here — implement event logic later)</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background:#0b0b0b;color:#fff;border:none;">
        <div class="modal-header border-0">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contact</label>
              <input name="contact" class="form-control" value="<?php echo htmlspecialchars($contact); ?>">
            </div>
            <?php if ($update_msg && $update_msg !== 'updated'): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($update_msg); ?></div>
            <?php endif; ?>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button name="update_profile" class="btn accent-btn">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
if (isset($_SESSION['registered_success'])) {
    echo "<script>
    Swal.fire({ icon:'success', title:'Account registered successfully', showConfirmButton:false, timer:1600, background:'#0b0b0b', color:'#fff' });
    </script>";
    unset($_SESSION['registered_success']);
}

if (isset($_SESSION['profile_updated'])) {
    echo "<script>
      Swal.fire({ icon:'success', title:'Record updated successfully', showConfirmButton:false, timer:1400, background:'#0b0b0b', color:'#fff' })
      .then(()=>{ location.reload(); });
    </script>";
    unset($_SESSION['profile_updated']);
}
?>
</body>
</html>
