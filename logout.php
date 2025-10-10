<?php
require_once "classes/database.php";
$db = new Database();
$db->logout();
header("Location: index.php"); // redirect sa login page
exit();
?>