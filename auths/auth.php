<?php
session_start();

// ======== DATABASE CONNECTION ========
$host = "localhost";
$db   = "Tourism";
$user = "root";
$pass = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// ======== LOGOUT HANDLER ========
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ======== LOGIN HANDLER ========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Invalid email or password";
    }
}

// ======== DASHBOARD / LOGIN FORM ========
if (isset($_SESSION['logged_in'])):
    $role = $_SESSION['role'];
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Dashboard</title></head>
    <body>
        <h1>Dashboard</h1>
        <p>Welcome, your role is: <strong><?= htmlspecialchars($role) ?></strong></p>

        <?php
        switch ($role) {
            case 'admin':
                echo "<p>Admin features here</p>";
                break;
            case 'agent':
                echo "<p>Site Agent features here</p>";
                break;
            case 'visitor':
                echo "<p>Visitor features here</p>";
                break;
            default:
                echo "<p>Other role features</p>";
        }
        ?>

        <p><a href="?logout=true">Logout</a></p>
    </body>
    </html>
<?php else: ?>
    <!DOCTYPE html>
    <html>
    <head><title>Login</title></head>
    <body>
        <h2>User Login</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
<?php endif; ?>
