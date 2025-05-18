<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSKEDLATOR ADMIN</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome CSS (for icons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>

<body>

    <!-- Header Section -->
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
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <?= htmlspecialchars($admin_username) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a href="main.php?page=profile" class="dropdown-item menu-link text-dark" data-page="#">
                            <i class="fa fa-user me-2"></i>Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fa fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


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

</body>

</html>
