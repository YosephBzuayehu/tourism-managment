<?php
session_start();


if (isset($_POST['login'])) {
    $_SESSION['user_id'] = 123;
    echo 'Session started. User logged in.';
    exit;
}


if (isset($_GET['check'])) {
    if (isset($_SESSION['user_id'])) {
        echo 'User is logged in. User ID: ' . $_SESSION['user_id'];
    } else {
        echo 'User is not logged in.';
    }
    exit;
}

// Destroy session (logout)
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    echo 'User logged out. Session destroyed.';
    exit;
}
