<?php
// connect_form_to_database.php
require_once 'auth-system/db.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if ($content === '') {
        die('Content cannot be empty.');
    }
    // Save to MySQL with status 'Pending Approval'
    $sql = "INSERT INTO uploads (content, status, submitted_at) VALUES (:content, :status, NOW())";
    $stmt = $conn->prepare($sql);
    $status = 'Pending Approval';
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    echo 'Content submitted and pending approval.';
} else {
    echo '<form method="POST">';
    echo '<textarea name="content" required placeholder="Enter your content here..."></textarea><br />';
    echo '<button type="submit">Submit</button>';
    echo '</form>';
}
