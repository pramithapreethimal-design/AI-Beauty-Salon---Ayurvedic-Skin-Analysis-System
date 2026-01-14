<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Error: Please log in first.";
    exit();
}

// Validate analysis ID
$analysis_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$analysis_id || $analysis_id <= 0) {
    http_response_code(400);
    echo "Error: Invalid analysis ID.";
    exit();
}

$user_id = $_SESSION['user_id'];
include 'db.php';

// Verify ownership
$stmt = $conn->prepare("SELECT image_path FROM skin_analysis WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $analysis_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Delete image file securely
    $image_path = "../uploads/" . basename($row['image_path']);
    if (file_exists($image_path)) {
        unlink($image_path);
    }
    
    // Delete database record
    $del_stmt = $conn->prepare("DELETE FROM skin_analysis WHERE id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $analysis_id, $user_id);
    if ($del_stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Error: Database deletion failed.";
    }
    $del_stmt->close();
} else {
    http_response_code(403);
    echo "Error: Analysis not found or access denied.";
}

$stmt->close();
$conn->close();
?>