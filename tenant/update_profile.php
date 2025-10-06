<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

$tenant_id = $_SESSION['user_id'];
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';

try {
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE tenants SET email = :email, phone = :phone, password = :password WHERE tenant_id = :id");
        $stmt->bindParam(':password', $hashed);
    } else {
        $stmt = $conn->prepare("UPDATE tenants SET email = :email, phone = :phone WHERE tenant_id = :id");
    }

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':id', $tenant_id);
    $stmt->execute();

    echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
} catch (Exception $e) {
    echo "<script>alert('Error updating profile: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
