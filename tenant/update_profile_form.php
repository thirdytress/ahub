<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

// Fetch tenant info
$stmt = $conn->prepare("SELECT firstname, lastname, username, email, phone FROM tenants WHERE tenant_id = :id LIMIT 1");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    echo "<script>alert('Tenant not found.'); window.location.href='dashboard.php';</script>";
    exit();
}

$fullname = htmlspecialchars($tenant['firstname'] . ' ' . $tenant['lastname']);
$email = htmlspecialchars($tenant['email']);
$phone = htmlspecialchars($tenant['phone']);
$username = htmlspecialchars($tenant['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile | ApartmentHub</title>
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
    .form-control {
      border-radius: 8px;
    }
    .btn {
      border-radius: 8px;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
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
  <div class="col-md-8 mx-auto">
    <div class="card p-4">
      <h3 class="text-primary mb-3"><i class="bi bi-person-circle me-2"></i>Update Profile</h3>
      <p class="text-muted mb-4">You can update your email, phone number, or password here.</p>

      <form method="POST" action="update_profile.php">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" value="<?= $fullname ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" value="<?= $username ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= $email ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= $phone ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">New Password (optional)</label>
          <input type="password" name="password" class="form-control" placeholder="Enter a new password if you want to change it">
        </div>

        <div class="d-flex justify-content-between">
          <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
