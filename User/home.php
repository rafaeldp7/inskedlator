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
  $userID = $_SESSION['userID'] ?? null;
  $model = new ScheduleModel($conn);

  // Handle profile picture upload
  $message = '';
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
      $baseDir = dirname(dirname(dirname(__FILE__)));
      $profileDir = $baseDir . '/Assets/Profile/';
      
      // Create directory if doesn't exist
      if (!file_exists($profileDir)) {
          if (!mkdir($profileDir, 0777, true)) {
              $message = 'Failed to create directory';
          }
      }
      
      if (empty($message)) {
          $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
          
          if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
              $newFilename = $userID . '.' . $ext;
              $targetPath = $profileDir . $newFilename;
              $relativePath = 'Assets/Profile/' . $newFilename;
              
              array_map('unlink', glob($profileDir . $userID . '.*'));
              
              if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
                  $stmt = $conn->prepare("INSERT INTO profile_pictures (user_id, file_path) 
                                        VALUES (?, ?) 
                                        ON DUPLICATE KEY UPDATE file_path = ?, uploaded_at = CURRENT_TIMESTAMP");
                  $stmt->bind_param("iss", $loggedInUserID, $relativePath, $relativePath);
                  
                  if ($stmt->execute()) {
                      $message = 'Profile picture updated successfully!';
                      // Update profile picture in session if needed
                      $_SESSION['profile_pic'] = $relativePath;
                  } else {
                      $message = 'Database update failed: ' . $conn->error;
                  }
              } else {
                  $message = 'Upload failed - Error: ' . $_FILES['profile_pic']['error'];
              }
          } else {
              $message = 'Only JPG, JPEG, and PNG files are allowed';
          }
      }
  }

  // Get schedules
  $schedules = [];
  try {
      $schedules = $model->getSchedulesUser($loggedInUserID);
  } catch (Exception $e) {
      die("Error fetching schedules: " . htmlspecialchars($e->getMessage()));
  }

  // Get current profile picture
  $profile_pic_path = '../Assets/Profile/default.png';
  $stmt = $conn->prepare("SELECT file_path FROM profile_pictures WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
  $stmt->bind_param("i", $loggedInUserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $profile_pic_path = '../' . $row['file_path'];
  } else {
      // Fallback to file system check
      foreach (['jpg', 'jpeg', 'png'] as $ext) {
          if (file_exists('../Assets/Profile/' . $userID . '.' . $ext)) {
              $profile_pic_path = '../Assets/Profile/' . $userID . '.' . $ext;
              break;
          }
      }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
      body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
      }

      header {
        padding: 3rem;
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
        overflow-y: auto;
      }
      #main-content {
        padding: 1rem;
        min-height: 93vh;
        max-height: 93vh;
      }
      #announcements {
        padding: 0.5rem;
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
      .profile-img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      object-position: center;
  }

      .upload-form {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
      }.notification-item {
    transition: background 0.2s;
  }
  .notification-item:hover {
    background: #f8f9fa !important;
  }
  #notificationBadge {
    font-size: 0.6rem;
  }

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
        <!-- Inside header (right side) -->
  <div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle position-relative" type="button" id="userDropdown" data-bs-toggle="dropdown">
      <?= htmlspecialchars($_SESSION['firstname'] ?? 'Username') ?>
      <!-- Notification Badge -->
      <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
        0
      </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
      <!-- Notification Dropdown Item -->
      <li class="dropdown-item">
        <div class="d-flex justify-content-between align-items-center">
          <strong>Notifications</strong>
          <button id="markAllRead" class="btn btn-sm btn-link">Mark all read</button>
        </div>
        <div id="notificationList" class="mt-2" style="max-height: 300px; overflow-y: auto;">
          <!-- Notifications will load here via AJAX -->
          <div class="text-center py-2 text-muted">No new notifications</div>
        </div>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li class="nav-item"><a href="#" class="nav-link menu-link text-dark px-2" data-page="./Tabs/settings.php"><i class="fa fa-cogs"></i> Profile</a></li>
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
      <nav id="sidebar" class="col-md-3 col-lg-2 bg-success p-3 border border-2 border-dark">
        <div class="user-info text-center mb-4">
        <img src="<?php echo $profile_pic_path . '?' . time(); ?>" alt="Profile Picture" class="profile-img img-fluid rounded-circle mb-2" width="100">


          <h3 class="fs-5"><?= htmlspecialchars($loggedInUser) ?></h3>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/schedule.php"><i class="fa fa-home"></i> Home</a></li>
          <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/view_schedule_status.php"><i class="fa fa-list-alt"></i> View Schedule Status</a></li>
          <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/add_schedule.php"><i class="fa fa-plus"></i> Add Schedule</a></li>
          <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/chat.php"><i class="fa fa-message"></i> Chat</a></li>
          <li class="nav-item"><a href="#" class="nav-link menu-link text-dark" data-page="./Tabs/calendar.php"><i class="fa fa-calendar"></i> Calendar</a></li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main id="main-content" class="col-md-6 col-lg-8 p-4 border border-2 border-dark">
        <?php if (!empty($message)): ?>
          <div class="alert alert-<?php echo strpos($message, 'failed') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
        
        <div class="text-center mb-4">
          <h3>Update Profile Picture</h3>
          <img src="<?php echo $profile_pic_path . '?' . time(); ?>" class="profile-img" id="profilePreview">
          
          <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="mb-3">
              <label for="profilePic" class="form-label">Select new profile picture (JPG, JPEG, PNG):</label>
              <input type="file" class="form-control" name="profile_pic" id="profilePic" accept="image/jpeg, image/png" required>
            </div>
            <button type="submit" class="btn btn-success">Upload Picture</button>
          </form>
        </div>
      </main>

      <!-- ANNOUNCEMENTS -->
      <aside id="announcements" class="col-md-3 col-lg-2 bg-light p-0 border border-1 border-dark">
        <h5>Announcements</h5>
        <p>Important updates go here.</p>
      </aside>
    </div>
  </div>

  <!-- INTERACTION SCRIPTS -->
  <script>
  $(function () {
    // Sidebar toggle functionality
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

    // Profile picture preview
    document.getElementById('profilePic').addEventListener('change', function(e) {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('profilePreview').src = e.target.result;
          // Also update sidebar image preview
          document.querySelector('#sidebar img').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      }
    });
  });

  </script>
  <script>
  $(document).ready(function() {
    // Load notifications on page load
    fetchNotifications();

    // Check for new notifications every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Mark all as read
    $('#markAllRead').click(function() {
      $.post('./Process/mark_notifications_read.php', function() {
        $('#notificationBadge').hide();
        fetchNotifications();
      });
    });

// In your dashboard.php - replace all notification-related JavaScript with this:

$(document).ready(function() {
    // Initialize moment.js if not already loaded
    if (typeof moment === 'undefined') {
        console.error('Moment.js is not loaded');
    }

    // Load notifications immediately
    fetchNotifications();

    // Set up periodic refresh (every 30 seconds)
    setInterval(fetchNotifications, 30000);

    // Mark all as read handler
    $('#markAllRead').click(function(e) {
        e.stopPropagation();
        $.post('./Process/mark_notifications_read.php', function(response) {
            if (response && response.success) {
                fetchNotifications();
            }
        }, 'json').fail(() => {
            console.error('Failed to mark notifications as read');
        });
    });

    // Single notification click handler
    $(document).on('click', '.notification-item', function() {
        const notifId = $(this).data('id');
        const notifType = $(this).data('type');
        
        // Mark as read
        $.post('./Process/mark_notifications_read.php', {id: notifId}, function(response) {
            if (response && response.success) {
                // Navigate to relevant page based on type
                switch(notifType) {
                    case 'message':
                        loadPage('./Tabs/chat.php');
                        break;
                    case 'event':
                        loadPage('./Tabs/calendar.php');
                        break;
                    case 'schedule':
                        loadPage('./Tabs/view_schedule_status.php');
                        break;
                    case 'announcement':
                        loadPage('./Tabs/announcement.php');
                        break;
                    default:
                        // Just refresh for unknown types
                        fetchNotifications();
                }
            }
        }, 'json');
    });
});

// Improved fetchNotifications function
function fetchNotifications() {
    // Show loading state
    $('#notificationList').html('<div class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    $.getJSON('./Process/fetch_notifications.php')
        .done(function(data) {
            if (data.error) {
                showNotificationError(data.error);
                return;
            }

            updateNotificationDisplay(data);
        })
        .fail(function() {
            showNotificationError('Failed to load notifications');
        });
}

function updateNotificationDisplay(notifications) {
    if (!notifications || notifications.length === 0) {
        $('#notificationList').html('<div class="text-center py-2 text-muted">No notifications</div>');
        $('#notificationBadge').hide();
        return;
    }

    // Calculate unread count
    const unreadCount = notifications.filter(n => !n.is_read).length;
    $('#notificationBadge').text(unreadCount).toggle(unreadCount > 0);

    // Build notifications HTML
    let html = '';
    notifications.forEach(notif => {
        const timeAgo = moment(notif.created_at).fromNow();
        html += `
        <div class="notification-item p-2 border-bottom ${notif.is_read ? '' : 'bg-light'}" 
             data-id="${notif.id}" data-type="${notif.type}">
            <div class="d-flex justify-content-between">
                <strong>${notif.title}</strong>
                <small class="text-muted">${timeAgo}</small>
            </div>
            <p class="mb-0 small">${notif.message}</p>
        </div>`;
    });
    
    $('#notificationList').html(html);
}

function showNotificationError(message) {
    $('#notificationList').html(`<div class="text-center py-2 text-danger">${message}</div>`);
    $('#notificationBadge').hide();
}
    // Play sound on new notification (optional)
    let lastNotifCount = 0;
    function checkNewNotifications() {
      $.getJSON('./Process/fetch_notifications.php', function(data) {
        if (data.length > lastNotifCount) {
          new Audio('./assets/notification.mp3').play(); // Add sound file
        }
        lastNotifCount = data.length;
      });
    }
  });
  </script>
  </body>
  </html>