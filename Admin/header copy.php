<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>

<!-- Header Section -->
 <head>
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS (with bundled Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

 <link rel="stylesheet" href="style.css">
 </head>
<header class="app-header">
    <div class="container-fluid d-flex justify-content-between align-items-center ">
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
            <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                aria-expanded="false">
                <?= htmlspecialchars($admin_username) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a href="main.php?page=profile" class="dropdown-item text-dark">
                        <i class="fa fa-user me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">
                        <i class="fa fa-sign-out-alt me-2"></i>Logout
                    </a></li>
            </ul>
        </div>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleButton = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");
        const mainWrapper = document.getElementById("main-wrapper");

        toggleButton.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
            mainWrapper.classList.toggle("collapsed");
        });
    });
</script>
