<?php


require_once 'auth-system/db.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        die('Invalid file type.');
    }
    if ($file['size'] > $maxSize) {
        die('File too large.');
    }

    $safeName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $safeName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Store metadata in MySQL using PDO
        $sql = "INSERT INTO uploads (filename, filepath, filetype, filesize, uploaded_at) VALUES (:filename, :filepath, :filetype, :filesize, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':filename', $safeName);
        $stmt->bindParam(':filepath', $targetPath);
        $stmt->bindParam(':filetype', $file['type']);
        $stmt->bindParam(':filesize', $file['size'], PDO::PARAM_INT);
        $stmt->execute();
        echo 'File uploaded successfully.';
    } else {
        echo 'File upload failed.';
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">';
    echo '<input type="file" name="file" required />';
    echo '<button type="submit">Upload</button>';
    echo '</form>';
}
