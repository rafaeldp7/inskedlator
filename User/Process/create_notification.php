<?php
require_once '../../config.php';

function createNotification($userId, $title, $message) {
  global $conn;
  $stmt = $conn->prepare("
    INSERT INTO notifications (id, title, message)
    VALUES (?, ?, ?)
  ");
  $stmt->bind_param("iss", $userId, $title, $message);
  return $stmt->execute();
}

// Example usage (call this when admin takes action):
// createNotification($targetUserId, "Schedule Approved", "Your schedule for Monday was approved");
?>