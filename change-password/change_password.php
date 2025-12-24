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
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle password change request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
        exit;
    }
    
    // Check current password
    try {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
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
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 14px 45px 14px 14px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 5px;
            font-size: 18px;
        }
        
        .password-strength {
            margin-top: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .strength-meter {
            flex: 1;
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: all 0.3s;
        }
        
        .strength-text {
            min-width: 60px;
            font-weight: 500;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            display: block;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        
        .requirements ul {
            list-style: none;
            margin-top: 8px;
        }
        
        .requirements li {
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .requirements li::before {
            content: "•";
            color: #667eea;
        }
        
        .requirement-met {
            color: #28a745;
        }
        
        .requirement-not-met {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Change Password</h1>
            <p>Secure your account with a new password</p>
        </div>
        
        <div class="content">
            <div id="message" class="message" style="display: none;"></div>
            
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="current_password" required placeholder="Enter your current password">
                        <button type="button" class="toggle-password" onclick="togglePassword('current_password')">👁</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="new_password" required minlength="6" placeholder="Enter new password">
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁</button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText">Weak</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" required minlength="6" placeholder="Confirm new password">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁</button>
                    </div>
                </div>
                
                <div class="requirements">
                    <strong>Password Requirements:</strong>
                    <ul>
                        <li id="req-length" class="requirement-not-met">At least 6 characters</li>
                        <li id="req-match" class="requirement-not-met">Passwords match</li>
                        <li id="req-strength" class="requirement-not-met">Medium or strong strength</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn" id="submitBtn" disabled>Change Password</button>
                
                <div class="back-link">
                    <a href="profile.php">← Back to Profile</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        const messageDiv = document.getElementById('message');
        
        // Password strength checker
        newPassword.addEventListener('input', updatePasswordStrength);
        confirmPassword.addEventListener('input', validateForm);
        currentPassword.addEventListener('input', validateForm);
        
        function updatePasswordStrength() {
            const password = newPassword.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const reqStrength = document.getElementById('req-strength');
            
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const strengthPercent = (strength / 5) * 100;
            strengthFill.style.width = strengthPercent + '%';
            
            if (strength <= 2) {
                strengthFill.style.background = '#dc3545';
                strengthText.textContent = 'Weak';
                reqStrength.className = 'requirement-not-met';
            } else if (strength <= 3) {
                strengthFill.style.background = '#ffc107';
                strengthText.textContent = 'Medium';
                reqStrength.className = 'requirement-met';
            } else {
                strengthFill.style.background = '#28a745';
                strengthText.textContent = 'Strong';
                reqStrength.className = 'requirement-met';
            }
            
            validateForm();
        }
        
        function validateForm() {
            const currentPass = currentPassword.value;
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;
            
            // Check length requirement
            const reqLength = document.getElementById('req-length');
            if (newPass.length >= 6) {
                reqLength.className = 'requirement-met';
            } else {
                reqLength.className = 'requirement-not-met';
            }
            
            // Check match requirement
            const reqMatch = document.getElementById('req-match');
            if (newPass && confirmPass && newPass === confirmPass) {
                reqMatch.className = 'requirement-met';
            } else {
                reqMatch.className = 'requirement-not-met';
            }
            
            // Enable submit button only if all requirements are met
            const allMet = 
                currentPass.length > 0 &&
                newPass.length >= 6 &&
                newPass === confirmPass &&
                document.getElementById('req-strength').className === 'requirement-met';
            
            submitBtn.disabled = !allMet;
        }
        
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }
        
        // Form submission
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPass = currentPassword.value;
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Changing Password...';
            
            try {
                const response = await fetch('change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        current_password: currentPass,
                        new_password: newPass,
                        confirm_password: confirmPass
                    })
                });
                
                const data = await response.json();
                
                messageDiv.textContent = data.message;
                messageDiv.className = data.success ? 'message success' : 'message error';
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    // Clear form
                    currentPassword.value = '';
                    newPassword.value = '';
                    confirmPassword.value = '';
                    
                    // Reset strength meter
                    document.getElementById('strengthFill').style.width = '0%';
                    document.getElementById('strengthText').textContent = 'Weak';
                    
                    // Reset requirements
                    document.getElementById('req-length').className = 'requirement-not-met';
                    document.getElementById('req-match').className = 'requirement-not-met';
                    document.getElementById('req-strength').className = 'requirement-not-met';
                    
                    // Keep disabled for security
                    setTimeout(() => {
                        submitBtn.textContent = 'Password Changed!';
                        setTimeout(() => {
                            submitBtn.textContent = 'Change Password';
                            submitBtn.disabled = false;
                            validateForm();
                        }, 2000);
                    }, 500);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Change Password';
                    validateForm();
                }
                
                // Auto-hide message after 5 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
                
            } catch (error) {
                messageDiv.textContent = 'Network error. Please try again.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                
                submitBtn.disabled = false;
                submitBtn.textContent = 'Change Password';
                validateForm();
                
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
            }
        });
        
        // Initial validation
        validateForm();
    </script>
</body>
</html>