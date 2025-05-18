<?php
session_start();

// Include your database connection here
include('../../config.php'); // Make sure this file contains your database connection details

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['userID'];

// Query to fetch user data from the database using prepared statements
$query = "SELECT * FROM users WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userID); // "s" denotes the type of the parameter (string)
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user exists
if (!$user) {
    echo "User not found.";
    exit();
}

// Output the user profile
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>


    <style>
        body {
            background-color: #f7f7f7;
            /* font-family: 'Arial', sans-serif; */
        }
        .profile-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-header h1 {
            font-size: 36px;
            color: #333;
        }
        .profile-header p {
            font-size: 18px;
            color: #888;
        }
        .profile-info {
            font-size: 18px;
            color: #444;
        }
        .profile-info p {
            padding: 8px 0;
        }
        .profile-info strong {
            color: #333;
        }
        .logout-btn {
            display: block;
            text-align: center;
            padding: 10px 20px;
            margin-top: 30px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
            }
            .profile-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Welcome, <?php echo htmlspecialchars($user['firstname']); ?>!</h1>
            <p>Your profile details</p>
        </div>

        <div class="profile-info">
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['middlename']) . ' ' . htmlspecialchars($user['lastname']); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['userID']); ?></p>
            <p><strong>Account Created On:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>

        <a href="./Process/logout.php" class="logout-btn">Logout</a>
    </div>


</body>
</html>
