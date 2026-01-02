<?php
require_once __DIR__ . '/db.php';

// Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($id <= 0 || !in_array($action, ['approve','reject'])) {
    http_response_code(400);
    echo "Invalid parameters.";
    exit;
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';

$stmt = $mysqli->prepare("UPDATE contents SET status = ? WHERE id = ?");
if (!$stmt) { die('Prepare failed: ' . $mysqli->error); }
$stmt->bind_param('si', $newStatus, $id);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);
}

// Redirect back to admin UI
header('Location: admin.php');
exit;

?>
