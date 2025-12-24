git checkout -b TOUR-11-implement-backend-authentication origin/TOUR-11-implement-backend-authentication
<?php
// logout.php: Destroys the session and logs out the user
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json');
echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
exit;
?>
