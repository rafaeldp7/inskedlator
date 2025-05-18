<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php'; // Ensure correct path
require_once '../Models/scheduleModel.php'; // Ensure this file exists

header('Content-Type: application/json');

$response = [];

if ($conn) {
    $sql = "SELECT * FROM schedules WHERE status = 'Approved'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'id' => $row['id'],
                'subject' => $row['subject'],
                'section' => $row['section'],
                'day' => $row['day'],
                'time' => $row['time'],
                'status' => $row['status']
            ];
        }
    }
} else {
    $response = ['error' => 'Database connection failed.'];
}

echo json_encode($response);
?>
