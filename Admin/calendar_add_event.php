<?php
// Tabs/calendar_add_event.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

require_once '../config.php';

// Sanitize input
$title = trim($_POST['title'] ?? '');
$desc  = trim($_POST['description'] ?? '');
$date  = trim($_POST['date'] ?? '');

// Basic validation
if (empty($title) || empty($date)) {
    http_response_code(400);
    exit('Missing title or date');
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    exit('Invalid date format');
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO events (title, description, date) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $desc, $date);

if ($stmt->execute()) {
    echo 'Event added';
} else {
    http_response_code(500);
    echo 'Database error';
}

$stmt->close();
$conn->close();
