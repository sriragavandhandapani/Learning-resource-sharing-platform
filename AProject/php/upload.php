<?php
require 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle file upload
if (isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filePath = $uploadDir . basename($filename);

    if (move_uploaded_file($fileTmp, $filePath)) {
        $stmt = $conn->prepare("INSERT INTO files (filename, size, path, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $filename, $fileSize, $filePath);
        $stmt->execute();
    } else {
        echo "<p style='color:red'>Failed to upload file.</p>";
    }
}
