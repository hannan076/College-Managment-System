<?php
// pages/feedback.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM feedback WHERE feedback_id = ?")->execute([$id]);
  header('Location: feedback.php');
  exit;
}

// (Admin can also add feedback entry)
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = intval($_POST['event_id']);
  $student_id = intval($_POST['student_id']);
  $rating = intval($_POST['rating']);
  $comments = $_POST['comments'] ?? null;
  $stmt = $pdo->prepare("INSERT INTO feedback (event_id, student_id, rating, comments) VALUES (?, ?, ?, ?)");
  $stmt->execute([$event_id, $student_id, $rating, $comments]);
  header('Location: feedback.php');
  exit;
}

$rows = $pdo->query("SELECT f.*, e.title, u.email FROM feedback f JOIN events e ON f.event_id=e.event_id JOIN users u ON f.student_id = u.user_id ORDER BY f.submitted_on DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Feedback</title>
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
    <h2 class="bg-secondary p-2 rounded">Feedback</h2>
    <a class="btn btn-secondary" href="?action=create_form">Add Feedback</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>User</th>
          <th>Rating</th>
          <th>Comments</th>
          <th>On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['feedback_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['rating']) ?></td>
            <td><?= htmlspecialchars($r['comments']) ?></td>
            <td><?= htmlspecialchars($r['submitted_on']) ?></td>
            <td><a class="btn btn-danger" href="?action=delete&id=<?= $r['feedback_id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3  class="bg-secondary p-2 rounded">Add Feedback</h3>
      <form method="post" action="feedback.php?action=create">
        <label>Event</label><select class="input" name="event_id"><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?></select>
        <label>User</label><select class="input" name="student_id"><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?></select>
        <label>Rating (1-5)</label><input class="input" type="number" name="rating" min="1" max="5" value="5">
        <label>Comments</label><textarea class="input" name="comments"></textarea>
        <div style="margin-top:.6rem"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>