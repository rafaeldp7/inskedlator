<?php

require_once('../config.php'); // Include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php'); // Redirect to login if not logged in
    exit();
}

// Fetch admin data from the database
$admin_id = $_SESSION['admin_id'];
$query = "SELECT id, name, username, created_at FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// If no admin found, log out
if (!$admin) {
    session_destroy();
    echo "<script>window.location.href='" . "../index.php" . "';</script>";
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Optionally, hash the new password if changed
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admins SET name = ?, username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $name, $username, $hashed_password, $admin_id);
    } else {
        $update_query = "UPDATE admins SET name = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $name, $username, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully.";
        echo "<script>window.location.href='" . "./main.php?page=profile" . "';</script>";
        exit();
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
   
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>PROFILE</h1>
        </div>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="main.php?page=profile" method="POST" class="profile-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password (Leave blank to keep current password)</label>
                <input type="password" id="password" name="password">
            </div>

            <button type="submit" class="btn_update">Update Profile</button>
        </form>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
<style>

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6f8;
    padding:  0.5rem 0;
}

/* Profile container */
.profile-container {
    width: 100%;
    max-width: 70%;
    margin: auto;
    padding: 2rem;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    /* margin-top: 5vh;
    margin-bottom: 5vh; */
}



/* Form styles */
.profile-form .form-group {
    margin-bottom: 1.25rem;
}

.profile-form label {
    font-weight: 600;
    color: #34495e;
    display: block;
    margin-bottom: 6px;
    font-size: 1rem;
}

.profile-form input {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    width: 100%;
}

.profile-form input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    outline: none;
}

/* Button Styles */
.btn_update {
    padding: 14px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

.btn_update:hover {
    background-color: #0056b3;
}

/* Logout button */
.logout-btn {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: color 0.3s ease;
    width: 100%;
}

.logout-btn:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Success and error messages */
.message {
    padding: 12px;
    margin-bottom: 1rem;
    border-radius: 6px;
    text-align: center;
    font-weight: 500;
    font-size: 1.95rem;
}

.success {
    background-color: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}

.error {
    background-color: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}

/* Responsive (Mobile-first) */
@media (max-width: 500px) {
    .profile-container {
        margin: 2rem 1rem;
        padding: 1.5rem;
    }

    .profile-header h1 {
        font-size: 1.75rem;
    }

    .btn_update, .logout-btn {
        font-size: 0.95rem;
    }
}


</style>