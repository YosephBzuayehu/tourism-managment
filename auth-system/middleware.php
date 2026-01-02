<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkRole($allowed_roles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized - Please login"]);
        exit;
    }

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Forbidden - Insufficient permissions"]);
        exit;
    }
}
?>
