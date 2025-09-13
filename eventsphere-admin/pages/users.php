<?php
// pages/users.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'participant';
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  if ($email && $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $hash, $role, $is_active]);
  }
  header('Location: users.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$id]);
  header('Location: users.php');
  exit;
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $email = $_POST['email'] ?? '';
  $role = $_POST['role'] ?? 'participant';
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  // update password only if provided
  if (!empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET email=?, password=?, role=?, is_active=? WHERE user_id=?");
    $stmt->execute([$email, $hash, $role, $is_active, $id]);
  } else {
    $stmt = $pdo->prepare("UPDATE users SET email=?, role=?, is_active=? WHERE user_id=?");
    $stmt->execute([$email, $role, $is_active, $id]);
  }
  header('Location: users.php');
  exit;
}

// FETCH FOR LIST
$users = $pdo->query("SELECT * FROM users ORDER BY user_id DESC")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Users</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    li {
      list-style: none;
    }

    a {
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
  <div class="w-100 bg-dark" style="height: 1000px;">
    <div class="container col-7">
      <h2 class="bg-secondary text-white p-3 fw-bold rounded">Users Records</h2>
      <a class="btn btn-secondary" href="?action=create_form"> + Add User </a>
      <table class="table table-stripped table-dark table-hover table-bordered rounded" style="margin-top:1rem">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Active</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['user_id']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
              <td><?= htmlspecialchars($u['created_at']) ?></td>
              <td>
                <a class="btn btn-success m-1" href="?action=edit_form&id=<?= $u['user_id'] ?>">Edit</a>
                <a class="btn btn-danger m-1" href="?action=delete&id=<?= $u['user_id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($action === 'create_form'): ?>
      <div class="container col-7 text-white" style="margin-top:1rem">
        <h3 class="bg-secondary p-3 rounded">Create User</h3>
        <form method="post" action="users.php?action=create">
          <label>Email</label><input class="input" name="email" type="email" required>
          <label>Password</label><input class="input" name="password" type="password" required>
          <label>Role</label>
          <select class="input" name="role">
            <option value="participant">participant</option>
            <option value="organizer">organizer</option>
            <option value="admin">admin</option>
          </select>
          <label><input type="checkbox" name="is_active" checked> Active</label>
          <div style="margin-top:.6rem"><button class="btn btn-success ">Create</button></div>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($action === 'edit_form' && !empty($_GET['id'])):
      $id = intval($_GET['id']);
      $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
      $stmt->execute([$id]);
      $user = $stmt->fetch();
      if (!$user) {
        echo "<p>Not found</p>";
        exit;
      }
    ?>
      <div class="container col-7 text-white" style="margin-top:1rem">
        <h3 class="bg-secondary p-2 rounded">Edit User</h3>
        <form method="post" action="users.php?action=update">
          <input type="hidden" name="id" value="<?= htmlspecialchars($user['user_id']) ?>">
          <label>Email</label><input class="input" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          <label>Password (leave blank to keep current)</label><input class="input" name="password" type="password">
          <label>Role</label>
          <select class="input" name="role">
            <option value="participant" <?= $user['role'] == 'participant' ? 'selected' : '' ?>>participant</option>
            <option value="organizer" <?= $user['role'] == 'organizer' ? 'selected' : '' ?>>organizer</option>
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>admin</option>
          </select>
          <label><input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>> Active</label>
          <div style="margin-top:.6rem"><button class="btn btn-success mb-3">Update</button></div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>