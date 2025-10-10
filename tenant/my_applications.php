<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$tenant_id = $_SESSION['user_id'];

$query = "SELECT ap.ApplicationID, a.ApartmentName, a.Location, a.MonthlyRate, ap.Status, ap.DateApplied
          FROM applications ap
          JOIN apartments a ON a.ApartmentID = ap.ApartmentID
          WHERE ap.TenantID = :tenant_id
          ORDER BY ap.DateApplied DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':tenant_id', $tenant_id);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Applications | ApartmentHub</title>
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
    .status {
      font-weight: 500;
      text-transform: capitalize;
    }
    .status.pending { color: orange; }
    .status.approved { color: green; }
    .status.rejected { color: red; }
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
    <h3 class="text-primary mb-4"><i class="bi bi-file-earmark-text me-2"></i>My Applications</h3>

    <?php if (count($applications) > 0): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Apartment</th>
              <th>Status</th>
              <th>Date Applied</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applications as $index => $app): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($app['ApartmentName']) ?></td>
                <td class="status <?= strtolower($app['Status']) ?>"><?= htmlspecialchars($app['Status']) ?></td>
                <td><?= htmlspecialchars($app['DateApplied']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">You haven’t submitted any applications yet.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
