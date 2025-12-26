<?php
// backend/login.php
// Handles user login and authentication status check

$host = "localhost";
$db_name = "Tourism";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        echo json_encode(["status" => "success", "message" => "Login successful"]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
    exit;
}

// If GET request, check authentication status
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    header('Content-Type: application/json');
    if (isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "authenticated", "user_id" => $_SESSION['user_id']]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "unauthenticated"]);
    }
    exit;
}
?>
