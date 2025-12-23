<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.html");
    exit;
}
?>

<h1>Dashboard</h1>
<p>Role: <?= $_SESSION['role']; ?></p>

<a href="logout.php">Logout</a>
