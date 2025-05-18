<?php
// Start session and enable error reporting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config.php'; 
require './Models/UserModel.php'; 

$user = new User($conn);
$message = "";

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Invalid CSRF token!";
        header("Location: signup.php");
        exit();
    }

    // Get form inputs and sanitize
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $middlename = !empty($_POST['middlename']) ? htmlspecialchars(trim($_POST['middlename'])) : "";
    $userID = trim($_POST['userID']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation checks
    if (strlen($password) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match.";
    } else {
        
        // Register user
        $registrationResult = $user->register($lastname, $firstname, $middlename, $userID, $password);
        
        if ($registrationResult === true) {
            $_SESSION['message'] = "Signup successful! Please log in.";
            header("Location: login.php?success=1");
            exit();
        } else {
            $_SESSION['message'] = $registrationResult;
        }
    }

    // Redirect back to registration page if there's an error
    header("Location: signup.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>

    <style>
        /* General Page Styling */


        /* Signup Container */
        .container {
            /* background: white; */
            padding: 40px;
            /* border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); */
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Form Fields */
        .form-control {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s ease-in-out;
        }

        .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 5px rgba(26, 188, 156, 0.5);
        }

        /* Buttons */
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

        /* Message Box */
        .message {
            margin-top: 15px;
            font-weight: bold;
            color: #e74c3c;
        }

        /* Login Link */
        .login-link {
            margin-top: 15px;
        }

        /* Fade-in Animation */
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
    <div class="container">
        <h2 class="mb-4">Signup</h2>

        <?php if (!empty($_SESSION['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middlename" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">User ID</label>
                <input type="text" name="userID" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
