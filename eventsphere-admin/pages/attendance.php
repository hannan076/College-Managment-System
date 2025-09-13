<?php
// pages/attendance.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE/MARK
if ($action === 'mark' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = intval($_POST['event_id']);
  $student_id = intval($_POST['student_id']);
  $attended = isset($_POST['attended']) ? 1 : 0;
  // either insert or update
  $stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE event_id=? AND student_id=?");
  $stmt->execute([$event_id, $student_id]);
  $exists = $stmt->fetch();
  if ($exists) {
    $pdo->prepare("UPDATE attendance SET attended=?, marked_on=NOW() WHERE attendance_id=?")->execute([$attended, $exists['attendance_id']]);
  } else {
    $pdo->prepare("INSERT INTO attendance (event_id, student_id, attended, marked_on) VALUES (?, ?, ?, NOW())")->execute([$event_id, $student_id, $attended]);
  }
  header('Location: attendance.php');
  exit;
}

if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?")->execute([$id]);
  header('Location: attendance.php');
  exit;
}

$rows = $pdo->query("SELECT a.*, e.title, u.email FROM attendance a JOIN events e ON a.event_id = e.event_id JOIN users u ON a.student_id = u.user_id ORDER BY a.marked_on DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Attendance</title>
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

<body class="bg-dark text-white ">
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
    <h2 class="bg-secondary p-2 rounded">Attendance</h2>
    <a class="btn btn-secondary" href="?action=mark_form">Mark Attendance</a>
    <table class="table  table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>Student</th>
          <th>Attended</th>
          <th>Marked On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['attendance_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= $r['attended'] ? 'Yes' : 'No' ?></td>
            <td><?= htmlspecialchars($r['marked_on']) ?></td>
            <td><a class="btn btn-danger" href="?action=delete&id=<?= $r['attendance_id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'mark_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="bg-secondary p-2 rounded">Mark Attendance</h3>
      <form method="post" action="attendance.php?action=mark">
        <label>Event</label><select class="input" name="event_id" required><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?></select>
        <label>Student</label><select class="input" name="student_id" required><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?></select>
        <label><input type="checkbox" name="attended" checked> Attended</label>
        <div style="margin-top:.6rem"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>