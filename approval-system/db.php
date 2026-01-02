<?php
// db.php - simple mysqli connection using env vars or defaults
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'Tourism';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

// helper: get POST/GET param safely
function input_val($key, $default = null) {
    if (isset($_POST[$key])) return trim($_POST[$key]);
    if (isset($_GET[$key])) return trim($_GET[$key]);
    return $default;
}

?>
