<?php
session_start();
require_once "../classes/database.php";
$db = new Database();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $rate = $_POST['rate'];

    // Handle Image Upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    if ($db->addApartment($name, $type, $location, $description, $rate, $imagePath)) {
        $message = "Apartment added successfully!";
    } else {
        $message = "Failed to add apartment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Apartment | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-primary mb-3">Add New Apartment</h3>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" class="form-control mb-3" placeholder="Apartment Name" required>
            <input type="text" name="type" class="form-control mb-3" placeholder="Type (e.g., Studio, 2BR)" required>
            <input type="text" name="location" class="form-control mb-3" placeholder="Location" required>
            <textarea name="description" class="form-control mb-3" placeholder="Description"></textarea>
            <input type="number" step="0.01" name="rate" class="form-control mb-3" placeholder="Monthly Rate" required>
            <input type="file" name="image" class="form-control mb-3">
            <button type="submit" class="btn btn-primary w-100">Add Apartment</button>
        </form>
    </div>
</div>
</body>
</html>
