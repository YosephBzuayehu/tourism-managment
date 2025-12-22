<?php
session_start();
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) exit;

$email = trim($data['email']);
$password = $data['password'];

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email=:email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(["status"=>"success","message"=>"Login successful"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Invalid email or password"]);
}
?>
