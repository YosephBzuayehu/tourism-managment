<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'db.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->bindParam(':uid', $user_id);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
?>
