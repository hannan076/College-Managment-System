<?php
// pages/event_waitlist.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE (add to waitlist)
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = intval($_POST['user_id']);
  $event_id = intval($_POST['event_id']);
  $status = $_POST['status'] ?? 'waiting';
  $stmt = $pdo->prepare("INSERT IGNORE INTO event_waitlist (user_id, event_id, status) VALUES (?,?,?)");
  $stmt->execute([$user_id, $event_id, $status]);
  header('Location: event_waitlist.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM event_waitlist WHERE waitlist_id = ?")->execute([$id]);
  header('Location: event_waitlist.php');
  exit;
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $status = $_POST['status'] ?? 'waiting';
  $pdo->prepare("UPDATE event_waitlist SET status=? WHERE waitlist_id=?")->execute([$status, $id]);
  header('Location: event_waitlist.php');
  exit;
}

$rows = $pdo->query("SELECT w.*, e.title, u.email FROM event_waitlist w JOIN events e ON w.event_id=e.event_id JOIN users u ON w.user_id=u.user_id ORDER BY w.waitlist_time DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Event Waitlist</title>
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
  <div class="container  col-7">
    <h2  class="bg-secondary p-2 rounded">Event Waitlist</h2>
    <a class="btn btn-secondary" href="?action=create_form">Add to Waitlist</a>
    <table class="table table-bordered table-stripped table-dark table-hoverz" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>User</th>
          <th>Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['waitlist_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['waitlist_time']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
              <a class="btn btn-success"  href="?action=edit_form&id=<?= $r['waitlist_id'] ?>">Edit</a>
              <a class="btn btn-danger" href="?action=delete&id=<?= $r['waitlist_id'] ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container  col-7" style="margin-top:1rem">
      <h3  class="bg-secondary p-2 rounded">Add to Waitlist</h3>
      <form method="post" action="event_waitlist.php?action=create">
        <label>Event</label><select class="input" name="event_id"><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?></select>
        <label>User</label><select class="input" name="user_id"><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?></select>
        <label>Status</label><select class="input" name="status">
          <option value="waiting">waiting</option>
          <option value="confirmed">confirmed</option>
          <option value="cancelled">cancelled</option>
        </select>
        <div style="margin-top:.6rem"><button class="btn btn-success">Add</button></div>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($action === 'edit_form' && !empty($_GET['id'])):
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM event_waitlist WHERE waitlist_id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
      echo "Not found";
      exit;
    }
  ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3>Edit Waitlist</h3>
      <form method="post" action="event_waitlist.php?action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($r['waitlist_id']) ?>">
        <label>Status</label><select class="input" name="status">
          <option value="waiting" <?= $r['status'] == 'waiting' ? 'selected' : '' ?>>waiting</option>
          <option value="confirmed" <?= $r['status'] == 'confirmed' ? 'selected' : '' ?>>confirmed</option>
          <option value="cancelled" <?= $r['status'] == 'cancelled' ? 'selected' : '' ?>>cancelled</option>
        </select>
        <div style="margin-top:.6rem"><button class="btn btn-success">Update</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>