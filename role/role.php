<?php
session_start();
include 'db.php'; // Optional: use if DB connection is separate

// ======== LOGOUT ========
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

        // ===== Role-Based Redirection =====
        switch ($user['role']) {
            case 'admin':
                header("Location: ?dashboard=admin");
                exit;
            case 'site agent':
                header("Location: ?dashboard=agent");
                exit;
            case 'researcher':
                header("Location: ?dashboard=researcher");
                exit;
            case 'visitor':
                header("Location: ?dashboard=visitor");
                exit;
            default:
                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
        }
    } else {
        $error = "Invalid email or password";
    }
}

// ======== DASHBOARD PAGES ========
if (isset($_SESSION['logged_in'])):

    $dashboard = $_GET['dashboard'] ?? '';

    echo "<h1>Dashboard</h1>";
    echo "<p>Role: <strong>".$_SESSION['role']."</strong></p>";

    switch ($_SESSION['role']) {
        case 'admin':
            echo "<p>Admin features here</p>";
            break;
        case 'site agent':
            echo "<p>Site Agent features here</p>";
            break;
        case 'researcher':
            echo "<p>Researcher features here</p>";
            break;
        case 'visitor':
            echo "<p>Visitor features here</p>";
            break;
        default:
            echo "<p>General dashboard</p>";
    }

    echo '<p><a href="?logout=true">Logout</a></p>';

else: ?>
    <!DOCTYPE html>
    <html>
    <head><title>Login</title></head>
    <body>
        <h2>User Login</h2>
        <?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
<?php endif; ?>
