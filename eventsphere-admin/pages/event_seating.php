<?php
// pages/event_seating.php
require_once __DIR__ . '/../auth.php';
require_admin();
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? 'list';

// CREATE or UPDATE (event_id is primary key)
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = intval($_POST['event_id']);
  $venue_id = $_POST['venue_id'] ?: null;
  $total_seats = intval($_POST['total_seats'] ?? 0);
  $seats_booked = intval($_POST['seats_booked'] ?? 0);
  $waitlist_enabled = isset($_POST['waitlist_enabled']) ? 1 : 0;

  // upsert: if exists update else insert
  $stmt = $pdo->prepare("SELECT event_id FROM event_seating WHERE event_id = ?");
  $stmt->execute([$event_id]);
  if ($stmt->fetch()) {
    $pdo->prepare("UPDATE event_seating SET venue_id=?, total_seats=?, seats_booked=?, waitlist_enabled=? WHERE event_id=?")
      ->execute([$venue_id, $total_seats, $seats_booked, $waitlist_enabled, $event_id]);
  } else {
    $pdo->prepare("INSERT INTO event_seating (event_id, venue_id, total_seats, seats_booked, waitlist_enabled) VALUES (?,?,?,?,?)")
      ->execute([$event_id, $venue_id, $total_seats, $seats_booked, $waitlist_enabled]);
  }
  header('Location: event_seating.php');
  exit;
}

if ($action === 'delete' && !empty($_GET['id'])) {
  $eid = intval($_GET['id']);
  $pdo->prepare("DELETE FROM event_seating WHERE event_id = ?")->execute([$eid]);
  header('Location: event_seating.php');
  exit;
}

$rows = $pdo->query("SELECT es.*, e.title FROM event_seating es LEFT JOIN events e ON es.event_id = e.event_id ORDER BY es.event_id DESC")->fetchAll();
$events = $pdo->query("SELECT event_id, title FROM events ORDER BY event_date DESC")->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Event Seating</title>
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
    <h2 class="bg-secondary p-2 rounded">Event Seating</h2>
    <a class="btn btn-secondary" href="?action=form">Add / Edit Seating</a>
    <table class="table table-bordered table-stripped table-dark table-hover" style="margin-top:1rem">
      <thead>
        <tr>
          <th>Event ID</th>
          <th>Event</th>
          <th>Total Seats</th>
          <th>Booked</th>
          <th>Waitlist</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['event_id']) ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['total_seats']) ?></td>
            <td><?= htmlspecialchars($r['seats_booked']) ?></td>
            <td><?= $r['waitlist_enabled'] ? 'Yes' : 'No' ?></td>
            <td><a class="btn btn-danger" href="?action=delete&id=<?= $r['event_id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($action === 'form'): ?>
    <div class="container col-7" style="margin-top:1rem">
      <h3 class="bg-secondary p-2 rounded">Add / Edit Seating</h3>
      <form method="post" action="event_seating.php?action=save">
        <label>Event</label><select class="input" name="event_id" required><?php foreach ($events as $e): ?><option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></option><?php endforeach; ?></select>
        <label>Venue ID (optional)</label><input class="input" name="venue_id">
        <label>Total Seats</label><input class="input" type="number" name="total_seats" value="0">
        <label>Seats Booked</label><input class="input" type="number" name="seats_booked" value="0">
        <label><input type="checkbox" name="waitlist_enabled" checked> Waitlist Enabled</label>
        <div style="margin-top:.6rem"><button class="btn btn-success">Save</button></div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>