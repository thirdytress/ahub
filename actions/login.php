<?php
session_start();
require_once "../classes/database.php";
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'tenant'; // tenant or admin

    $result = $db->loginUser($username, $password, $role);

    if ($result === "tenant") {
        echo "<script>alert('Login successful!'); window.location.href='../tenant/dashboard.php';</script>";
    } elseif ($result === "admin") {
        echo "<script>alert('Welcome Admin!'); window.location.href='../admin/dashboard.php';</script>";
    } else {
        echo "<script>alert('{$result}'); window.history.back();</script>";
    }
}
?>
