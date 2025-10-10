<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

// --- get admin fullname dynamically ---
$fullname = $_SESSION['fullname'] ?? '';
if (empty($fullname)) {
    $admin = $db->getAdminById($_SESSION['user_id']);
    if ($admin) {
        $fullname = $admin['fullname'];
        $_SESSION['fullname'] = $fullname;
        $_SESSION['username'] = $admin['username'] ?? '';
    }
}

// --- fetch counts for dashboard cards ---
$totalTenants = $db->countTenants();
$totalApplications = $db->countApplications();
$totalApartments = $db->countApartments();
$totalLeases = $db->countLeases();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .card { border: none; border-radius: 10px; }
    .dashboard-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
      cursor: pointer;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      background-color: #f0f8ff;
    }
    .dashboard-card .icon {
      font-size: 2rem;
      color: #0d6efd;
      transition: transform 0.3s ease, color 0.3s ease;
    }
    .dashboard-card:hover .icon {
      transform: scale(1.2);
      color: #0b5ed7;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Admin</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="card p-4">
    <h3 class="text-primary">Welcome, <?= htmlspecialchars($fullname ?: 'Admin'); ?>!</h3>
    <hr>
    <p>This is your admin dashboard. You can manage tenants, applications, and apartment listings here.</p>

    <div class="row mt-4">
      <!-- Manage Tenants -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2">
            <i class="bi bi-people-fill icon"></i>
          </div>
          <h5>Manage Tenants</h5>
          <p class="small text-muted"><?= $totalTenants ?> tenants</p>
          <a href="manage_tenants.php" class="btn btn-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Applications -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2">
            <i class="bi bi-file-earmark-text-fill icon"></i>
          </div>
          <h5>View Applications</h5>
          <p class="small text-muted"><?= $totalApplications ?> applications</p>
          <a href="view_applications.php" class="btn btn-outline-primary btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Add Apartment -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2">
            <i class="bi bi-building-fill icon"></i>
          </div>
          <h5>Add Apartment</h5>
          <p class="small text-muted"><?= $totalApartments ?> apartments</p>
          <a href="add_apartment.php" class="btn btn-success btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- Change Password -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2">
            <i class="bi bi-key-fill icon"></i>
          </div>
          <h5>Change Password</h5>
          <p class="small text-muted">Secure your account</p>
          <a href="change_password.php" class="btn btn-warning btn-sm mt-auto">Go</a>
        </div>
      </div>

      <!-- View Leases -->
      <div class="col-md-3 mb-3">
        <div class="card text-center h-100 p-3 shadow-sm dashboard-card">
          <div class="mb-2">
            <i class="bi bi-file-text-fill icon"></i>
          </div>
          <h5>View Leases</h5>
          <p class="small text-muted"><?= $totalLeases ?> active leases</p>
          <a href="view_leases.php" class="btn btn-info btn-sm mt-auto">Go</a>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
