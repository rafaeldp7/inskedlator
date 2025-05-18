<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../config.php';
require_once '../User/Models/ScheduleModel.php';
require_once '../User/Models/UserModel.php';
require_once 'AdminModel.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$user = new User($conn);
$model = new ScheduleModel($conn);

$sort_by = $_GET['sort_by'] ?? 'subject';
$order = $_GET['order'] ?? 'asc';
$status = $_GET['status'] ?? 'all';

$schedules = $model->getSchedules($sort_by, $order, $status);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = intval($_POST['schedule_id']);

    if (isset($_POST['approve'])) {
        $model->updateScheduleStatus($schedule_id, 'Approved');
    } elseif (isset($_POST['reject'])) {
        $model->updateScheduleStatus($schedule_id, 'Rejected');
    } elseif (isset($_POST['delete'])) {
        $model->deleteSchedule($schedule_id);
    }

    header("Location: admin.php?sort_by=$sort_by&order=$order&status=$status");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header class="bg-success text-white py-3 px-4 border-bottom border-dark" style="position: fixed; top: 0; width: 100%; z-index: 1050;">
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 fs-3">INSKEDLATOR ADMIN</h1>
    <!-- <a href="logout.php" class="btn btn-outline-light fw-bold">Logout</a> -->
  </div>
</header>
    <div class="left">
        <div class="profile"><p>Hello, Admin!</p></div>
        <div class="tabs">
            <div class="tab menu-link" data-page="./Tabs/schedule.php">Schedules</div>
            <div class="tab menu-link" data-page="./Tabs/calendar.php">Calendar</div>
            <div class="tab menu-link" data-page="./Tabs/announcement.php">Announcement</div>
            <div class="tab menu-link" data-page="./Tabs/chat.php">Chat</div>
            <div class="tab menu-link" data-page="./Tabs/profile.php">Profile</div>
        </div>
        <div class="logout">
            <a href="logout.php" id="logout-link">LOGOUT</a>
        </div>
    </div>

    <div class="right">
        <div class="header">
            <h1>Admin</h1>
            <select id="filter" class="form-select"
                onchange="window.location.href='admin.php?status=' + this.value + '&sort_by=<?= htmlspecialchars($sort_by) ?>&order=<?= htmlspecialchars($order) ?>'">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div id="main-content"><p>Loading...</p></div>
    </div>

    <script>
  $(document).ready(function () {
    let lastPage = localStorage.getItem("lastVisitedPage") || "./Tabs/schedule.php";

    function loadPage(page, updateURL = true) {

      $("#main-content")
        .html("<p>Loading...</p>")
        .load(page, () => {
          if (page.includes("schedule.php")) {
            applyFilter();
            $("#filter").show();
          } else {
            $("#filter").hide();
          }
        });

      localStorage.setItem("lastVisitedPage", page);


      $(".menu-link").removeClass("active");
      $(`.menu-link[data-page='${page}']`).addClass("active");

      if (updateURL) {
        let newUrl = page.includes("schedule.php")
          ? `admin.php?status=${$("#filter").val()}&sort_by=<?= htmlspecialchars($sort_by) ?>&order=<?= htmlspecialchars($order) ?>`
          : "admin.php";
        window.history.pushState({}, "", newUrl);
      }
    }

    function applyFilter() {
      const selected = $("#filter").val();
      $("#main-content table tbody tr").each(function () {
        const rowStatus = $(this).data("status");
        $(this).toggle(selected === "all" || rowStatus === selected);
      });
    }

    
    $(".menu-link").click(function () {
      loadPage($(this).data("page"));
    });

    $("#filter").change(() => {
      loadPage("./Tabs/schedule.php");
    });

    $("#logout-link").click(function (e) {
      e.preventDefault();
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "logout.php";
      }
    });

    loadPage(lastPage, false);
  });
</script>

</body>
</html>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    display: flex;
    height: 100vh;
    overflow: hidden;
    background-color: #f4f6f9;
}

#filter {
    width: 30%;
    border-radius: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    padding: 8px;
}

.left {
    background-color: #2C3E50;
    width: 15%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    color: white;
    position: absolute;
    top: 0;
    left: 0;
    height: 100vh;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.profile {
    width: 100%;
    text-align: center;
    font-weight: bold;
    background-color: #34495E;
    border-radius: 8px;
    margin-bottom: 15px;
}

.tab {
    padding: 15px;
    margin: 8px 0;
    background-color: #34495E;
    text-align: center;
    cursor: pointer;
    border-radius: 8px;
    font-weight: bold;
    width: 100%;
    transition: all 0.3s ease-in-out;
}

.tab:hover, .tab.active {
    background-color: #1ABC9C;
    transform: scale(1.05);
}

.logout {
    margin-top: auto;
    width: 100%;
    text-align: center;
    padding: 15px;
}

#logout-link {
    background: white;
    color: #E74C3C;
    padding: 5%;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    border-radius: 8px;
    transition: all 0.3s ease-in-out;
}

#logout-link:hover {
    background: #C0392B;
    color: white;
    transform: scale(1.05);
}

.right {
    margin-left: 15%;
    margin-top: 70px;
    width: 85%;
    height: calc(100vh - 70px);
    overflow-y: auto;
    padding: 25px;
    background: white;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 2px solid #ddd;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

th, td {
    border: 1px solid #ddd;
    padding: 14px;
    text-align: center;
    font-size: 16px;
}

th {
    background-color: #1ABC9C;
    color: white;
    text-transform: uppercase;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #D4EFDF;
    transition: 0.3s ease-in-out;
}

.approve-btn,
.reject-btn,
.delete-btn {
    padding: 10px 15px;
    font-size: 14px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.approve-btn {
    background: #28A745;
    color: white;
}

.approve-btn:hover {
    background: #218838;
    transform: scale(1.05);
}

.reject-btn {
    background: #E67E22;
    color: white;
}

.reject-btn:hover {
    background: #D35400;
    transform: scale(1.05);
}

.delete-btn {
    background: #E74C3C;
    color: white;
}

.delete-btn:hover {
    background: #C0392B;
    transform: scale(1.05);
}
</style>
