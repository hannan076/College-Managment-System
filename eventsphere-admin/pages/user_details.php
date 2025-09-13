<?php
// pages/user_details.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = intval($_POST['user_id']);
  $full_name = $_POST['full_name'] ?? null;
  $mobile = $_POST['mobile'] ?? null;
  $department = $_POST['department'] ?? null;
  $enrollment_no = $_POST['enrollment_no'] ?? null;
  $profile_photo = $_POST['profile_photo'] ?? null;

  $stmt = $pdo->prepare("INSERT INTO user_details (user_id, full_name, mobile, department, enrollment_no, profile_photo) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$user_id, $full_name, $mobile, $department, $enrollment_no, $profile_photo]);
  header('Location: user_details.php');
  exit;
}

if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM user_details WHERE detail_id = ?")->execute([$id]);
  header('Location: user_details.php');
  exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $full_name = $_POST['full_name'] ?? null;
  $mobile = $_POST['mobile'] ?? null;
  $department = $_POST['department'] ?? null;
  $enrollment_no = $_POST['enrollment_no'] ?? null;
  $profile_photo = $_POST['profile_photo'] ?? null;

  $stmt = $pdo->prepare("UPDATE user_details SET full_name=?, mobile=?, department=?, enrollment_no=?, profile_photo=? WHERE detail_id=?");
  $stmt->execute([$full_name, $mobile, $department, $enrollment_no, $profile_photo, $id]);
  header('Location: user_details.php');
  exit;
}

$rows = $pdo->query("SELECT ud.*, u.email FROM user_details ud LEFT JOIN users u ON ud.user_id = u.user_id ORDER BY ud.detail_id DESC")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>User Details</title>
  <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    li{
      list-style: none;
    }
    a{
      font-size: 18px;
      color: white !important;
      text-decoration: none !important;
    }
  </style>
</head>

<body>
    <div class="sidebar col-2">
    <div class="brand">EventSphere</div>
    <ul>
      <li><a href="../index.php">Dashboard</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="events.php">Events</a></li>
      <li><a href="registrations.php">Registrations</a></li>
      <li><a href="attendance.php">Attendance</a></li>
      <li><a href="feedback.php">Feedback</a></li>
      <li><a href="certificates.php">Certificates</a></li>
      <li><a href="media_gallery.php">Media</a></li>
      <li><a href="event_seating.php">Seating</a></li>
      <li><a href="event_waitlist.php">Waitlist</a></li>
      <li><a href="calendar_sync.php">Calendar Sync</a></li>
      <li><a href="event_share_log.php">Share Log</a></li>
      <li><a href="../logout.php">Logout</a></li>

    </ul>

  </div>
  <div class="container">
    <h2>User Details</h2>
    <a class="btn" href="?action=create_form">Add Details</a>
    <table class="table" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Name</th>
          <th>Mobile</th>
          <th>Department</th>
          <th>Enrollment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['detail_id']) ?></td>
            <td><?= htmlspecialchars($r['email'] ?? $r['user_id']) ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['mobile']) ?></td>
            <td><?= htmlspecialchars($r['department']) ?></td>
            <td><?= htmlspecialchars($r['enrollment_no']) ?></td>
            <td>
              <a class="btn" href="?action=edit_form&id=<?= $r['detail_id'] ?>">Edit</a>
              <a class="btn" href="?action=delete&id=<?= $r['detail_id'] ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'):
    $users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
  ?>
    <div class="container" style="margin-top:1rem">
      <h3>Add User Details</h3>
      <form method="post" action="user_details.php?action=create">
        <label>User</label>
        <select class="input" name="user_id" required>
          <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?>
        </select>
        <label>Full Name</label><input class="input" name="full_name">
        <label>Mobile</label><input class="input" name="mobile">
        <label>Department</label><input class="input" name="department">
        <label>Enrollment No</label><input class="input" name="enrollment_no">
        <label>Profile Photo (URL)</label><input class="input" name="profile_photo">
        <div style="margin-top:.6rem"><button class="btn">Save</button></div>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($action === 'edit_form' && !empty($_GET['id'])):
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM user_details WHERE detail_id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
      echo "Not found";
      exit;
    }
  ?>
    <div class="container" style="margin-top:1rem">
      <h3>Edit Details</h3>
      <form method="post" action="user_details.php?action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($r['detail_id']) ?>">
        <label>Full Name</label><input class="input" name="full_name" value="<?= htmlspecialchars($r['full_name']) ?>">
        <label>Mobile</label><input class="input" name="mobile" value="<?= htmlspecialchars($r['mobile']) ?>">
        <label>Department</label><input class="input" name="department" value="<?= htmlspecialchars($r['department']) ?>">
        <label>Enrollment No</label><input class="input" name="enrollment_no" value="<?= htmlspecialchars($r['enrollment_no']) ?>">
        <label>Profile Photo (URL)</label><input class="input" name="profile_photo" value="<?= htmlspecialchars($r['profile_photo']) ?>">
        <div style="margin-top:.6rem"><button class="btn">Update</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>