<?php
session_start();


if (isset($_POST['login'])) {
    // Example: get role from POST or hardcoded for demo
    $role = isset($_POST['role']) ? $_POST['role'] : 'visitor';
    $_SESSION['user_id'] = 123;
    $_SESSION['role'] = $role;
    // Determine dashboard path based on role
    $dashboards = [
        'visitor' => 'visitor_dashboard.php',
        'reasercher' => 'researcher_dashboard.php',
        'site agent' => 'site_agent_dashboard.php',
        'Admin' => 'admin_dashboard.php'
    ];
    $redirect = isset($dashboards[$role]) ? $dashboards[$role] : 'visitor_dashboard.php';
    echo json_encode([
        'status' => 'success',
        'role' => $role,
        'redirect' => $redirect
    ]);
    exit;
}


if (isset($_GET['check'])) {
    if (isset($_SESSION['user_id'])) {
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'visitor';
        echo json_encode([
            'status' => 'logged_in',
            'user_id' => $_SESSION['user_id'],
            'role' => $role
        ]);
    } else {
        echo json_encode(['status' => 'not_logged_in']);
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
