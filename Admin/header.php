<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<header class="admin-header">
    <div class="admin-header-container">
        <div class="admin-header-left">
            <div class="admin-header-title">
                <h1 class="admin-title">INSKEDLATOR ADMIN</h1>
                <p class="admin-subtitle">PERSONNEL SCHEDULING GENERATOR</p>
            </div>
            <button class="admin-toggle-btn" id="sidebarToggle" aria-expanded="true">
                ☰
            </button>
        </div>
        <div class="admin-dropdown">
            <button class="admin-user-btn" id="userDropdownToggle">
                <?= htmlspecialchars($admin_username) ?> ▼
            </button>
            <ul class="admin-dropdown-menu" id="userDropdownMenu">
                <li><a href="main.php?page=profile" class="admin-dropdown-item">Profile</a></li>
                <li class="admin-divider"></li>
                <li><a class="admin-dropdown-item admin-logout" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<script>
    // Sidebar toggle logic
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");
        const mainWrapper = document.getElementById("main-wrapper");

        toggleButton.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
            mainWrapper.classList.toggle("collapsed");
        });

        // Dropdown toggle logic
        const dropdownBtn = document.getElementById("userDropdownToggle");
        const dropdownMenu = document.getElementById("userDropdownMenu");

        dropdownBtn.addEventListener("click", function () {
            dropdownMenu.classList.toggle("show");
        });

        document.addEventListener("click", function (e) {
            if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove("show");
            }
        });
    });
</script>
<style>
    .admin-header {
    background-color: #167716;
    color: #fff;
    padding: 10px 20px;
}

.admin-header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header-left {
    display: flex;
    align-items: center;
}

.admin-header-title {
    text-align: center;
}

.admin-title {
    margin: 0;
    font-size: 1.5rem;
}

.admin-subtitle {
    margin: 0;
    font-size: 0.9rem;
}

.admin-toggle-btn {
    margin-left: 20px;
    background: none;
    border: 1px solid #ffffff;
    color: #ffffff;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 1.1rem;
}

.admin-user-btn {
    background: none;
    border: 1px solid #ffffff;
    color: #ffffff;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
}

.admin-dropdown {
    position: relative;
}

.admin-dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background-color: #ffffff;
    color: #000000;
    border: 1px solid #cccccc;
    border-radius: 4px;
    margin-top: 5px;
    display: none;
    list-style: none;
    padding: 0;
    z-index: 1000;
}

.admin-dropdown-menu.show {
    display: block;
}

.admin-dropdown-item {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #000;
}

.admin-dropdown-item:hover {
    background-color: #f0f0f0;
}

.admin-divider {
    border-top: 1px solid #ccc;
    margin: 5px 0;
}

.admin-logout {
    color: red;
}

</style>