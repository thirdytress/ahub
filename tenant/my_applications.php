<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$applications = $db->getTenantApplications($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Applications | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .status.pending { color: orange; }
    .status.approved { color: green; }
    .status.rejected { color: red; }
  </style>
</head>
<body>
<div class="container mt-5">
  <h3 class="text-primary mb-4">My Applications</h3>

  <?php if ($applications): ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle bg-white">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Apartment</th>
            <th>Location</th>
            <th>Status</th>
            <th>Date Applied</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $i => $a): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($a['apartment_name']) ?></td>
              <td><?= htmlspecialchars($a['Location']) ?></td>
              <td class="status <?= strtolower($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></td>
              <td><?= htmlspecialchars($a['date_applied']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted">You have not submitted any applications yet.</p>
  <?php endif; ?>
</div>
</body>
</html>
