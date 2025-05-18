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

            echo "<script>window.location.href = './main.php?page=announcement';</script>";
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
                echo "<script>window.location.href = './main.php?page=announcement';</script>";
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

        echo "<script>window.location.href = './main.php?page=announcement';</script>";
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
    <div class="container announcement-container">
        <h2>Manage Announcements</h2>

        <!-- Flash message -->
        <?php if ($flash_message): ?>
            <div id="flash-message" class="alert alert-<?= htmlspecialchars($flash_message['type']) ?>">
                <?= htmlspecialchars($flash_message['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Add Announcement Form -->
        <form method="POST">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea name="content" id="content" class="form-control" maxlength="500" required></textarea>
            </div>
            <button type="submit" name="add_announcement" class="btn btn-primary">Add Announcement</button>
        </form>

        <!-- List Announcements -->
        <div class="announcement-list">
            <?php foreach ($announcements as $ann): ?>
                <div class="announcement-item">
                    <div class="announcement-title"><?= htmlspecialchars($ann['title']) ?></div>
                    <div class="announcement-content"><?= nl2br(htmlspecialchars($ann['content'])) ?></div>
                    <div class="announcement-date"><?= date('F j, Y \a\t g:i A', strtotime($ann['created_at'])) ?></div>
                    <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                        <input type="hidden" name="id" value="<?= $ann['id'] ?>">
                        <button type="submit" name="delete_announcement" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>

<style>
body {
    background-color: #f5f5f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container.announcement-container {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

h2 {
    margin-bottom: 25px;
    font-weight: bold;
}

.form-group {
    margin-bottom: 20px;
}

textarea.form-control {
    resize: none;
    height: 120px;
    font-size: 14px;
    border-radius: 10px;
}

.btn-primary {
    border-radius: 25px;
    padding: 10px 20px;
    font-size: 14px;
}

.btn-danger {
    border-radius: 25px;
    padding: 8px 14px;
    font-size: 13px;
}

.announcement-list {
    margin-top: 30px;
}

.announcement-item {
    background-color: #f8f9fa;
    padding: 15px 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    position: relative;
}

.announcement-title {
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 16px;
}

.announcement-content {
    font-size: 14px;
}

.announcement-date {
    font-size: 12px;
    color: #888;
    margin-top: 10px;
}

.delete-form {
    position: absolute;
    top: 15px;
    right: 20px;
}
</style>
