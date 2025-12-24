<?php
// Database configuration
$host = "localhost";
$db_name = "Tourism";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create password_reset_tokens table
    $sql = "
    CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $conn->exec($sql);
    
    echo "Database table created successfully!<br>";
    echo "You can now use the forgot password feature at: <a href='forgot_password.php'>forgot_password.php</a>";
    
} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>