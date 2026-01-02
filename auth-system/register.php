<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Incomplete data"]);
    exit;
}

// Default role if not provided
$role = isset($data['role']) ? $data['role'] : 'Tourist';
$valid_roles = ['Admin', 'User', 'Guide', 'Tourist'];
if (!in_array($role, $valid_roles)) {
    $role = 'Tourist';
}

$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, role) VALUES (:firstname, :lastname, :email, :password, :role)");

$password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

$stmt->bindParam(":firstname", $data['firstname']);
$stmt->bindParam(":lastname", $data['lastname']);
$stmt->bindParam(":email", $data['email']);
$stmt->bindParam(":password", $password_hash);
$stmt->bindParam(":role", $role);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["status"=>"success","message"=>"User created"]);
} else {
    http_response_code(503);
    echo json_encode(["status"=>"error","message"=>"Unable to create user"]);
}
?>
