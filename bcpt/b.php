<?php
// secure_password_update.php

$host = "localhost";
$db   = "mydb";
$user = "root";
$pass = "password";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed");
}

// INPUT (example)
$userId        = 1;
$currentPass   = "oldPassword123";
$newPassword   = "NewSecure@123";

// 1. Fetch stored password hash
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}

// 2. Verify current password
if (!password_verify($currentPass, $user['password_hash'])) {
    die("Current password is incorrect");
}

// 3. Hash new password (bcrypt)
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

// 4. Update password securely
$update = $pdo->prepare(
    "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?"
);
$update->execute([$newHash, $userId]);

echo "Password updated securely";
