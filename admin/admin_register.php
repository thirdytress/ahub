<?php
require_once "../classes/database.php";
$db = new Database();

// ✅ Secret key para ikaw lang ang makaka-access
$secretKey = "supersecret123";

// ❌ Kapag walang key o mali ang key, i-block agad
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die("<h2 style='color:red; text-align:center;'>Access Denied 🔒</h2>");
}

// ✅ Handle admin registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $conn = $db->connect();
        $check = $conn->prepare("SELECT * FROM admins WHERE username = :username OR email = :email");
        $check->bindParam(':username', $username);
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            echo "<script>alert('Username or email already exists!');</script>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (fullname, username, email, password)
                                    VALUES (:fullname, :username, :email, :password)");
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed);

            if ($stmt->execute()) {
                echo "<script>alert('Admin registered successfully!'); window.location.href='dashboard.php';</script>";
            } else {
                echo "<script>alert('Registration failed. Try again.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Admin Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow-lg col-md-6 mx-auto">
    <div class="card-body">
      <h3 class="text-center mb-4">🔐 Admin Registration</h3>
      <form method="POST">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Confirm Password</label>
          <input type="password" name="confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register Admin</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
