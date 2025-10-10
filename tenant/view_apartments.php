<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$tenant_id = $_SESSION['user_id'];
$apartments = $db->getAvailableApartments($tenant_id);
$leases = $db->getTenantLeases($tenant_id);
$message = "";

// Handle apartment application
if (isset($_GET['apply'])) {
    $apartment_id = intval($_GET['apply']);
    $result = $db->applyApartment($tenant_id, $apartment_id);

    if ($result === true) {
        $message = "✅ Application submitted successfully!";
    } else {
        $message = "⚠️ " . $result;
    }

    // Refresh available apartments after applying
    $apartments = $db->getAvailableApartments($tenant_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Apartments | ApartmentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">

    <h2 class="text-primary mb-4">Available Apartments</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row mb-5">
        <?php if ($apartments): ?>
            <?php foreach ($apartments as $a): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($a['Image'])): ?>
                            <img src="../<?= htmlspecialchars($a['Image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5><?= htmlspecialchars($a['Name']) ?></h5>
                            <p><?= htmlspecialchars($a['Location']) ?></p>
                            <p><strong>₱<?= number_format($a['MonthlyRate'], 2) ?>/month</strong></p>
                            <a href="?apply=<?= $a['ApartmentID'] ?>" class="btn btn-primary mt-auto w-100">Apply</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No available apartments right now.</p>
        <?php endif; ?>
    </div>

    <h2 class="text-primary mb-4">My Current Leases</h2>
    <?php if ($leases): ?>
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Apartment</th>
                        <th>Location</th>
                        <th>Monthly Rate</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leases as $i => $l): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($l['apartment_name']) ?></td>
                            <td><?= htmlspecialchars($l['Location']) ?></td>
                            <td>₱<?= number_format($l['MonthlyRate'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($l['start_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($l['end_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You have no active leases.</p>
    <?php endif; ?>

</div>
</body>
</html>
