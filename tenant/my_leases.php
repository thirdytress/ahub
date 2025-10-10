<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// 🔹 Use centralized function
$leases = $db->getTenantLeases($tenant_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Leases | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar {
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .lease-period {
      font-size: 0.9rem;
      color: #555;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Tenant</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Back</a>
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="card p-4">
    <h3 class="text-primary mb-4"><i class="bi bi-house-door me-2"></i>My Leases</h3>

    <?php if (!empty($leases)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Apartment</th>
              <th>Location</th>
              <th>Monthly Rate</th>
              <th>Lease Start</th>
              <th>Lease End</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leases as $index => $lease): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($lease['apartment_name']) ?></td>
                <td><?= htmlspecialchars($lease['Location']) ?></td>
                <td>₱<?= number_format($lease['MonthlyRate'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($lease['start_date'])) ?></td>
                <td><?= date('M d, Y', strtotime($lease['end_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">You currently have no active leases.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
