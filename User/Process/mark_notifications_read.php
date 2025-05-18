<?php
require_once '../../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Mark all as read
    if (empty($_POST['id'])) {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
    } 
    // Mark specific notification as read
    else {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $_POST['id'], $_SESSION['user_id']);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Update failed");
    }
} catch (Exception $e) {
    error_log("Error marking notifications read: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to update notifications']);
}
?>