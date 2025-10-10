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
$stmt = $conn->prepare("SELECT firstname, lastname, username FROM tenants WHERE tenant_id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);
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
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            transition: transform 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: scale(1.03);
        }
        .btn-primary {
            border-radius: 8px;
        }
        .welcome {
            margin-top: 30px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub Tenant</a>
    <div class="d-flex">
      <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container text-center">
    <div class="welcome">
        <h2 class="fw-bold text-primary">
            Welcome, <?= htmlspecialchars($tenant['firstname'] . " " . $tenant['lastname']) ?>!
        </h2>
        <p class="text-muted">Manage your apartment applications, leases, and profile easily.</p>
    </div>

    <div class="row justify-content-center g-4">
        <!-- Available Apartments -->
        <div class="col-md-5 col-lg-3">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-building fs-1 text-primary"></i>
                    <h5 class="card-title mt-3">Available Apartments</h5>
                    <p class="text-muted">View and apply for available units.</p>
                    <a href="view_apartments.php" class="btn btn-primary w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- My Applications -->
        <div class="col-md-5 col-lg-3">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text fs-1 text-success"></i>
                    <h5 class="card-title mt-3">My Applications</h5>
                    <p class="text-muted">Check the status of your applications.</p>
                    <a href="my_applications.php" class="btn btn-primary w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- My Leases -->
        <div class="col-md-5 col-lg-3">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-key fs-1 text-warning"></i>
                    <h5 class="card-title mt-3">My Leases</h5>
                    <p class="text-muted">View your current apartment lease details.</p>
                    <a href="my_leases.php" class="btn btn-primary w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Update Profile -->
        <div class="col-md-5 col-lg-3">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <i class="bi bi-person-circle fs-1 text-info"></i>
                    <h5 class="card-title mt-3">Update Profile</h5>
                    <p class="text-muted">Edit your contact details and password.</p>
                    <a href="update_profile_form.php" class="btn btn-primary w-100">Go</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
