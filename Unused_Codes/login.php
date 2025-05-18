<?php
// login.php — Handle user login POST
session_start();
require_once 'config.php';
require_once 'User/Models/UserModel.php';

// 1) CSRF validation
if (
    empty($_POST['csrf_token'])
    || ! hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['error_message'] = 'Invalid CSRF token.';
    header('Location: index.php');
    exit;
}

// 2) Input validation
$userID   = trim($_POST['userID']   ?? '');
$password = trim($_POST['password'] ?? '');

if ($userID === '' || $password === '') {
    $_SESSION['error_message'] = 'Please enter both User ID and Password.';
    header('Location: index.php');
    exit;
}

// 3) Fetch user record
$model = new User($conn);
$user  = $model->findByUserID($userID);

// 4) Verify credentials
if (
    ! $user
    || ! password_verify($password, $user['password'])
) {
    $_SESSION['error_message'] = 'Invalid User ID or Password.';
    header('Location: index.php');
    exit;
}

// 5) Success: populate session and redirect
$_SESSION['user_id']    = $user['id'];
$_SESSION['userID']     = $user['userID'];
$_SESSION['lastname']   = $user['lastname'];
$_SESSION['firstname']  = $user['firstname'];
$_SESSION['middlename'] = $user['middlename'];
$_SESSION['full_name']  = "Hello, {$user['firstname']}!";

header('Location: User/home.php');
exit;
