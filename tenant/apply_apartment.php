<?php
session_start();
require_once "../classes/database.php";

// --- Require tenant session ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['apartment_id'])) {
    header("Location: view_apartment.php");
    exit();
}

$apartment_id = (int) $_GET['apartment_id'];
$tenant_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();

// --- Check if apartment exists and is available ---
$stmt = $conn->prepare("SELECT * FROM apartments WHERE ApartmentID=:id AND Status='Available' LIMIT 1");
$stmt->bindParam(':id', $apartment_id);
$stmt->execute();
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apartment) {
    echo "<script>alert('This apartment is no longer available.'); window.location.href='view_apartment.php';</script>";
    exit();
}

// --- Check if tenant has already applied for this apartment ---
$stmt = $conn->prepare("SELECT * FROM applications WHERE tenant_id=:tenant AND apartment_id=:apt LIMIT 1");
$stmt->bindParam(':tenant', $tenant_id);
$stmt->bindParam(':apt', $apartment_id);
$stmt->execute();
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "<script>alert('You have already applied for this apartment.'); window.location.href='view_apartment.php';</script>";
    exit();
}

// --- Insert new application ---
$stmt = $conn->prepare("INSERT INTO applications (tenant_id, apartment_id, status) VALUES (:tenant, :apt, 'Pending')");
$stmt->bindParam(':tenant', $tenant_id);
$stmt->bindParam(':apt', $apartment_id);
$stmt->execute();

echo "<script>alert('Application submitted successfully!'); window.location.href='view_apartment.php#myApplications';</script>";
exit();
