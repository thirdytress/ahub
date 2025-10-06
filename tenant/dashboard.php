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

$conn = $db->connect();

// --- Fetch available apartments ---
$stmt = $conn->prepare("SELECT * FROM apartments WHERE Status='Available' ORDER BY DateAdded DESC");
$stmt->execute();
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch tenant applications ---
$stmt = $conn->prepare("
    SELECT a.application_id, a.status as app_status, a.date_applied,
           p.Name as apartment_name, p.Location, p.MonthlyRate
    FROM applications a
    JOIN apartments p ON a.apartment_id = p.ApartmentID
    WHERE a.tenant_id = :tenant_id
    ORDER BY a.date_applied DESC
");
$stmt->bindParam(':tenant_id', $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="#">ApartmentHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTenant">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTenant">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#myApplications">My Applications</a></li>
        <li class="nav-item"><a class="nav-link" href="#profile">Profile</a></li>
        <li class="nav-item"><a href="../logout.php" class="btn btn-outline-danger btn-sm ms-2">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="card p-4 mb-4">
    <h3 class="text-primary">Welcome, <?= $fullname; ?>!</h3>
    <hr>
    <p>This is your tenant dashboard. You can browse available apartments and track your applications here.</p>

    <div class="mt-3">
      <a href="#availableApartments" class="btn btn-primary me-2">View Apartments</a>
      <a href="#myApplications" class="btn btn-outline-primary">My Applications</a>
    </div>
  </div>

  <!-- AVAILABLE APARTMENTS -->
  <div id="availableApartments" class="card p-4 mb-4">
    <h4 class="text-secondary mb-3">Available Apartments</h4>
    <?php if ($apartments): ?>
      <div class="row">
        <?php foreach ($apartments as $apt): ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100 p-3">
            <h5><?= htmlspecialchars($apt['Name']); ?></h5>
            <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($apt['Location']); ?></p>
            <p class="mb-1"><strong>Rate:</strong> $<?= number_format($apt['MonthlyRate'],2); ?>/month</p>
            <a href="apply_apartment.php?apartment_id=<?= $apt['ApartmentID']; ?>" class="btn btn-success btn-sm">Apply Now</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted">No apartments available at the moment.</p>
    <?php endif; ?>
  </div>

  <!-- MY APPLICATIONS -->
  <div id="myApplications" class="card p-4 mb-4">
    <h4 class="text-secondary mb-3">My Applications</h4>
    <?php if ($applications): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Apartment</th>
              <th>Location</th>
              <th>Monthly Rate</th>
              <th>Date Applied</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($applications as $i => $app): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($app['apartment_name']) ?></td>
              <td><?= htmlspecialchars($app['Location']) ?></td>
              <td>$<?= number_format($app['MonthlyRate'],2) ?></td>
              <td><?= date('M d, Y H:i', strtotime($app['date_applied'])) ?></td>
              <td>
                <?php if ($app['app_status'] === 'Pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($app['app_status'] === 'Approved'): ?>
                  <span class="badge bg-success">Approved</span>
                <?php else: ?>
                  <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">You haven't applied to any apartments yet.</p>
    <?php endif; ?>
  </div>

  <!-- PROFILE SECTION -->
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
