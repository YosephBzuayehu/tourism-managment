<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('database/schema.sql');
    $conn->exec($sql);
    
    echo "Database and table created successfully.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
