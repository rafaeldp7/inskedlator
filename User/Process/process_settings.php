<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$user_id          = $_POST['user_id']            ?? null;
$userID           = trim($_POST['userID']        ?? '');
$firstname        = trim($_POST['firstname']     ?? '');
$middlename       = trim($_POST['middlename']    ?? '');
$lastname         = trim($_POST['lastname']      ?? '');
$current_password = $_POST['current_password']   ?? '';
$new_password     = $_POST['new_password']       ?? '';
$confirm_password = $_POST['confirm_password']   ?? '';

$response = ['message' => []];

try {
    $stmt = $conn->prepare(
        'SELECT userID, firstname, middlename, lastname, password
         FROM users
         WHERE id = ?'
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result(
        $db_userID,
        $db_firstname,
        $db_middlename,
        $db_lastname,
        $db_hashed_password
    );
    $stmt->fetch();
    $stmt->close();

    // Check for duplicate userID
    if ($userID !== $db_userID) {
        $checkStmt = $conn->prepare(
            'SELECT id FROM users WHERE userID = ? AND id != ?'
        );
        $checkStmt->bind_param('si', $userID, $user_id);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            echo json_encode(['error' => 'User ID already exists.']);
            exit;
        }
        $checkStmt->close();
    }

    // Update profile info
    if (
        $userID     !== $db_userID    ||
        $firstname  !== $db_firstname ||
        $middlename !== $db_middlename||
        $lastname   !== $db_lastname
    ) {
        $updateStmt = $conn->prepare(
            'UPDATE users
             SET userID = ?, firstname = ?, middlename = ?, lastname = ?
             WHERE id = ?'
        );
        $updateStmt->bind_param(
            'ssssi',
            $userID,
            $firstname,
            $middlename,
            $lastname,
            $user_id
        );
        $updateStmt->execute();
        $updateStmt->close();

        $_SESSION['userID']     = $userID;
        $_SESSION['firstname']  = $firstname;
        $_SESSION['middlename'] = $middlename;
        $_SESSION['lastname']   = $lastname;

        $response['message'][] = 'Profile info updated.';
    }

    // Change password if provided
    if ($current_password || $new_password || $confirm_password) {
        if (!$current_password || !$new_password || !$confirm_password) {
            echo json_encode(['error' => 'All password fields are required to change password.']);
            exit;
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['error' => 'New password and confirm password do not match.']);
            exit;
        }

        if (!password_verify($current_password, $db_hashed_password)) {
            echo json_encode(['error' => 'Incorrect current password.']);
            exit;
        }

        $new_hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $pwStmt = $conn->prepare(
            'UPDATE users SET password = ? WHERE id = ?'
        );
        $pwStmt->bind_param('si', $new_hashed, $user_id);
        $pwStmt->execute();
        $pwStmt->close();

        $response['message'][] = 'Password changed successfully.';
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_exts)) {
            $response['message'][] = '❌ Invalid file type for profile picture.';
        } elseif ($_FILES['profile_picture']['size'] > 10 * 1024 * 1024) {
            $response['message'][] = '❌ Profile picture must be under 10MB.';
        } else {
            $upload_dir = '../../Assets/Profile/';
            $new_filename = $db_userID . '.' . $ext;
            $destination = $upload_dir . $new_filename;

            // Remove old pictures with different extensions
            foreach ($allowed_exts as $old_ext) {
                $old_file = $upload_dir . $db_userID . '.' . $old_ext;
                if (file_exists($old_file) && $old_file !== $destination) {
                    unlink($old_file);
                }
            }

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $relative_path = 'Assets/Profile/' . $new_filename;

                // Save or update path in database
                $stmt = $conn->prepare("
                    INSERT INTO profile_pictures (user_id, file_path)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE file_path = VALUES(file_path)
                ");
                $stmt->bind_param("is", $user_id, $relative_path);
                $stmt->execute();
                $stmt->close();

                $response['message'][] = 'Profile picture updated.';
            } else {
                $response['message'][] = '❌ Failed to upload profile picture.';
            }
        }
    }

    if (empty($response['message'])) {
        $response['message'][] = 'No changes were made.';
    }

    // Send updated user data
    $response['userID']     = $userID;
    $response['firstname']  = $firstname;
    $response['middlename'] = $middlename;
    $response['lastname']   = $lastname;

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error updating profile: ' . $e->getMessage()
    ]);
    exit;
}
