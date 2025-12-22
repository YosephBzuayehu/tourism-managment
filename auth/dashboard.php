<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
<h1>Welcome, <?= $_SESSION['role'] ?>!</h1>
<p>This is the dashboard.</p>
<a href="logout.php">Logout</a>
