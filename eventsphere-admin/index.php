<?php
require 'auth.php';
require_admin();

$tables = ['users', 'events', 'registrations', 'feedback'];
$counts = [];
foreach ($tables as $t) {
  $counts[$t] = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
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

<body class="bg-light">
  <div class="sidebar col-2">
    <div class="brand">EventSphere</div>
    <ul >
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="pages/users.php">Users</a></li>
      <li><a href="pages/events.php">Events</a></li>
      <li><a href="pages/registrations.php">Registrations</a></li>
      <li><a href="pages/attendance.php">Attendance</a></li>
      <li><a href="pages/feedback.php">Feedback</a></li>
      <li><a href="pages/certificates.php">Certificates</a></li>
      <li><a href="pages/media_gallery.php">Media</a></li>
      <li><a href="pages/event_seating.php">Seating</a></li>
      <li><a href="pages/event_waitlist.php">Waitlist</a></li>
      <li><a href="pages/calendar_sync.php">Calendar Sync</a></li>
      <li><a href="pages/event_share_log.php">Share Log</a></li>
      <li><a href="logout.php">Logout</a></li>

    </ul>
    
  </div>
  <div class="w-100 bg-dark text-white">
  <div class="container col-8 " style="height: 1000px;">
    <h1>Dashboard</h1>
    <div class="card bg-secondary">Users: <?= intval($counts['users']) ?></div>
    <div class="card bg-secondary">Events: <?= intval($counts['events']) ?></div>
    <div class="card bg-secondary">Registrations: <?= intval($counts['registrations']) ?></div>
    <div class="card bg-secondary">Feedback: <?= intval($counts['feedback']) ?></div>
  </div>
  </div>
</body>

</html>