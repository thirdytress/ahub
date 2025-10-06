<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant = $db->getTenantInfo($_SESSION['user_id']);
$fullname = htmlspecialchars($tenant['firstname'] . ' ' . $tenant['lastname']);
$email = htmlspecialchars($tenant['email']);
$phone = htmlspecialchars($tenant['phone']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Dashboard | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTenant">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTenant">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#">My Applications</a></li>
        <li class="nav-item"><a class="nav-link" href="#profile">Profile</a></li>
        <li class="nav-item"><a href="../logout.php" class="btn btn-outline-danger btn-sm ms-2">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Dashboard Section -->
<div class="container mt-4">
  <div class="card p-4 mb-4">
    <h3 class="text-primary">Welcome, <?= $fullname; ?>!</h3>
    <hr>
    <p>This is your tenant dashboard. You can view your applications, manage your profile, and more.</p>

    <div class="mt-3">
      <a href="#" class="btn btn-primary me-2">View Apartments</a>
      <a href="#" class="btn btn-outline-primary">Apply Now</a>
    </div>
  </div>

  <!-- ✅ Tenant Profile Section -->
  <div id="profile" class="card p-4">
    <h4 class="text-secondary mb-3">My Profile</h4>

    <form method="POST" action="update_profile.php">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" value="<?= $fullname; ?>" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($tenant['username']); ?>" readonly>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= $email; ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= $phone; ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">New Password (optional)</label>
        <input type="password" name="password" class="form-control" placeholder="Enter new password if you want to change">
      </div>

      <button type="submit" class="btn btn-success">Update Profile</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
