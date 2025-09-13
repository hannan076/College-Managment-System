<?php
// pages/registrations.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE (admin can add registration)
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = intval($_POST['event_id']);
  $student_id = intval($_POST['student_id']);
  $status = $_POST['status'] ?? 'confirmed';
  $stmt = $pdo->prepare("INSERT IGNORE INTO registrations (event_id, student_id, status) VALUES (?, ?, ?)");
  $stmt->execute([$event_id, $student_id, $status]);
  header('Location: registrations.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM registrations WHERE registration_id = ?")->execute([$id]);
  header('Location: registrations.php');
  exit;
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $status = $_POST['status'] ?? 'confirmed';
  $pdo->prepare("UPDATE registrations SET status=? WHERE registration_id=?")->execute([$status, $id]);
  header('Location: registrations.php');
  exit;
}

$rows = $pdo->query("SELECT r.*, e.title, u.email FROM registrations r JOIN events e ON r.event_id=e.event_id JOIN users u ON r.student_id = u.user_id ORDER BY r.registered_on DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Registrations</title>
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

<body class="bg-dark text-white">
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
  <div class="container col-7">
    <h2 class="p-2 rounded bg-secondary">Registrations</h2>
    <a class="btn btn-secondary" href="?action=create_form"> + Add Registration</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>Student</th>
          <th>Registered On</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['registration_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['registered_on']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
              <a class="btn btn-success" href="?action=edit_form&id=<?= $r['registration_id'] ?>">Edit</a>
              <a class="btn btn-danger" href="?action=delete&id=<?= $r['registration_id'] ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="p-2 rounded bg-secondary">Add Registration</h3>
      <form method="post" action="registrations.php?action=create">
        <label>Event</label><select class="input" name="event_id" required><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?></select>
        <label>Student</label><select class="input" name="student_id" required><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?></select>
        <label>Status</label><select class="input" name="status">
          <option value="confirmed">confirmed</option>
          <option value="waitlist">waitlist</option>
          <option value="cancelled">cancelled</option>
        </select>
        <div style="margin-top:.6rem"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($action === 'edit_form' && !empty($_GET['id'])):
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE registration_id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
      echo "Not found";
      exit;
    }
  ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="p-2 rounded bg-secondary ">Edit Registration</h3>
      <form method="post" action="registrations.php?action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($r['registration_id']) ?>">
        <label>Status</label>
        <select class="input" name="status">
          <option value="confirmed" <?= $r['status'] == 'confirmed' ? 'selected' : '' ?>>confirmed</option>
          <option value="waitlist" <?= $r['status'] == 'waitlist' ? 'selected' : '' ?>>waitlist</option>
          <option value="cancelled" <?= $r['status'] == 'cancelled' ? 'selected' : '' ?>>cancelled</option>
        </select>
        <div style="margin-top:.6rem"><button class="btn btn-success">Update</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>