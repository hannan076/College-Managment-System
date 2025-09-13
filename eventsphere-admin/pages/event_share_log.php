<?php
// pages/event_share_log.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE (admin can add a log)
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'] ?: null;
  $event_id = $_POST['event_id'] ?: null;
  $platform = $_POST['platform'] ?? null;
  $share_message = $_POST['share_message'] ?? null;
  $stmt = $pdo->prepare("INSERT INTO event_share_log (user_id, event_id, platform, share_message) VALUES (?,?,?,?)");
  $stmt->execute([$user_id, $event_id, $platform, $share_message]);
  header('Location: event_share_log.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM event_share_log WHERE share_id = ?")->execute([$id]);
  header('Location: event_share_log.php');
  exit;
}

$rows = $pdo->query("SELECT s.*, e.title, u.email FROM event_share_log s LEFT JOIN events e ON s.event_id=e.event_id LEFT JOIN users u ON s.user_id=u.user_id ORDER BY s.share_timestamp DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Share Log</title>
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

<body   class="bg-dark text-white">
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
    <h2 class="bg-secondary p-2 rounded">Event Share Log</h2>
    <a class="btn btn-secondary" href="?action=create_form">Add Log</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>User</th>
          <th>Platform</th>
          <th>Message</th>
          <th>When</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['share_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['platform']) ?></td>
            <td><?= htmlspecialchars($r['share_message']) ?></td>
            <td><?= htmlspecialchars($r['share_timestamp']) ?></td>
            <td><a class="btn btn-danger" href="?action=delete&id=<?= $r['share_id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3  class="bg-secondary p-2 rounded">Add Share Log</h3>
      <form method="post" action="event_share_log.php?action=create">
        <label>User</label><select class="input" name="user_id">
          <option value="">-- none --</option><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?>
        </select>
        <label>Event</label><select class="input" name="event_id">
          <option value="">-- none --</option><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?>
        </select>
        <label>Platform</label><input class="input" name="platform">
        <label>Message</label><textarea class="input" name="share_message"></textarea>
        <div style="margin-top:.6rem"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>