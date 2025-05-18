<?php
/**
 * Calendar Actions Handler
 * Handles CRUD operations via AJAX requests
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';
require_once 'Calendar.php'; // Include the Calendar class

// Create calendar instance
$calendar = new Calendar(); // Using default connection parameters from config

// Get action type
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process based on action
switch ($action) {
    case 'create':
        createEvent();
        break;
        
    case 'update':
        updateEvent();
        break;
        
    case 'delete':
        deleteEvent();
        break;
        
    default:
        http_response_code(400);
        echo "Invalid action";
        exit;
}

/**
 * Create a new event
 */
function createEvent() {
    global $calendar;
    
    // Validate required fields
    if (empty($_POST['title']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {
        http_response_code(400);
        echo "Missing required fields";
        exit;
    }
    
    // Format datetime if needed
    $start_date = formatDatetime($_POST['start_date']);
    $end_date = formatDatetime($_POST['end_date']);
    
    // Create event
    $eventId = $calendar->createEvent(
        $_POST['title'],
        $_POST['description'] ?? '',
        $start_date,
        $end_date,
        $_POST['location'] ?? ''
    );
    
    if ($eventId) {
        http_response_code(200);
        echo json_encode(['success' => true, 'id' => $eventId]);
    } else {
        http_response_code(500);
        echo "Failed to create event";
    }
}

/**
 * Update an existing event
 */
function updateEvent() {
    global $calendar;
    
    // Validate required fields
    if (empty($_POST['id']) || empty($_POST['title']) || empty($_POST['start_date']) || empty($_POST['end_date'])) {
        http_response_code(400);
        echo "Missing required fields";
        exit;
    }
    
    // Format datetime if needed
    $start_date = formatDatetime($_POST['start_date']);
    $end_date = formatDatetime($_POST['end_date']);
    
    // Prepare event data
    $eventData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? '',
        'start_date' => $start_date,
        'end_date' => $end_date,
        'location' => $_POST['location'] ?? ''
    ];
    
    // Update event
    $success = $calendar->updateEvent($_POST['id'], $eventData);
    
    if ($success) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo "Failed to update event";
    }
}

/**
 * Delete an event
 */
function deleteEvent() {
    global $calendar;
    
    // Validate required fields
    if (empty($_POST['id'])) {
        http_response_code(400);
        echo "Missing event ID";
        exit;
    }
    
    // Delete event
    $success = $calendar->deleteEvent($_POST['id']);
    
    if ($success) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo "Failed to delete event";
    }
}

/**
 * Format datetime string to MySQL format if needed
 * Handles both formats: "2025-05-01T14:30" and "2025-05-01 14:30:00"
 */
function formatDatetime($datetime) {
    if (strpos($datetime, 'T') !== false) {
        // Convert HTML datetime-local format to MySQL format
        return str_replace('T', ' ', $datetime) . ':00';
    }
    return $datetime;
}
?>