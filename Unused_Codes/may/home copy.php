<?php
// Start session and check login
require_once '../config.php';
require './Models/ScheduleModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$loggedInUser = $_SESSION['full_name'] ?? header("Location: ../index.php");
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>

  <!-- Stylesheets -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
  }
  header {
    padding:3rem;
    background-color: #198754;
    border-bottom: 2px solid #000;
  }
  header h1 {
    font-weight: 600;
    letter-spacing: 1px;
  }
  .dropdown-toggle {
    font-weight: 500;
  }

  #sidebar {
    background-color: #198754;
    color: white;
    min-height: 93vh;
    border-right: 2px solid #000;
  }

  #sidebar .nav-link {
    color: white;
    font-weight: 500;
    transition: background 0.2s ease-in-out;
  }

  #sidebar .nav-link:hover,
  #sidebar .nav-link.active {
    background-color: #145c3a;
    border-radius: 8px;
    color: #fff !important;
  }

  #sidebar .user-info h3 {
    font-size: 1.1rem;
    font-weight: 600;
  }

  #main-content,
  #announcements {
    border-left: 2px solid #000;
    border-right: 2px solid #000;
    background-color: #fff;
  }

  #main-content {
  padding: 1rem;
  min-height: 93vh;
}
#announcements {
  padding: 1rem;
  min-height: 93vh;
}
  .dropdown-menu li a:hover {
    background-color: #f8f9fa;
  }

  .menu-link i {
    margin-right: 8px;
  }

  .width-100 {
    width: 100% !important;
  }

  .btn-outline-light:hover {
    background-color: white;
    color: #198754;
  }
</style>
</head>
<body>

<!-- HEADER -->
<header class="bg-success text-white py-2 border-bottom-2 border-dark">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="me-3 text-center">
          <h1 class="m-0 fs-3 text-underline">INSKEDLATOR</h1>
          <p class="mb-0">PERSONNEL SCHEDULING GENERATOR</p>
        </div>
        <button class="btn btn-outline-light" id="sidebarToggle" aria-expanded="true">
          <i class="fa fa-bars"></i>
        </button>
      </div>
      <div class="dropdown">
        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
          <?= htmlspecialchars($_SESSION['firstname'] ?? 'Username') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark px-2" data-page="./Tabs/profile.php"><i class="fa fa-user"></i> Profile</a></li>
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark px-2" data-page="./Tabs/settings.php"><i class="fa fa-cogs"></i> Settings</a></li>

          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="./Process/logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</header>

<!-- MAIN LAYOUT -->
<div class="container-fluid">
  <div class="row">

    <!-- SIDEBAR -->
    <nav id="sidebar" class="col-md-3 col-lg-2 bg-success p-3  border border-2 border-dark">
      <div class="user-info text-center mb-4">
        <img src="../Assets/profile_icon.png" alt="Profile Picture" class="img-fluid rounded-circle mb-2" width="100">
        <h3 class="fs-5"><?= htmlspecialchars($loggedInUser) ?></h3>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/schedule.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/view_schedule_status.php"><i class="fa fa-list-alt"></i> View Schedule Status</a></li>
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/add_schedule.php"><i class="fa fa-plus"></i> Add Schedule</a></li>
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/chat.php"><i class="fa fa-message"></i> Chat</a></li>
        <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/calendar.php"><i class="fa fa-calendar"></i> Calendar</a></li>
        <!-- <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/settings.php"><i class="fa fa-user"></i> Profile</a></li> -->
        <!-- <li class="nav-item mt-5">
          <a href="#" id="logout-link" class="nav-link text-white bg-danger fw-bold text-center rounded py-2">
            <i class="fa fa-sign-out-alt me-2"></i> Logout
          </a>
        </li> -->
      </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <main id="main-content" class="col-md-6 col-lg-8 p-4 border border-2 border-dark" >
      <p>Loading content...</p>
    </main>

    <!-- ANNOUNCEMENTS -->
    <aside id="announcements" class="col-md-3 col-lg-2 bg-light p-3 border border-2 border-dark">
      <h5>📢 Announcements</h5>
      <p>Important updates go here.</p>
    </aside>

  </div>
</div>

<!-- INTERACTION SCRIPTS -->
<script>
$(function () {
  const $sidebar = $("#sidebar");
  const $main = $("#main-content");
  const $announcements = $("#announcements");
  const $toggleBtn = $("#sidebarToggle");
  const $toggleIcon = $toggleBtn.find("i");

  const defaultPage = "./Tabs/schedule.php";
  const announcementPage = "./Tabs/announcement.php";
  const lastPage = localStorage.getItem("lastVisitedPage") || defaultPage;

  // Load pages
  $main.load(lastPage);
  $announcements.load(announcementPage);

  $(".menu-link").each(function () {
    if ($(this).data("page") === lastPage) {
      $(this).addClass("active bg-dark text-white");
    }
  });

  // Sidebar toggle
  let collapsed = localStorage.getItem("sidebarCollapsed") === "true";

  function applySidebarState() {
    if (collapsed) {
      $sidebar.addClass("d-none");
      $announcements.addClass("d-none");
      $main.removeClass("col-md-6 col-lg-8").addClass("width-100");
      $toggleIcon.removeClass("fa-bars").addClass("fa-xmark");
      $toggleBtn.attr("aria-expanded", "false");
    } else {
      $sidebar.removeClass("d-none");
      $announcements.removeClass("d-none");
      $main.removeClass("width-100").addClass("col-md-6 col-lg-8");
      $toggleIcon.removeClass("fa-xmark").addClass("fa-bars");
      $toggleBtn.attr("aria-expanded", "true");
    }
  }

  applySidebarState();

  $toggleBtn.click(function () {
    collapsed = !collapsed;
    localStorage.setItem("sidebarCollapsed", collapsed);
    applySidebarState();
  });

  // Menu click
  $(".menu-link").click(function (e) {
    e.preventDefault();
    const page = $(this).data("page");
    if (!page) return;

    $main.html("<p>Loading...</p>").load(page);
    localStorage.setItem("lastVisitedPage", page);

    $(".menu-link").removeClass("active bg-dark text-white");
    $(this).addClass("active bg-dark text-white");
  });

  // Logout
  $("#logout-link").click(function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to log out?")) {
      localStorage.removeItem("lastVisitedPage");
      window.location.href = "./Process/logout.php";
    }
  });
});
</script>

<!-- SETTINGS UPDATE HANDLER -->
<script>
$(function() {
  $('#updateProfileForm').on('submit', function(e) {
    e.preventDefault();

    var newPass = $('#new_password').val();
    var confirmPass = $('#confirm_password').val();
    if ((newPass || confirmPass) && newPass !== confirmPass) {
      $('#responseMessage').html(`<div class="alert alert-danger">❌ New passwords do not match.</div>`);
      return;
    }

    $.ajax({
      url: './Process/process_settings.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(response) {
        if (response.error) {
          $('#responseMessage').html(`<div class="alert alert-danger">❌ ${response.error}</div>`);
          return;
        }

        var msgs = response.message.join('<br>');
        $('#responseMessage').html(`<div class="alert alert-success">✅ ${msgs}</div>`);

        $('#firstname').val(response.firstname);
        $('#middlename').val(response.middlename);
        $('#lastname').val(response.lastname);
        $('#userID').val(response.userID);

        setTimeout(function() {
          $('#current_password, #new_password, #confirm_password').val('');
          window.location.href = 'home.php';
        }, 1500);
      },
      error: function() {
        $('#responseMessage').html(`<div class="alert alert-danger">❌ Error updating profile.</div>`);
      }
    });
  });
});
</script>

</body>
</html>
