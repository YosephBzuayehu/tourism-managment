<?php
session_start();

// Database configuration
$host = "localhost";
$db_name = "Tourism";
$username = "root";
$password = "";
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create password_reset_tokens table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token)
        )
    ");
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'request_reset') {
            // Request password reset
            $email = trim($_POST['email']);
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, firstname FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete old tokens for this email
                $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?")->execute([$email]);
                
                // Save new token
                $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires_at]);
                
                // Create reset link (for demo, we'll show it on the page)
                $reset_link = "$base_url/forgot_password.php?action=reset&token=$token";
                
                echo json_encode([
                    'success' => true,
                    'message' => "Password reset link generated! For demo: <a href='$reset_link'>Click here to reset</a>",
                    'demo_link' => $reset_link
                ]);
                
                // In production, send email:
                // mail($email, "Password Reset", "Click here to reset: $reset_link");
                
            } else {
                echo json_encode(['success' => false, 'message' => 'Email not found']);
            }
            
        } elseif ($action === 'reset_password') {
            // Reset password
            $token = $_POST['token'];
            $new_password = $_POST['new_password'];
            
            // Validate token
            $stmt = $conn->prepare("
                SELECT email FROM password_reset_tokens 
                WHERE token = ? AND expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $token_data = $stmt->fetch();
            
            if ($token_data) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $email = $token_data['email'];
                
                // Update password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);
                
                // Delete used token
                $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?")->execute([$token]);
                
                echo json_encode(['success' => true, 'message' => 'Password reset successful!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            }
        }
    }
    exit;
}

// Handle GET requests
if (isset($_GET['action']) && $_GET['action'] === 'reset' && isset($_GET['token'])) {
    // Show reset password form
    $token = $_GET['token'];
    
    // Validate token
    $stmt = $conn->prepare("
        SELECT email FROM password_reset_tokens 
        WHERE token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $token_valid = $stmt->fetch();
    
    if (!$token_valid) {
        $error = "Invalid or expired reset link.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
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
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            display: none;
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
        
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .strength-bar {
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
            transition: width 0.3s, background 0.3s;
        }
        
        .password-container {
            position: relative;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Password</h1>
            <p>Secure your account with a new password</p>
        </div>
        
        <div class="content">
            <?php if (isset($_GET['action']) && $_GET['action'] === 'reset'): ?>
                <!-- Step 2: Reset Password Form -->
                <div id="step-reset" class="form-step active">
                    <?php if (isset($error)): ?>
                        <div class="message error"><?php echo $error; ?></div>
                        <div class="back-link">
                            <a href="forgot_password.php">← Back to Request Reset</a>
                        </div>
                    <?php else: ?>
                        <form id="resetForm">
                            <input type="hidden" id="resetToken" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="password-container">
                                    <input type="password" id="new_password" required minlength="6" placeholder="Enter new password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁</button>
                                </div>
                                <div class="password-strength">
                                    <span>Strength:</span>
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <span id="strengthText">Weak</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="password-container">
                                    <input type="password" id="confirm_password" required minlength="6" placeholder="Confirm new password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁</button>
                                </div>
                                <div id="confirmError" class="message error" style="display: none;"></div>
                            </div>
                            
                            <div id="resetMessage" class="message" style="display: none;"></div>
                            
                            <button type="submit" class="btn">Reset Password</button>
                            
                            <div class="back-link">
                                <a href="forgot_password.php">← Back to Request Reset</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Step 1: Request Reset Form -->
                <div id="step-request" class="form-step active">
                    <form id="requestForm">
                        <div class="message info">
                            Enter your email address and we'll send you a link to reset your password.
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" required placeholder="Enter your registered email">
                        </div>
                        
                        <div id="requestMessage" class="message" style="display: none;"></div>
                        
                        <button type="submit" class="btn">Send Reset Link</button>
                        
                        <div class="back-link">
                            <a href="login.html">← Back to Login</a>
                        </div>
                    </form>
                </div>
                
                <!-- Step 3: Success Message -->
                <div id="step-success" class="form-step">
                    <div class="message success" id="successMessage"></div>
                    
                    <div class="back-link">
                        <a href="login.html">← Back to Login</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if (isset($_GET['action']) && $_GET['action'] === 'reset' && !isset($error)): ?>
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
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
            } else if (strength <= 3) {
                strengthFill.style.background = '#ffc107';
                strengthText.textContent = 'Medium';
            } else {
                strengthFill.style.background = '#28a745';
                strengthText.textContent = 'Strong';
            }
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const confirmError = document.getElementById('confirmError');
            
            if (confirmPassword && newPassword !== confirmPassword) {
                confirmError.textContent = 'Passwords do not match';
                confirmError.style.display = 'block';
            } else {
                confirmError.style.display = 'none';
            }
        });
        
        // Reset form submission
        document.getElementById('resetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = document.getElementById('resetToken').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const messageDiv = document.getElementById('resetMessage');
            
            // Validation
            if (newPassword.length < 6) {
                messageDiv.textContent = 'Password must be at least 6 characters';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                return;
            }
            
            if (newPassword !== confirmPassword) {
                messageDiv.textContent = 'Passwords do not match';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                return;
            }
            
            // Submit
            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('token', token);
            formData.append('new_password', newPassword);
            
            try {
                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                messageDiv.textContent = data.message;
                messageDiv.className = data.success ? 'message success' : 'message error';
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 3000);
                }
            } catch (error) {
                messageDiv.textContent = 'Network error. Please try again.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            }
        });
        <?php else: ?>
        // Request form submission
        document.getElementById('requestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const messageDiv = document.getElementById('requestMessage');
            
            if (!email) {
                messageDiv.textContent = 'Please enter your email address';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                return;
            }
            
            // Basic email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                messageDiv.textContent = 'Please enter a valid email address';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
                return;
            }
            
            // Submit
            const formData = new FormData();
            formData.append('action', 'request_reset');
            formData.append('email', email);
            
            try {
                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    document.getElementById('successMessage').innerHTML = data.message;
                    document.getElementById('step-request').classList.remove('active');
                    document.getElementById('step-success').classList.add('active');
                    
                    // If demo link is provided (for demo purposes)
                    if (data.demo_link) {
                        const demoLink = document.createElement('p');
                        demoLink.style.marginTop = '10px';
                        demoLink.innerHTML = `<strong>Demo link:</strong> <a href="${data.demo_link}" target="_blank">${data.demo_link}</a>`;
                        document.getElementById('successMessage').appendChild(demoLink);
                    }
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.textContent = 'Network error. Please try again.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            }
        });
        <?php endif; ?>
        
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }
    </script>
</body>
</html>