<?php
require_once "../classes/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $db = new Database();
    $result = $db->registerTenant($firstname, $lastname, $username, $email, $phone, $password, $confirm_password);

    if ($result === true) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Error: $result'); window.history.back();</script>";
    }
}
?>
