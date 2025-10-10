<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
$tenant = $db->getTenantProfile($tenant_id);

// Stats
$conn = $db->connect();

// Applications count
$appCount = $conn->prepare("SELECT COUNT(*) FROM applications WHERE tenant_id = :id");
$appCount->execute([':id' => $tenant_id]);
$totalApplications = $appCount->fetchColumn();

// Leases count
$leaseCount = $conn->prepare("SELECT COUNT(*) FROM leases WHERE tenant_id = :id");
$leaseCount->execute([':id' => $tenant_id]);
$totalLeases = $leaseCount->fetchColumn();

// Notifications count
$notifCount = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE tenant_id = :id AND status = 'Unread'");
$notifCount->execute([':id' => $tenant_id]);
$unreadNotifs = $notifCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Dashboard | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-card {
      border-radius: 15px;
      background: linear-gradient(145deg, #ffffff, #f1f3f5);
      text-align: center;
      padding: 25px 15px;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .stat-card p {
      margin: 0;
      font-size: 0.95rem;
      color: #555;
    }
    .go-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 5px 15px;
      font-weight: 600;
      background-color: #0d6efd;
      color: white;
      border-radius: 8px;
      text-decoration: none;
    }
    .go-btn:hover {
      background-color: #0b5ed7;
      color: #fff;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Tenant</a>
    <div class="d-flex">
      <a href="view_apartments.php" class="btn btn-outline-primary btn-sm me-2">
        <i class="bi bi-building"></i> Apartments
      </a>
      <a href="notifications.php" class="btn btn-outline-warning btn-sm me-2">
        <i class="bi bi-bell"></i> Notifications 
        <?php if ($unreadNotifs > 0): ?>
          <span class="badge bg-danger"><?= $unreadNotifs ?></span>
        <?php endif; ?>
      </a>
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container">
  <!-- Welcome -->
  <div class="text-center mb-4">
    <h2 class="fw-bold text-primary">Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</h2>
    <p class="text-muted">Manage your profile, applications, and leases here.</p>
  </div>

  <!-- Profile Summary Card -->
  <div class="card mb-5 p-4">
    <h4 class="text-primary mb-3"><i class="bi bi-person-circle me-2"></i>Your Profile Summary</h4>
    <div class="row">
      <div class="col-md-6 mb-2">
        <strong>Full Name:</strong> <?= htmlspecialchars($tenant['firstname'] . " " . $tenant['lastname']) ?>
      </div>
      <div class="col-md-6 mb-2">
        <strong>Username:</strong> <?= htmlspecialchars($tenant['username']) ?>
      </div>
      <div class="col-md-6 mb-2">
        <strong>Email:</strong> <?= htmlspecialchars($tenant['email']) ?>
      </div>
      <div class="col-md-6 mb-2">
        <strong>Phone:</strong> <?= htmlspecialchars($tenant['phone']) ?>
      </div>
    </div>
    <div class="mt-3">
      <a href="update_profile.php" class="btn btn-primary">
        <i class="bi bi-pencil-square"></i> Edit Profile
      </a>
    </div>
  </div>

  <!-- Stat Boxes -->
  <div class="row text-center">
    <!-- Applications -->
    <div class="col-md-4 mb-4">
      <div class="stat-card">
        <i class="bi bi-file-earmark-text text-primary fs-2 mb-2"></i>
        <h3><?= $totalApplications ?></h3>
        <p>Total Applications</p>
        <a href="my_applications.php" class="go-btn">GO</a>
      </div>
    </div>

    <!-- Leases -->
    <div class="col-md-4 mb-4">
      <div class="stat-card">
        <i class="bi bi-house-door text-success fs-2 mb-2"></i>
        <h3><?= $totalLeases ?></h3>
        <p>Active Leases</p>
        <a href="my_leases.php" class="go-btn">GO</a>
      </div>
    </div>

    <!-- Notifications -->
    <div class="col-md-4 mb-4">
      <div class="stat-card">
        <i class="bi bi-bell text-warning fs-2 mb-2"></i>
        <h3><?= $unreadNotifs ?></h3>
        <p>Unread Notifications</p>
        <a href="notifications.php" class="btn btn-outline-warning btn-sm mt-2">
          <i class="bi bi-arrow-right-circle"></i> View
        </a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
