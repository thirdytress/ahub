<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];

// 🧭 Fetch tenant profile
$tenant = $db->getTenantProfile($tenant_id);
$message = "";

// 📝 Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $result = $db->updateTenantProfile($tenant_id, $firstname, $lastname, $username, $email, $phone, $password, $confirm);

    if ($result === true) {
        $message = "✅ Profile updated successfully!";
        $tenant = $db->getTenantProfile($tenant_id); // refresh data
    } else {
        $message = "⚠️ " . $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile | ApartmentHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="card p-4">
    <h3 class="text-primary mb-3">Update My Profile</h3>

    <?php if ($message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">First Name</label>
          <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($tenant['firstname']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Last Name</label>
          <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($tenant['lastname']) ?>" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($tenant['username']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($tenant['email']) ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($tenant['phone']) ?>">
      </div>

      <hr>
      <p class="text-muted mb-2"><strong>Change Password (optional)</strong></p>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
    </form>
  </div>
</div>
</body>
</html>
