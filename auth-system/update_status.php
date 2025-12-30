<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require 'middleware.php';
require 'db.php';

// Only admin can approve/reject
checkRole(['Admin']);

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['id']) || empty($data['action'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Missing id or action"]);
    exit;
}

$id = (int)$data['id'];
$action = $data['action'];

if (!in_array($action, ['approve','reject'])) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Invalid action"]);
    exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';


try {
    // 1. Get current status and creator to notify
    // Correcting table name from 'content' to 'contents' as seen in init_db.php
    $stmtGet = $conn->prepare("SELECT created_by FROM contents WHERE id = :id");
    $stmtGet->bindParam(':id', $id);
    $stmtGet->execute();
    $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        http_response_code(404);
        echo json_encode(["status"=>"error","message"=>"Content not found"]);
        exit;
    }
    $creatorId = $row['created_by'];

    // 2. Update status
    $stmt = $conn->prepare("UPDATE contents SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $msg = "Content $newStatus";

        // 3. Notify Creator
        if ($creatorId) {
             // 3a. Get email
             $stmtUser = $conn->prepare("SELECT email FROM users WHERE id = :uid");
             $stmtUser->bindParam(':uid', $creatorId);
             $stmtUser->execute();
             $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
             
             if ($userRow) {
                 $toEmail = $userRow['email'];
                 $subject = "Your content has been $newStatus";
                 $body = "Hello,\n\nYour content submission has been reviewed and marked as $newStatus.\n\nRegards,\nAdmin Team";
                 $headers = "From: no-reply@tourism.com";
                 
                 // Send Email (suppress errors if no mail server configured)
                 @mail($toEmail, $subject, $body, $headers);
             }

             // 3b. Insert DB Notification
             $notifMsg = "Your content was $newStatus.";
             $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:uid, :msg)");
             $stmtNotif->bindParam(':uid', $creatorId);
             $stmtNotif->bindParam(':msg', $notifMsg);
             $stmtNotif->execute();
        }

        echo json_encode(["status"=>"success","message"=>"Content $newStatus, researcher notified"]);
    } else {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>"Failed to update content status"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}

?>
