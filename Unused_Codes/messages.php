<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json'); // Force JSON response

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$admin_id = 1; // Assuming admin ID is always 1
$last_msg_id = isset($_GET['last_msg_id']) ? (int) $_GET['last_msg_id'] : 0;

// Check database connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "SELECT id, sender_id, receiver_id, message, timestamp 
        FROM messages 
        WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        AND id > ?
        ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL error: " . $conn->error]);
    exit();
}

$stmt->bind_param("iiiii", $user_id, $admin_id, $admin_id, $user_id, $last_msg_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => $row["id"],
        "sender_id" => $row["sender_id"],
        "receiver_id" => $row["receiver_id"],
        "message" => $row["message"],
        "timestamp" => $row["timestamp"]
    ];
}

echo json_encode($messages);
?>
