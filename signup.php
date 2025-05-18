<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php'; 
require 'User/Models/UserModel.php'; 

$user = new User($conn);


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token!";
        header("Location: signup.php");
        exit();
    }

   
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $middlename = !empty($_POST['middlename']) ? htmlspecialchars(trim($_POST['middlename'])) : "";
    $userID = trim($_POST['userID']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    
    if (strlen($password) < 6) {
        $_SESSION['error_message'] = "Password must be at least 6 characters long.";
        echo "<script>alert('Password must be at least 6 characters long.');</script>";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        
        try {
            $registrationResult = $user->register($lastname, $firstname, $middlename, $userID, $password);
            if ($registrationResult === true) {
                $_SESSION['success_message'] = "Signup successful! Please log in.";
                header("Location: User/home.php");
                exit();
            } else {
                $_SESSION['error_message'] = $registrationResult;
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        }
        
    }

    header("Location: index.php");
    exit();
}
