<?php
require_once 'config.php';
require_once 'setup.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inskedlator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* General Styles */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
            background-image: url("./Assets/Backgrounds/bg_1.jpg");
            background-size: cover;
            background-position: center;
            font-family: 'Poppins', sans-serif;
        }

        /* Container */
        .container-index {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 12px;
            /* box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); */
            text-align: center;
            justify-content: center;
            width: 50%;
            max-width: 50%;
            height: 70%;
            max-height: 70%;
            backdrop-filter: blur(10px);
            animation: fadeIn 0.5s ease-in-out;
            /* display: flex;
            justify-content: center; */
            align-items: center;
        }
        #login{
            display: flex;
            justify-content: center;
            align-items: center;
        }
        /* Navigation */
        .nav {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 20px;
        }

        .nav a, .dropdown-toggle {
            font-size: 1rem;
            font-weight: 600;
            color: #2C3E50;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav a:hover, .nav a.active {
            color: white;
            background: #1ABC9C;
        }

        /* Sections */
        .content-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        #home {
            display: block;
        }

        /* Dropdown */
        .dropdown-menu {
            text-align: center;
        }
        .dropdown {
            position: relative;
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container-index">
        <!-- Navigation -->
        <div class="nav">
            <a onclick="showContent('home')" id="nav-home" class="active">HOME</a>
            <a onclick="showContent('about')" id="nav-about">ABOUT US</a>
            <a onclick="showContent('contact')" id="nav-contact">CONTACT</a>

            <!-- Dropdown Login -->
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" id="loginDropdown" data-bs-toggle="dropdown" aria-expanded="false">LOGIN</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" onclick="loadLoginForm('User/login.php')">Login as User</a></li>
                    <li><a class="dropdown-item" onclick="loadLoginForm('Admin/adminLogin.php')">Login as Admin</a></li>
                </ul>
            </div>
        </div>

        <!-- Content Area -->
        <div id="content">
            <div id="home" class="content-section">
                <h2>WELCOME TO INSKEDLATOR</h2>
                <p>Inskedlator is a scheduling management system designed to simplify and streamline the process of creating,
                   managing, and viewing schedules for students, teachers, and administrators...</p>
            </div>

            <div id="about" class="content-section">
                <h2>ABOUT US</h2>
                <p>We are dedicated to providing an easy-to-use scheduling management system...</p>
            </div>

            <div id="contact" class="content-section">
                <h2>CONTACT</h2>
                <p>Email us at support@inskedlator.com or call (123) 456-7890</p>
            </div>

            <div id="login" class="content-section"></div>
        </div>
    </div>

    <script>
        function showContent(sectionId) {
            $("#content").fadeOut(200, function () {
                $(".content-section").hide();
                $("#" + sectionId).fadeIn(300);
                $("#content").fadeIn(300);
            });

            $(".nav a").removeClass("active");
            $("#nav-" + sectionId).addClass("active");
        }

        function loadLoginForm(page) {
            $("#content").fadeOut(200, function () {
                $("#login").load(page, function () {
                    
                    $(".content-section").hide();
                    $("#login").fadeIn(300);
                    $("#content").fadeIn(300);
                });
            });

            $(".nav a").removeClass("active");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex; 
            justify-content: center; 
            align-items: center;
            height: 100vh; 
            background-color: #f8f9fa; 
            margin: 0;
            background-image: url("./Assets/Backgrounds/bg_1.jpg");
            background-size: cover; /* Ensures the image covers the entire screen */
            background-position: center; /* Centers the background image */
            font-family: 'Arial', sans-serif; /* Improved font */
        }
        .container {
            background: rgba(255, 255, 255, 0.85); /* Slight transparency for the background */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 80%;
            max-width: 700px; /* Ensures container width is limited */
            height: auto;
            backdrop-filter: blur(10px); /* Adds a blur effect for a modern touch */
        }
        .container h2 {
            color: #2C3E50;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .container a {
            font-size: 1.2rem;
            margin: 10px;
            color: #2C3E50;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }
        .container a:hover {
            color: #1ABC9C;
        }
        .btn {
            padding: 12px 20px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary {
            background-color: #1ABC9C;
            border-color: #1ABC9C;
        }
        .btn-primary:hover {
            background-color: #16A085;
            border-color: #16A085;
            transform: scale(1.05);
        }
        .btn-secondary {
            background-color: #4E5D6C;
            border-color: #4E5D6C;
        }
        .btn-secondary:hover {
            background-color: #34495E;
            border-color: #34495E;
            transform: scale(1.05);
        }
        .header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;

        }
        .header a {
            margin: 0 15px;
            font-size: 1rem;
            color: #2C3E50;
            text-decoration: none;
            font-weight: normal;
        }
        .header a:hover {
            color: #1ABC9C;
        }
        p {
            font-size: 1.2rem; /* Slightly larger font size for readability */
            line-height: 1.6; /* Increases line spacing for better readability */
            color: #34495E; /* Dark grey color for the text */
            margin-bottom: 20px; /* Adds space below the paragraph */
            font-weight: normal; /* Keeps the text weight normal */
            text-align: justify; /* Justify the text for a clean, aligned look */
            padding: 10px 20px; /* Adds padding around the text */
            background-color: rgba(255, 255, 255, 0.7); /* Light background to enhance readability */
            border-radius: 8px; /* Softens the edges of the background */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Adds a subtle shadow for depth */
            max-width: 90%; /* Ensures the text doesn't go too wide */
            margin: 20px auto; /* Centers the text and gives it space around */
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="./index.php">HOME</a>
            <a href="">ABOUT US</a>
            <a href="">CONTACT</a>
            <!-- Dropdown for Login -->
            <!-- <div class="dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">LOGIN</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="./User/login.php">Login as User</a></li>
                    <li><a class="dropdown-item" href="./Admin/adminLogin.php">Login as Admin</a></li>
                </ul>
            </div>
        </div>
        <h2 class="mb-4">WELCOME TO INSKEDLATOR</h2>
        <p> Inskedlator is a scheduling management system designed to simplify and streamline the process of creating,
             managing, and viewing schedules for students, teachers, and administrators. With Inskedlator, 
             users can easily add, update, and view schedules for various subjects, days, and times. 
             The system ensures that there are no scheduling conflicts and provides a user-friendly 
             interface to handle scheduling tasks efficiently. It is an essential tool for educational 
             institutions to organize their academic schedules while maintaining a seamless experience for 
             both administrators and users.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> --> -->
