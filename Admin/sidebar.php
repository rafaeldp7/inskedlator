<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin'; // Default to 'Admin' if not set
?>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<nav id="sidebar" class="p-3 border-end border-2 border-dark position-sticky top-0 vh-100">
    <div class="user-info text-center mb-4">
        <img src="../Assets/Profile/default.png" alt="Profile Picture" class="img-fluid rounded-circle mb-2" width="100">
        <h3 class="fs-6 text-white">Hello, <?= htmlspecialchars($admin_name) ?></h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="main.php?page=schedule" class="nav-link menu-link">Schedules</a>
        </li>
        <li class="nav-item">
            <a href="main.php?page=announcement" class="nav-link menu-link">Announcement</a>
        </li>
        <li class="nav-item">
            <a href="main.php?page=chat" class="nav-link menu-link">Chat</a>
        </li>
        <li class="nav-item">
            <a href="main.php?page=calendar" class="nav-link menu-link">Calendar</a>
        </li>
        <li class="nav-item">
            <a href="main.php?page=addUser" class="nav-link menu-link">Add User</a>
        </li>

        <li class="nav-item">
            <a href="main.php?page=users" class="nav-link menu-link">Users</a>
        </li>
        <!-- <li class="nav-item">
            <a href="main.php?page=profile" class="nav-link menu-link">Profile</a>
        </li> -->
    </ul>
</nav>
