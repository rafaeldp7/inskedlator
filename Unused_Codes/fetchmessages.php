<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Admin/messageModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = 1; // Fixed Admin ID

// Check if user is logged in
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if (isset($_POST['last_msg_id'])) {
    $last_msg_id = (int) $_POST['last_msg_id'];

    // Fetch messages from the database where the message ID is greater than the last fetched message ID
    $stmt = $conn->prepare("SELECT id, sender_id, sender_type, message, sent_at 
                            FROM chat 
                            WHERE id > ? 
                            AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                            ORDER BY id ASC");
    $stmt->bind_param("iiiii", $last_msg_id, $user_id, $admin_id, $admin_id, $user_id);
    $stmt->execute();

    // Fetch the results as an associative array
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);

    // Return messages as JSON
    echo json_encode($messages);
} else {
    echo json_encode(["status" => "error", "message" => "Missing last_msg_id parameter"]);
}
?>
