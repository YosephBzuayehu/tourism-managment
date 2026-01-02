<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'middleware.php';
require 'db.php';

// Only admin can fetch all content for moderation
checkRole(['Admin']);

try {
    $stmt = $conn->prepare("SELECT c.id, c.title, c.body, c.status, c.created_at, u.firstname, u.lastname FROM content c LEFT JOIN users u ON c.author_id = u.id ORDER BY c.created_at DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status"=>"success","data"=>$rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}

?>
