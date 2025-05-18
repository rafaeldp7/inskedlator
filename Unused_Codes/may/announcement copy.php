<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Flash Message Functions
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_announcement'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        // Validation
        if (empty($title) || empty($content)) {
            set_flash_message("Title and Content cannot be empty.", "danger");
        } elseif (strlen($title) > 100) {
            set_flash_message("Title cannot exceed 100 characters.", "danger");
        } elseif (strlen($content) > 500) {
            set_flash_message("Content cannot exceed 500 characters.", "danger");
        } else {
            // Insert Announcement
            $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);

            if ($stmt->execute()) {
                set_flash_message("Announcement added successfully!", "success");
            } else {
                set_flash_message("Error adding announcement: " . $stmt->error, "danger");
            }
            $stmt->close();

            header('Location: ../admin.php');
            exit();
        }
    }

    if (isset($_POST['delete_announcement'])) {
        $id = intval($_POST['id']);

        // Start Transaction
        $conn->begin_transaction();

        // Fetch Announcement
        $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $announcement = $result->fetch_assoc();
        $stmt->close();

        if ($announcement) {
            // Archive Record in deleted_data
            $deletedRecord = json_encode($announcement);
            $stmt = $conn->prepare("INSERT INTO deleted_data (table_name, deleted_record) VALUES (?, ?)");
            $tableName = "announcements";
            $stmt->bind_param("ss", $tableName, $deletedRecord);

            if (!$stmt->execute()) {
                $conn->rollback();
                set_flash_message("Error archiving announcement: " . $stmt->error, "danger");
                header("Location: ./Tabs.php");
                exit();
            }
            $stmt->close();

            // Delete Announcement
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                $conn->rollback();
                set_flash_message("Error deleting announcement: " . $stmt->error, "danger");
            } else {
                $conn->commit();
                set_flash_message("Announcement deleted successfully!", "success");
            }
            $stmt->close();
        } else {
            $conn->rollback();
            set_flash_message("Announcement not found.", "danger");
        }

        header("Location: ../admin.php");
        exit();
    }
}

// Fetch Announcements
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $result->fetch_all(MYSQLI_ASSOC);

// Get Flash Message
$flash_message = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let flashMessage = document.getElementById("flash-message");
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.display = "none";
                }, 5000); // Hide after 5 seconds
            }
        });
    </script>
</head>
<body>


<div class="d-flex" id="main-wrapper">

    <div class="container mt-4">
        <h2>Manage Announcements</h2>

        <!-- Display Flash Message -->
        <?php if ($flash_message): ?>
            <div id="flash-message" class="alert alert-<?= htmlspecialchars($flash_message['type']) ?>" role="alert">
                <?= htmlspecialchars($flash_message['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Add Announcement Form -->
        <form action="./Tabs/announcement.php" method="POST" class="mb-3">
            <div class="mb-2">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" required maxlength="100">
            </div>
            <div class="mb-2">
                <label for="content" class="form-label">Content</label>
                <textarea name="content" id="content" class="form-control" rows="3" required maxlength="500"></textarea>
            </div>
            <button type="submit" name="add_announcement" class="btn btn-primary">Add Announcement</button>
        </form>

        <!-- Existing Announcements -->
        <h3>Existing Announcements</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?= htmlspecialchars($announcement['title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($announcement['content'])) ?></td>
                        <td><?= date('F j, Y, g:i a', strtotime($announcement['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="./Tabs/announcement.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $announcement['id'] ?>">
                                <button type="submit" name="delete_announcement" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
