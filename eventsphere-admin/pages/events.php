<?php
// pages/events.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? null;
  $description = $_POST['description'] ?? null;
  $category = $_POST['category'] ?? null;
  $event_date = $_POST['event_date'] ?: null;
  $event_time = $_POST['event_time'] ?: null;
  $venue = $_POST['venue'] ?? null;
  $organizer_id = $_POST['organizer_id'] ?: null;
  $max_participants = intval($_POST['max_participants'] ?? 0);
  $status = $_POST['status'] ?? 'pending';
  $banner_url = $_POST['banner_url'] ?? null;

  $stmt = $pdo->prepare("INSERT INTO events (title, description, category, event_date, event_time, venue, organizer_id, max_participants, status, banner_url) VALUES (?,?,?,?,?,?,?,?,?,?)");
  $stmt->execute([$title, $description, $category, $event_date, $event_time, $venue, $organizer_id, $max_participants, $status, $banner_url]);
  header('Location: events.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("DELETE FROM events WHERE event_id = ?")->execute([$id]);
  header('Location: events.php');
  exit;
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $title = $_POST['title'] ?? null;
  $description = $_POST['description'] ?? null;
  $category = $_POST['category'] ?? null;
  $event_date = $_POST['event_date'] ?: null;
  $event_time = $_POST['event_time'] ?: null;
  $venue = $_POST['venue'] ?? null;
  $organizer_id = $_POST['organizer_id'] ?: null;
  $max_participants = intval($_POST['max_participants'] ?? 0);
  $status = $_POST['status'] ?? 'pending';
  $banner_url = $_POST['banner_url'] ?? null;

  $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, category=?, event_date=?, event_time=?, venue=?, organizer_id=?, max_participants=?, status=?, banner_url=?, updated_at = NOW() WHERE event_id=?");
  $stmt->execute([$title, $description, $category, $event_date, $event_time, $venue, $organizer_id, $max_participants, $status, $banner_url, $id]);
  header('Location: events.php');
  exit;
}

$rows = $pdo->query("SELECT e.*, u.email as organizer_email FROM events e LEFT JOIN users u ON e.organizer_id = u.user_id ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Events</title>
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
    <h2 class="bg-secondary p-2 rounded">Events</h2>
    <a class="btn btn-secondary" href="?action=create_form"> + Add Event</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Date</th>
          <th>Venue</th>
          <th>Organizer</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['event_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['event_date']) . ' ' . htmlspecialchars($r['event_time']) ?></td>
            <td><?= htmlspecialchars($r['venue']) ?></td>
            <td><?= htmlspecialchars($r['organizer_email']) ?></td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
              <a class="btn btn-success" href="?action=edit_form&id=<?= $r['event_id'] ?>">Edit</a>
              <a class="btn btn-danger" href="?action=delete&id=<?= $r['event_id'] ?>" onclick="return confirm('Delete event?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="p-2 bg-secondary rounded">Create Event</h3>
      <form method="post" action="events.php?action=create">
        <label>Title</label><input class="input" name="title" required>
        <label>Description</label><textarea class="input" name="description"></textarea>
        <label>Category</label><input class="input" name="category">
        <label>Date</label><input class="input" type="date" name="event_date">
        <label>Time</label><input class="input" type="time" name="event_time">
        <label>Venue</label><input class="input" name="venue">
        <label>Organizer</label>
        <select class="input" name="organizer_id">
          <option value="">-- none --</option>
          <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?>
        </select>
        <label>Max Participants</label><input class="input" type="number" name="max_participants" value="0">
        <label>Status</label>
        <select class="input" name="status">
          <option value="pending">pending</option>
          <option value="approved">approved</option>
          <option value="cancelled">cancelled</option>
          <option value="draft">draft</option>
        </select>
        <label>Banner URL</label><input class="input" name="banner_url">
        <div style="margin-top:.6rem"><button class="btn btn-success">Create</button></div>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($action === 'edit_form' && !empty($_GET['id'])):
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
      echo "Not found";
      exit;
    }
  ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="bg-secondary rounded p-2">Edit Event</h3>
      <form method="post" action="events.php?action=update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($r['event_id']) ?>">
        <label>Title</label><input class="input" name="title" value="<?= htmlspecialchars($r['title']) ?>" required>
        <label>Description</label><textarea class="input" name="description"><?= htmlspecialchars($r['description']) ?></textarea>
        <label>Category</label><input class="input" name="category" value="<?= htmlspecialchars($r['category']) ?>">
        <label>Date</label><input class="input" type="date" name="event_date" value="<?= htmlspecialchars($r['event_date']) ?>">
        <label>Time</label><input class="input" type="time" name="event_time" value="<?= htmlspecialchars($r['event_time']) ?>">
        <label>Venue</label><input class="input" name="venue" value="<?= htmlspecialchars($r['venue']) ?>">
        <label>Organizer</label>
        <select class="input" name="organizer_id">
          <option value="">-- none --</option>
          <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>" <?= $u['user_id'] == $r['organizer_id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?>
        </select>
        <label>Max Participants</label><input class="input" type="number" name="max_participants" value="<?= htmlspecialchars($r['max_participants']) ?>">
        <label>Status</label>
        <select class="input" name="status">
          <option value="pending" <?= $r['status'] == 'pending' ? 'selected' : '' ?>>pending</option>
          <option value="approved" <?= $r['status'] == 'approved' ? 'selected' : '' ?>>approved</option>
          <option value="cancelled" <?= $r['status'] == 'cancelled' ? 'selected' : '' ?>>cancelled</option>
          <option value="draft" <?= $r['status'] == 'draft' ? 'selected' : '' ?>>draft</option>
        </select>
        <label>Banner URL</label><input class="input" name="banner_url" value="<?= htmlspecialchars($r['banner_url']) ?>">
        <div style="margin-top:.6rem"><button class="btn btn-success">Update</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>