<?php
session_start();
require_once "../classes/database.php";
$db = new Database();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$apartments = $db->getAllApartments();
$message = "";

if (isset($_GET['apply'])) {
    $result = $db->applyApartment($_SESSION['user_id'], $_GET['apply']);
    $message = $result === true ? "Application submitted!" : $result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Apartments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-primary mb-4">Available Apartments</h2>
    <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <div class="row">
        <?php foreach ($apartments as $a): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if ($a['Image']): ?>
                    <img src="../<?= htmlspecialchars($a['Image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h5><?= htmlspecialchars($a['Name']) ?></h5>
                    <p><?= htmlspecialchars($a['Location']) ?></p>
                    <p><strong>₱<?= number_format($a['MonthlyRate'], 2) ?>/month</strong></p>
                    <?php if ($a['Status'] === 'Available'): ?>
                        <a href="?apply=<?= $a['ApartmentID'] ?>" class="btn btn-primary w-100">Apply</a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>Occupied</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
