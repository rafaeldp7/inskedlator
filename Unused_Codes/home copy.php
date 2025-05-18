<?php
// Start session and check login
require_once '../config.php';
require './Models/scheduleModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['full_name'] ?? header("Location: login.php");
$loggedInUserID = $_SESSION['user_id'] ?? null;
$model = new ScheduleModel($conn);

$schedules = [];
try {
    $schedules = $model->getSchedulesUser($loggedInUserID);
} catch (Exception $e) {
    die("Error fetching schedules: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head> 

<body>

<header class="bg-success text-white text-center py-3 border borderbottom-2 border-dark">
    <h1>INSKEDLATOR</h1>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 bg-success p-3 min-vh-100 border border-2 border-dark">
            <div class="user-info text-center">
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $loggedInUser = $_SESSION['full_name'] ?? 'Guest';
                $defaultProfile = "../Assets/profile_icon.png";
                $profilePath = $defaultProfile;
                ?>
                <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-2 " width="100">
                <h3 class="fs-5"> <?= htmlspecialchars($loggedInUser) ?> </h3>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/schedule.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/view_schedule_status.php"><i class="fa fa-list-alt"></i> View Schedule Status</a></li>
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/add_schedule.php"><i class="fa fa-plus"></i> Add Schedule</a></li>
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/chat.php"><i class="fa fa-message"></i> Chat</a></li>
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/calendar.php"><i class="fa fa-calendar"></i> Calendar</a></li>
                <!-- <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/announcement.php"><i class="fa fa-bullhorn"></i> Announcement</a></li> -->
                <li class="nav-item"><a href="#" class="nav-link menu-link  text-dark" data-page="./Tabs/settings.php"><i class="fa fa-user"></i> Profile</a></li>
                <li class="nav-item mt-5"><a href="#" id="logout-link" class="nav-link text-white bg-danger fw-bold text-center rounded py-2"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-6 col-lg-8 p-4 border border-2 border-dark" id="main-content">
            <p>Loading content...</p>
        </main>

        <!-- Right Panel (Announcements) -->
        <aside class="col-md-3 col-lg-2 bg-light p-3 border border-2 border-dark " id="announcement-content">
            <h5>📢 Announcements</h5>
            <p>Important updates go here.</p>
        </aside>
    </div>
</div>

<script>
$(document).ready(function () {
    var announcementPage = "./Tabs/announcement.php";
    var defaultPage = "./Tabs/schedule.php";
    function toggleSendButton() {
        $("#send-button").prop("disabled", $("#message-input").val().trim() === "");
    }

    var lastPage = localStorage.getItem("lastVisitedPage") || defaultPage;
    $("#main-content").load(lastPage);
    $('#main-content').load('Tabs/calendar.php');

    $("#announcement-content").load(announcementPage);

    // Highlight the last active tab
    $(".menu-link").each(function () {
        if ($(this).data("page") === lastPage) {
            $(this).addClass("active bg-dark text-white");
        }
    });

    $(".menu-link").click(function (e) {
        e.preventDefault();
        var page = $(this).data("page");

        if (page) {
            $("#main-content").html("<p>Loading...</p>");
            $("#main-content").load(page);
            localStorage.setItem("lastVisitedPage", page);

            // Remove 'active' class from all links and highlight the current one
            $(".menu-link").removeClass("active bg-dark text-white");
            $(this).addClass("active bg-dark text-white");
        }
    });

    $("#logout-link").click(function (e) {
        e.preventDefault();
        if (confirm("Are you sure you want to log out?")) {
            localStorage.removeItem("lastVisitedPage");
            window.location.href = "./Process/logout.php";
        }
    });
});

</script>

</body>
</html>
<style>
    #logout-link {
    transition: background 0.3s ease, transform 0.2s ease;
    }
    #logout-link:hover {
        background: #c82333 !important;
        transform: scale(1.05);
    }
</style>
