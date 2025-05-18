<?php
// Start session and check login
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

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
    <link href="./styles.css" rel="stylesheet"> <!-- Custom Styles -->
    
</head> 

<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <?php include("sidebar.php"); ?>

    <div class="main-content">


        <!-- Dynamic Content Area -->
        <div class="dashboard-content" id="main-content">
            <p>Loading content...</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    var defaultPage = "./Tabs/schedule.php"; // Default page

    function toggleSendButton() {
        $("#send-button").prop("disabled", $("#message-input").val().trim() === "");
    }
    // Load last visited page or default
    var lastPage = localStorage.getItem("lastVisitedPage") || defaultPage;
    $("#main-content").load(lastPage);


    // Sidebar click event
    $(".menu-link").click(function (e) {
        e.preventDefault();
        var page = $(this).data("page");

        if (page) {
            $("#main-content").html("<p>Loading...</p>"); // Show loading state
            $("#main-content").load(page);
            localStorage.setItem("lastVisitedPage", page);

            // Highlight active menu item
            $(".menu-link").removeClass("active");
            $(this).addClass("active");
        }
    });
});


</script>


</body>
</html>
