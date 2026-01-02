<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require 'middleware.php';
require 'db.php';

// Only admin can approve/reject
checkRole(['Admin']);

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['id']) || empty($data['action'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Missing id or action"]);
    exit;
}

$id = (int)$data['id'];
$action = $data['action'];

if (!in_array($action, ['approve','reject'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Invalid action"]);
    exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    $stmt = $conn->prepare("UPDATE content SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Content $newStatus"]);
    } else {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>"Failed to update content status"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}

?>
