<?php
require '../config.php'; // Ensure database connection is included
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($username) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('❌ All fields are required.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match.');</script>";
    } else {

        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('❌ username already exists. Try another.');</script>";
        } else {

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO admins (name, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $username, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>alert('✅ Admin registered successfully! Redirecting to login...'); window.location.href='../index.php';</script>";
            } else {
                echo "<script>alert('❌ Something went wrong. Please try again.');</script>";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1abc9c, #16a085);
            padding: 20px;
        }

        .container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        h2 {
            font-weight: bold;
            color: #333;
        }

        .form-control {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: all 0.3s ease-in-out;
        }

        .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 5px rgba(26, 188, 156, 0.5);
        }

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
        <h2 class="mb-4">Admin Signup</h2>

        <form action="adminSignup.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>

        <div class="mt-3">
            <a href="../index.php" class="btn btn-outline-secondary">Back to Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
