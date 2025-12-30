<?php
// init_db.php - create database (if needed) and contents table
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'Tourism';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($conn->connect_errno) {
    die('Connection failed: ' . $conn->connect_error);
}

// create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS `" . $conn->real_escape_string($DB_NAME) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!$conn->query($sql)) {
    die('Failed to create database: ' . $conn->error);
}

// select DB and create table
$conn->select_db($DB_NAME);

$create = "CREATE TABLE IF NOT EXISTS `contents` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($create)) {
    die('Failed to create contents table: ' . $conn->error);
}

$createNotif = "CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($createNotif)) {
    die('Failed to create notifications table: ' . $conn->error);
}

echo "Initialization complete. Table `contents` exists in database `" . htmlspecialchars($DB_NAME) . "`.";

?>
