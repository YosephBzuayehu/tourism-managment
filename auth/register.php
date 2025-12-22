<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) exit;

$firstname = trim($data['firstname']);
$lastname  = trim($data['lastname']);
$email     = trim($data['email']);
$phone     = $data['phone'];
$password  = password_hash($data['password'], PASSWORD_DEFAULT);
$role      = $data['role'];

// Check email
$stmt = $conn->prepare("SELECT id FROM users WHERE email=:email");
$stmt->bindParam(':email', $email);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    echo json_encode(["status"=>"error","message"=>"Email exists"]);
    exit;
}

// Insert
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, password, role) 
                        VALUES (:fn, :ln, :email, :phone, :pass, :role)");
$stmt->execute([
    ':fn'=>$firstname,
    ':ln'=>$lastname,
    ':email'=>$email,
    ':phone'=>$phone,
    ':pass'=>$password,
    ':role'=>$role
]);
echo json_encode(["status"=>"success","message"=>"Registered successfully"]);
?>
