<?php
// Database configuration
$host = "localhost";
$db_name = "Tourism";   // Database name
$username = "root";
$password = "";

try {
    // Connect to MySQL server (without specifying DB yet)
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it does not exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // Connect to the newly created database
    $conn->exec("USE `$db_name`");

    // Create users table if it does not exist
    $tableSql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student','admin','teacher','department') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    $conn->exec($tableSql);

} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>
