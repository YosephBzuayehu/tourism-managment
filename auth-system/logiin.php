<?php
session_start();
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Email and password required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, password, role FROM users WHERE email=:email");
$stmt->bindParam(":email", $data['email']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($data['password'], $user['password'])) {
    http_response_code(401);
    echo json_encode(["status"=>"error","message"=>"Invalid credentials"]);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

unset($user['password']);
echo json_encode(["status"=>"success","user"=>$user]);
