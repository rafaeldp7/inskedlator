<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$_SESSION = [];


session_destroy();


session_start();
session_regenerate_id(true);


if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}


echo "<script>
        alert('You have been successfully logged out.');
        window.location.href = '../index.php';
      </script>";

exit();
?>
