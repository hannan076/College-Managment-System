<?php
// pages/media_gallery.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$action = $_GET['action'] ?? 'list';

// UPLOAD / CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = $_POST['event_id'] ?: null;
  $file_type = $_POST['file_type'] ?? 'image';
  $caption = $_POST['caption'] ?? null;
  $uploaded_by = $_POST['uploaded_by'] ?: null;
  $file_url = null;

  if (!empty($_FILES['file']['name'])) {
    $orig = $_FILES['file']['name'];
    $ext = pathinfo($orig, PATHINFO_EXTENSION);
    $safe = uniqid('media_') . '.' . $ext;
    $dest = $uploadDir . '/' . $safe;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
      $file_url = 'uploads/' . $safe;
    }
  }

  if ($file_url) {
    $stmt = $pdo->prepare("INSERT INTO media_gallery (event_id, file_type, file_url, uploaded_by, caption) VALUES (?,?,?,?,?)");
    $stmt->execute([$event_id, $file_type, $file_url, $uploaded_by, $caption]);
  }
  header('Location: media_gallery.php');
  exit;
}

// DELETE
if ($action === 'delete' && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $stmt = $pdo->prepare("SELECT file_url FROM media_gallery WHERE media_id = ?");
  $stmt->execute([$id]);
  $r = $stmt->fetch();
  if ($r && !empty($r['file_url'])) {
    $path = __DIR__ . '/../' . $r['file_url'];
    if (file_exists($path)) @unlink($path);
  }
  $pdo->prepare("DELETE FROM media_gallery WHERE media_id = ?")->execute([$id]);
  header('Location: media_gallery.php');
  exit;
}

$rows = $pdo->query("SELECT m.*, e.title, u.email FROM media_gallery m LEFT JOIN events e ON m.event_id=e.event_id LEFT JOIN users u ON m.uploaded_by=u.user_id ORDER BY m.uploaded_on DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
$users = $pdo->query("SELECT user_id, email FROM users ORDER BY email")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Media Gallery</title>
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

<body  class="bg-dark text-white">
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
    <h2  class="bg-secondary p-2 rounded">Media Gallery</h2>
    <a class="btn btn-secondary" href="?action=create_form">Upload Media</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>File</th>
          <th>Type</th>
          <th>Uploader</th>
          <th>Caption</th>
          <th>On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['media_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?php if ($r['file_url']): ?><a href="../<?= htmlspecialchars($r['file_url']) ?>" target="_blank" class="btn btn-success">View</a><?php endif; ?></td>
            <td><?= htmlspecialchars($r['file_type']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['caption']) ?></td>
            <td><?= htmlspecialchars($r['uploaded_on']) ?></td>
            <td><a class="btn btn-danger" href="?action=delete&id=<?= $r['media_id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'create_form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3  class="bg-secondary p-2 rounded">Upload Media</h3>
      <form method="post" action="media_gallery.php?action=create" enctype="multipart/form-data">
        <label>Event (optional)</label><select class="input" name="event_id">
          <option value="">-- none --</option><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?>
        </select>
        <label>File</label><input class="input" type="file" name="file" required>
        <label>Type</label>
        <select class="input" name="file_type">
          <option value="image">image</option>
          <option value="video">video</option>
        </select>
        <label>Uploaded By</label><select class="input" name="uploaded_by"><?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['email']) ?></option><?php endforeach; ?></select>
        <label>Caption</label><input class="input" name="caption">
        <div style="margin-top:.6rem"><button class="btn btn-success">Upload</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>