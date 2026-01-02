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


// 1. Get creator
$creatorId = 0;
$stmtGet = $mysqli->prepare("SELECT created_by FROM contents WHERE id = ?");
if ($stmtGet) {
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $stmtGet->bind_result($creatorId);
    $stmtGet->fetch();
    $stmtGet->close();
}

$stmt = $mysqli->prepare("UPDATE contents SET status = ? WHERE id = ?");
if (!$stmt) { die('Prepare failed: ' . $mysqli->error); }
$stmt->bind_param('si', $newStatus, $id);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);
}

// 2. Notify Creator
if ($creatorId) {
    // 2a. Get email
    $toEmail = "";
    $stmtUser = $mysqli->prepare("SELECT email FROM users WHERE id = ?");
    if ($stmtUser) {
        $stmtUser->bind_param("i", $creatorId);
        $stmtUser->execute();
        $stmtUser->bind_result($toEmail);
        $stmtUser->fetch();
        $stmtUser->close();
    }
    
    if ($toEmail) {
        $subject = "Your content has been $newStatus";
        $body = "Hello,\n\nYour content submission has been reviewed and marked as $newStatus.\n\nRegards,\nAdmin Team";
        $headers = "From: no-reply@tourism.com";
        @mail($toEmail, $subject, $body, $headers);
    }

    // 2b. Insert DB Notification
    $notifMsg = "Your content was $newStatus.";
    $stmtNotif = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    if ($stmtNotif) {
        $stmtNotif->bind_param("is", $creatorId, $notifMsg);
        $stmtNotif->execute();
        $stmtNotif->close();
    }
}

// Redirect back to admin UI
header('Location: admin.php');
exit;

?>
