<?php
$plainPassword = 'user_password123';
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
$loginPassword = 'user_password123';
if (password_verify($loginPassword, $hashedPassword)) {
    echo "Login successful!";
} else {
    echo "Invalid credentials.";
}