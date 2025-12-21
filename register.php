<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

// Include DB and validation
include 'db.php';
include 'validate.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}


// Assign variables
$firstname = trim($data['firstname']);
$lastname = trim($data['lastname']);
$email = trim($data['email']);
$phone = trim($data['phone']);
$password = $data['password'];
$role = $data['role'];

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit;
}

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, password, role) 
                        VALUES (:firstname, :lastname, :email, :phone, :password, :role)");
$stmt->bindParam(':firstname', $firstname);
$stmt->bindParam(':lastname', $lastname);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':role', $role);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["status" => "success", "message" => "User registered successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to register user"]);
}
?>
