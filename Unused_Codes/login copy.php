<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once './Models/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$user = new User($conn);
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userID = trim(htmlspecialchars($_POST['userID'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = trim($_POST['password'] ?? '');

    if (empty($userID) || empty($password)) {
        $message = "Please enter both User ID and Password.";
    } else {
        $stmt = $conn->prepare("SELECT id, lastname, firstname, middlename, password FROM users WHERE userID = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $lastname, $firstname, $middlename, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['full_name'] = trim("Hello, $firstname!") ?: "Unknown User";
                    $_SESSION['firstname'] = $firstname;
                    $_SESSION['middlename'] = $middlename;
                    $_SESSION['lastname'] = $lastname;
                    $_SESSION['userID'] = $userID;

                    header("Location: home.php");
                    exit();
                } else {
                    $message = "Invalid User ID or Password!";
                }
            } else {
                $message = "User not found!";
            }
            $stmt->close();
        } else {
            error_log("Database error: " . $conn->error);
            $message = "Something went wrong!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 🌟 Modern Page Styling */
        /* body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1abc9c, #16a085);
            padding: 20px;
        } */

        /* 🔳 Login Box */
        .container-login {

            width: 100%;
            max-width: 50%;
            text-align: center;
            /* animation: fadeIn 0.5s ease-in-out; */
        }

        /* 📝 Form Fields */
        .form-control {
            /* margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px; */
            /* border: 1px solid #ddd; */
            /* transition: all 0.3s ease-in-out; */
        }

        /* .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 5px rgba(26, 188, 156, 0.5);
        } */

        /* 🔘 Buttons */
        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .btn-primary {
            background: #1abc9c;
            border: none;
        }

        .btn-primary:hover {
            background: #16a085;
            transform: scale(1.05);
        }

        .btn-outline-secondary {
            border-color: #1abc9c;
            color: #1abc9c;
        }

        .btn-outline-secondary:hover {
            background: #1abc9c;
            color: white;
        }

        /* ❌ Error Message */
        .message {
            margin-top: 10px;
            font-weight: bold;
            color: #e74c3c;
        }

        /* 🔗 Sign Up Link */
        .signup-link {
            margin-top: 15px;
        }

        /* 🎬 Fade-in Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container-login">
        <h2 class="mb-4">Login</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="./User/login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">User ID</label>
                <input type="name" name="userID" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="signup-link">
            <p>Don't have an account?</p>
            <a href="./User/signup.php" class="btn btn-outline-secondary">Sign Up</a>
        </div>
        <div class="back-to-role"><a href="../index.php">back to role</a></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            alert("<?php echo htmlspecialchars($_SESSION['success_message']); ?>");
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
</body>
</html>
