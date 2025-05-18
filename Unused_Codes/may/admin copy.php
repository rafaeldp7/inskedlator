<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../User/Models/ScheduleModel.php';
require_once '../User/Models/UserModel.php';
require_once 'AdminModel.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$user = new User($conn);
$model = new ScheduleModel($conn);

$sort_by = $_GET['sort_by'] ?? 'subject';
$order = $_GET['order'] ?? 'asc';
$status = $_GET['status'] ?? 'all';

$schedules = $model->getSchedules($sort_by, $order, $status) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = (int) $_POST['schedule_id'];

    if (isset($_POST['approve'])) {
        $model->updateScheduleStatus($schedule_id, 'Approved');
    } elseif (isset($_POST['reject'])) {
        $model->updateScheduleStatus($schedule_id, 'Rejected');
    } elseif (isset($_POST['delete'])) {
        $model->deleteSchedule($schedule_id);
    }

    $query = http_build_query([
        'sort_by' => $sort_by,
        'order' => $order,
        'status' => $status
    ]);
    header("Location: admin.php?$query");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Inskedlator</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- HEADER -->
    <header class="bg-success text-white py-2 border-bottom border-dark">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-3 text-center">
                    <h1 class="m-0 fs-3">INSKEDLATOR ADMIN</h1>
                    <p class="mb-0">PERSONNEL SCHEDULING GENERATOR</p>
                </div>
                <button class="btn btn-outline-light ms-3" id="sidebarToggle" aria-expanded="true">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <?= htmlspecialchars($admin_username) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a href="#" class="dropdown-item menu-link text-dark" data-page="./Tabs/profile.php">
                        <i class="fa fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a id="logout-link" class="dropdown-item text-danger" href="#">
                        <i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- MAIN WRAPPER -->
    <div class="d-flex" id="main-wrapper">
        <!-- SIDEBAR -->
        <nav id="sidebar" class="bg-success p-3 border border-2 border-dark">
            <div class="user-info text-center mb-4">
                <img src="../Assets/profile_icon.png" alt="Profile Picture" class="img-fluid rounded-circle mb-2" width="100">
                <h3 class="fs-5 text-white">Hello, <?= htmlspecialchars($admin_name) ?></h3>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link text-white menu-link" data-page="./Tabs/schedule.php">Schedules</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white menu-link" data-page="./Tabs/announcement.php">Announcement</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white menu-link" data-page="./Tabs/chat.php">Chat</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white menu-link" data-page="./Tabs/calendar.php">Calendar</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white menu-link" data-page="./Tabs/addUser.php">Add User</a>
                </li>
            </ul>
        </nav>

        <!-- MAIN CONTENT -->
        <div id="right" class="flex-grow-1 p-4">
            <div class="header mb-3">
                <select id="filter" class="form-select w-auto">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div id="main-content">
                <p>Loading...</p>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        $(document).ready(function () {
            const $sidebar = $("#sidebar");
            const $toggleBtn = $("#sidebarToggle");
            const $toggleIcon = $toggleBtn.find("i");
            const $filter = $("#filter");
            const $menuLinks = $(".menu-link");

            let collapsed = localStorage.getItem("sidebarCollapsed") === "true";

            function applySidebarState() {
                if (collapsed) {
                    $("body").addClass("sidebar-collapsed");
                    $toggleIcon.removeClass("fa-bars").addClass("fa-xmark");
                    $toggleBtn.attr("aria-expanded", "false");
                } else {
                    $("body").removeClass("sidebar-collapsed");
                    $toggleIcon.removeClass("fa-xmark").addClass("fa-bars");
                    $toggleBtn.attr("aria-expanded", "true");
                }
            }

            $toggleBtn.on("click", function () {
                collapsed = !collapsed;
                localStorage.setItem("sidebarCollapsed", collapsed);
                applySidebarState();
            });

            function loadPage(page, updateURL = true) {
                $("#main-content").html("<p>Loading...</p>").load(page, function () {
                    if (page.includes("schedule.php")) {
                        $filter.show();
                        applyFilter();
                    } else {
                        $filter.hide();
                    }
                });

                localStorage.setItem("lastVisitedPage", page);
                $menuLinks.removeClass("active");
                $menuLinks.filter(`[data-page='${page}']`).addClass("active");

                if (updateURL) {
                    const newUrl = page.includes("schedule.php")
                        ? `admin.php?status=${$filter.val()}&sort_by=<?= htmlspecialchars($sort_by) ?>&order=<?= htmlspecialchars($order) ?>`
                        : "admin.php";
                    history.pushState({}, "", newUrl);
                }
            }

            function applyFilter() {
                const selected = $filter.val();
                $("#main-content table tbody tr").each(function () {
                    const rowStatus = $(this).data("status");
                    $(this).toggle(selected === "all" || rowStatus === selected);
                });
            }

            $menuLinks.on("click", function (e) {
                e.preventDefault();
                const page = $(this).data("page");
                loadPage(page);
            });

            $filter.on("change", function () {
                loadPage("./Tabs/schedule.php");
            });

            $("#logout-link").on("click", function (e) {
                e.preventDefault();
                if (confirm("Are you sure you want to log out?")) {
                    window.location.href = "logout.php";
                }
            });

            applySidebarState();
            const lastPage = localStorage.getItem("lastVisitedPage") || "./Tabs/schedule.php";
            loadPage(lastPage, false);
        });
    </script>

</body>
</html>



