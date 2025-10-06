<?php
session_start();
require_once "../classes/database.php";

$db = new Database();

// --- require admin session (unified session keys) ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// --- prefer session fullname, otherwise try to fetch from DB ---
$fullname = $_SESSION['fullname'] ?? '';

if (empty($fullname)) {
    // attempt to fetch from admins table using user_id
    try {
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT fullname, username FROM admins WHERE admin_id = :id LIMIT 1");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $fullname = $row['fullname'];
            // update session for future requests
            $_SESSION['fullname'] = $fullname;
            if (empty($_SESSION['username']) && !empty($row['username'])) {
                $_SESSION['username'] = $row['username'];
            }
        }
    } catch (Exception $e) {
        // fail silently but keep $fullname empty
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; }
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

    <div class="mt-4">
      <a href="#" class="btn btn-primary me-2">Manage Tenants</a>
      <a href="#" class="btn btn-outline-primary">View Applications</a>
      <a href="change_password.php" class="btn btn-warning">Change Password</a>
    </div>
  </div>
</div>

</body>
</html>
