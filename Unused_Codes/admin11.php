<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../User/Models/ScheduleModel.php';
require_once '../User/Models/UserModel.php';
require_once 'AdminModel.php';

// **🔹 HANDLE POST REQUESTS (APPROVE, REJECT, DELETE) 🔹**
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['schedule_id'], $_POST['action'])) {
        echo json_encode(["success" => false, "error" => "Missing parameters"]);
        exit();
    }

    $schedule_id = intval($_POST['schedule_id']);
    $action = $_POST['action'];

    if (!$conn) {
        echo json_encode(["success" => false, "error" => "Database connection failed"]);
        exit();
    }

    switch ($action) {
        case 'approve':
            $query = "UPDATE schedules SET status = 'Approved' WHERE id = ?";
            break;
        case 'reject':
            $query = "UPDATE schedules SET status = 'Rejected' WHERE id = ?";
            break;
        case 'delete':
            $query = "DELETE FROM schedules WHERE id = ?";
            break;
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
            exit();
    }

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $schedule_id);
        $execute = $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => $execute]);
    } else {
        echo json_encode(["success" => false, "error" => "Query preparation failed"]);
    }

    $conn->close();
    exit();
}

// **🔹 ENSURE ADMIN IS LOGGED IN 🔹**
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$user = new User($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<header class="bg-dark text-white text-center py-3 border-bottom">
    <h1>Admin Panel</h1>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 bg-dark p-3 min-vh-100 border-end">
            <div class="user-info text-center text-white">
                <h3 class="fs-5">Admin</h3>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link text-white menu-link" data-page="Tabs/schedule.php">Manage Schedules</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white menu-link" data-page="Tabs/calendar.php">Calendar</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white menu-link" data-page="Tabs/announcement.php">Announcements</a></li>
                <li class="nav-item"><a href="#" class="nav-link text-white menu-link" data-page="Tabs/chat.php">Chat</a></li>
                <li class="nav-item mt-5"><a href="#" id="logout-link" class="nav-link bg-danger text-white fw-bold text-center rounded py-2">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 col-lg-10 overflow-y-auto" id="main-content">
            <p>Loading content...</p>
        </main>
    </div>
</div>

<script>

$(document).ready(function () {
    let defaultPage = "Tabs/schedule.php"; 
    let lastPage = localStorage.getItem("lastVisitedPage") || defaultPage;

    function updateActiveLink(page) {
        $(".menu-link").removeClass("active bg-primary text-white");
        $(".menu-link").each(function () {
            if ($(this).data("page") === page) {
                $(this).addClass("active bg-primary text-white");
            }
        });
    }

    function loadPage(page) {
        $("#main-content").html("<p>Loading...</p>");
        $("#main-content").load(page, function(response, status, xhr) {
            if (status === "error") {
                $("#main-content").html("<p class='text-danger'>Error loading page.</p>");
                console.error("Error loading page:", xhr.statusText);
            } else {
                attachEventHandlers();  
            }
        });

        localStorage.setItem("lastVisitedPage", page);
        updateActiveLink(page);
    }

    loadPage(lastPage);

    $(".menu-link").click(function (e) {
        e.preventDefault();
        let page = $(this).data("page");
        if (page) {
            loadPage(page);
        }
    });

    $("#logout-link").click(function (e) {
        e.preventDefault();
        if (confirm("Are you sure you want to log out?")) {
            localStorage.removeItem("lastVisitedPage");
            window.location.href = "./logout.php";
        }
    });

    function attachEventHandlers() {
        // 🔹 Sorting
        $(document).off("click", ".sort").on("click", ".sort", function (event) {
            event.preventDefault();
            let sortBy = $(this).data("sort");
            let currentOrder = new URL(window.location.href).searchParams.get('order') || 'asc';
            let newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            loadPage("Tabs/schedule.php?sort_by=" + sortBy + "&order=" + newOrder);
        });

        // 🔹 Approve, Reject, Delete Actions
        $(document).off("click", ".action-btn").on("click", ".action-btn", function () {
            let action = $(this).data("action");
            let row = $(this).closest("tr");
            let scheduleId = row.data("id");

            if (action === "delete" && !confirm("Are you sure you want to delete this schedule?")) return;

            $.post("admin.php", {  
                schedule_id: scheduleId,
                action: action
            }, function (response) {
                try {
                    let data = JSON.parse(response);
                    if (data.success) {
                        if (action === "delete") {
                            row.remove();
                        } else {
                            row.find(".status").text(action === "approve" ? "Approved" : "Rejected"); 
                        }
                    } else {
                        alert("Error: " + data.error);
                    }
                } catch (e) {
                    console.error("Parsing Error:", response);
                    alert("Unexpected response from server.");
                }
            }).fail(function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Failed to connect to the server.");
            });
        });

        // 🔹 Filter Status
        $(document).off("change", "#status-filter").on("change", "#status-filter", function () {
            let newUrl = "Tabs/schedule.php?status=" + $(this).val();
            loadPage(newUrl);
        });

        // 🔹 Preserve Sorting on Filter Change
        $(document).off("click", "th a").on("click", "th a", function (event) {
            event.preventDefault();
            let newUrl = $(this).attr("href");
            loadPage("Tabs/schedule.php?" + newUrl.split("?")[1]);
        });
    }
});
</script>

</body>
</html>
