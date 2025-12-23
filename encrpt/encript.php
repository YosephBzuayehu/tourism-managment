<?php

$inputPassword = $_POST['password'];


$storedHash = $user['password'];


if (password_verify($inputPassword, $storedHash)) {
    
} else {
    
}
