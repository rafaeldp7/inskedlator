<div class="sidebar">
    <div class="user-info">
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loggedInUser = $_SESSION['username'] ?? 'Guest'; // Fallback if not logged in
        $defaultProfile = "./Profile/profile_icon.png"; // Default profile picture
        $profilePath = $defaultProfile; // Start with default

        // Check for profile picture with different possible extensions
        // $possibleExtensions = ["jpg", "jpeg", "png"];
        // foreach ($possibleExtensions as $ext) {
        //     $filePath = "./Profile/" . $loggedInUser . "." . $ext;
        //     if (file_exists($filePath)) {
        //         $profilePath = $filePath;
        //         break; // Stop checking once found
        //     }
        // }
        ?>

        <!-- Profile Picture -->
        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile Picture" class="profile-pic">



        <h3 style="font-size: 2rem, max-width:100%"><?= htmlspecialchars($loggedInUser) ?></h3>
    </div>

    <ul>
        <li><a href="#" class="menu-link active" data-page="./Tabs/schedule.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/view_schedule_status.php"><i class="fa fa-plus"></i> View Schedule Status</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/add_schedule.php"><i class="fa fa-plus"></i> Add Schedule</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/chat.php"><i class="fa fa-message"></i> Chat</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/calendar.php"><i class="fa fa-calendar"></i> Calendar</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/announcement.php"><i class="fa fa-bullhorn"></i> Announcement</a></li>
        <li><a href="#" class="menu-link active" data-page="./Tabs/settings.php"><i class="fa fa-user"></i> Profile</a></li>
    
        <!-- Corrected Logout Link -->
        <li><a href="#" id="logout-link"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<script>
document.getElementById("logout-link").addEventListener("click", function(event) {
    event.preventDefault(); // Prevent immediate redirection

    let confirmLogout = confirm("Are you sure you want to log out?");
    
    if (confirmLogout) {
        window.location.href = "./Process/logout.php"; // Redirect to logout.php
    }
});
</script>
