<?php
require './config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "<script>alert('❌ Please enter both username and password.');</script>";
        echo "<script> window.location.href='./index.php';</script>";
    } else {

        $stmt = $conn->prepare("SELECT id, password, name FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $hashed_password, $name);
            $stmt->fetch();

  
            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_name'] = $name; // Assuming the name is the same as username for simplicity
                echo "<script> window.location.href='./Admin/main.php';</script>";


                exit();
            } else {
                echo "<script>alert('❌ Invalid password.');</script>";
                echo "<script> window.location.href='./index.php';</script>";
            }
        } else {
            echo "<script>alert('❌ Admin not found.');</script>";
            echo "<script> window.location.href='./index.php';</script>";
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
    <title>Admin Login</title>

    <style>

        .login-container {

            width: 400px;
            text-align: center;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
        }
        .btn-secondary {
            background-color: #1ABC9C;
            border-color: #1ABC9C;
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #16A085;
            border-color: #16A085;
            transform: scale(1.05);
        }
        .alert {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-secondary w-100">Login</button>
        </form>
    </div>
</body>
</html>
