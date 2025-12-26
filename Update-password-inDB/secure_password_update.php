<?php
// secure_password_update.php

$host = "localhost";
$db_name = "Tourism";   // Database name
$username = "root";
$password = "";

session_start();

try {
    // Connect to MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Please login first"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }
    
    // Validate required fields
    $required_fields = ["current_password", "new_password", "confirm_password"];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "$field is required"]);
            exit;
        }
    }
    
    $currentPass = trim($data['current_password']);
    $newPassword = trim($data['new_password']);
    $confirmPassword = trim($data['confirm_password']);
    
    // 1. Validate new password matches confirmation
    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "New passwords do not match"]);
        exit;
    }
    
    // 2. Validate password length (minimum 6 characters as per your validation)
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters"]);
        exit;
    }
    
    // 3. Validate password is different from current
    if ($currentPass === $newPassword) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "New password cannot be same as current password"]);
        exit;
    }
    
    // 4. Validate role-based password requirements (using your role system)
    try {
        // Get user role first
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userRole = $stmt->fetchColumn();
        
        // Apply role-specific password requirements if needed
        if ($userRole === 'admin') {
            // Admin users might need stronger passwords
            if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                http_response_code(400);
                echo json_encode([
                    "status" => "error", 
                    "message" => "Admin passwords require at least one uppercase letter and one number"
                ]);
                exit;
            }
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error"]);
        exit;
    }
    
    try {
        // 5. Fetch stored password hash (using your table structure)
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "User not found"]);
            exit;
        }
        
        // 6. Verify current password (using your existing password hashing)
        if (!password_verify($currentPass, $user['password'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Current password is incorrect"]);
            exit;
        }
        
        // 7. Check password history (optional enhancement)
        // You could implement password history to prevent reuse
        $historyCheck = $pdo->prepare("
            SELECT password FROM users 
            WHERE id = ? AND password = ?
        ");
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $historyCheck->execute([$userId, $newHash]);
        if ($historyCheck->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Cannot reuse previous password"]);
            exit;
        }
        
        // 8. Hash new password (using your existing method)
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // 9. Update password securely
        $update = $pdo->prepare("
            UPDATE users 
            SET password = ? 
            WHERE id = ?
        ");
        $success = $update->execute([$newHash, $userId]);
        
        if ($success) {
            // Optional: Log password change activity
            $logStmt = $pdo->prepare("
                INSERT INTO password_change_log (user_id, changed_at, ip_address) 
                VALUES (?, NOW(), ?)
            ");
            $logStmt->execute([
                $userId, 
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            // Clear any existing password reset tokens for security
            $clearTokens = $pdo->prepare("
                DELETE FROM password_reset_tokens WHERE email IN (
                    SELECT email FROM users WHERE id = ?
                )
            ");
            $clearTokens->execute([$userId]);
            
            // Optional: Invalidate all sessions except current
            // session_regenerate_id(true);
            
            http_response_code(200);
            echo json_encode([
                "status" => "success", 
                "message" => "Password updated successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update password"]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        exit;
    }
    
} else {
    // If not POST request, show HTML form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update Password</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            
            .password-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 450px;
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
            
            .form-section {
                padding: 30px;
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
            
            .input-wrapper {
                position: relative;
            }
            
            .input-wrapper input {
                width: 100%;
                padding: 14px 45px 14px 15px;
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
                gap: 10px;
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
                font-size: 12px;
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
                transition: all 0.3s;
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
                display: none;
            }
            
            .message.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                display: none;
            }
            
            .requirements {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin-top: 25px;
                font-size: 13px;
                color: #666;
            }
            
            .requirements h3 {
                margin-bottom: 10px;
                font-size: 14px;
                color: #333;
            }
            
            .requirements ul {
                list-style: none;
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
            
            .back-link {
                text-align: center;
                margin-top: 20px;
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
        </style>
    </head>
    <body>
        <div class="password-container">
            <div class="header">
                <h1>Update Password</h1>
                <p>Secure your account with a new password</p>
            </div>
            
            <div class="form-section">
                <div id="message" class="message"></div>
                
                <form id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="current_password" required placeholder="Enter current password">
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
                        <h3>Password Requirements:</h3>
                        <ul>
                            <li id="req-current">Current password must be correct</li>
                            <li id="req-length">At least 6 characters</li>
                            <li id="req-match">Passwords must match</li>
                            <li id="req-different">New password different from current</li>
                            <li id="req-strength">Medium or strong strength</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn" id="submitBtn" disabled>Update Password</button>
                    
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
                
                // Complexity checks (using your validation patterns)
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
                
                // Update requirement indicators
                document.getElementById('req-length').className = 
                    newPass.length >= 6 ? 'requirement-met' : 'requirement-not-met';
                
                document.getElementById('req-match').className = 
                    (newPass && confirmPass && newPass === confirmPass) ? 'requirement-met' : 'requirement-not-met';
                
                document.getElementById('req-different').className = 
                    (currentPass && newPass && currentPass !== newPass) ? 'requirement-met' : 'requirement-not-met';
                
                // Enable submit button only if all requirements are met
                const allMet = 
                    currentPass.length > 0 &&
                    newPass.length >= 6 &&
                    newPass === confirmPass &&
                    currentPass !== newPass &&
                    document.getElementById('req-strength').className === 'requirement-met';
                
                submitBtn.disabled = !allMet;
                return allMet;
            }
            
            // Toggle password visibility
            function togglePassword(inputId) {
                const input = document.getElementById(inputId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
            }
            
            // Form submission
            document.getElementById('passwordForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    showMessage('Please meet all requirements', 'error');
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';
                
                try {
                    const response = await fetch('secure_password_update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            current_password: currentPassword.value,
                            new_password: newPassword.value,
                            confirm_password: confirmPassword.value
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        showMessage(data.message, 'success');
                        
                        // Clear form
                        currentPassword.value = '';
                        newPassword.value = '';
                        confirmPassword.value = '';
                        
                        // Reset UI
                        document.getElementById('strengthFill').style.width = '0%';
                        document.getElementById('strengthText').textContent = 'Weak';
                        
                        // Reset requirements
                        document.querySelectorAll('.requirements li').forEach(li => {
                            li.className = 'requirement-not-met';
                        });
                        
                        // Update button
                        submitBtn.textContent = 'Password Updated!';
                        setTimeout(() => {
                            submitBtn.textContent = 'Update Password';
                            submitBtn.disabled = true;
                        }, 2000);
                        
                        // Optional: Redirect after 3 seconds
                        // setTimeout(() => {
                        //     window.location.href = 'profile.php';
                        // }, 3000);
                    } else {
                        showMessage(data.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Update Password';
                    }
                    
                } catch (error) {
                    showMessage('Network error. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Update Password';
                }
            });
            
            function showMessage(text, type) {
                messageDiv.textContent = text;
                messageDiv.className = `message ${type}`;
                messageDiv.style.display = 'block';
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
            }
            
            // Initial validation
            validateForm();
        </script>
    </body>
    </html>
    <?php
}
?>