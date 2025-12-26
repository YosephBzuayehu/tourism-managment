<?php
require 'middleware.php';

// Only Admin can access this
checkRole(['Admin']);

echo json_encode(["status" => "success", "message" => "Welcome Admin!"]);
?>
