<?php
session_start();

// Database configuration
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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
try {
    $stmt = $conn->prepare("SELECT firstname, lastname, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.html');
        exit;
    }
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .profile-card {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .profile-content {
            padding: 40px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-group {
            margin-bottom: 20px;
        }
        
        .info-group label {
            display: block;
            margin-bottom: 6px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .info-group .value {
            font-size: 16px;
            color: #333;
            padding: 10px 0;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e1e5e9;
        }
        
        .action-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .action-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #e1e5e9;
        }
        
        .action-btn.secondary:hover {
            background: #e9ecef;
        }
        
        .action-btn.danger {
            background: #fff5f5;
            color: #dc3545;
            border: 1px solid #f5c6cb;
        }
        
        .action-btn.danger:hover {
            background: #f8d7da;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e7f4ff;
            color: #0066cc;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        @media (max-width: 600px) {
            .profile-content {
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Profile</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['firstname']); ?>!</p>
        </div>
        
        <div class="profile-card">
            <div class="profile-content">
                <div class="info-grid">
                    <div>
                        <div class="info-group">
                            <label>Full Name</label>
                            <div class="value"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <label>Email Address</label>
                            <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <label>Phone Number</label>
                            <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="info-group">
                            <label>Account Role</label>
                            <div class="value">
                                <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <label>Member Since</label>
                            <div class="value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <label>Account Security</label>
                            <div class="value">
                                <small>Last password change: Not tracked</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="actions">
                    <a href="change_password.php" class="action-btn primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Change Password
                    </a>
                    
                    <a href="edit_profile.php" class="action-btn secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Profile
                    </a>
                    
                    <a href="logout.php" class="action-btn danger">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>