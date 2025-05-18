<?php
session_start();
require_once 'config.php';
require_once 'setup.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);



$error = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inskedlator</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #ffffff;
      color: #212529;

    }
    .container {
      min-height: 97vh;
    }
    .navbar {
      
      padding-bottom: 1rem;
      margin-bottom: 2rem;
    }
    .brand {
      font-weight: bold;
      font-size: 2rem;
      color: #167716;
      cursor: pointer;
    }
    h2{
      color: #167716; 
      font-size: 6rem;
    }
    p{
      font-size: 2rem;
    }
    .nav-buttons .btn {
      min-width: 100px;
      margin-right: 0.5rem;
    }
    .btn-getStarted{
            padding: 0.5rem 2rem;
            margin-top: 1rem;
      background-color:rgb(21, 98, 21);
      color: #ffffff;
      border: none;

      border-radius: 0.5rem;
      font-size: 2rem;
      font-weight: bold;
    }
    .dropdown .btn {
      min-width: 100px;
      border: 1px solid #167716;
    }
    .section {
      display: none;
    }
    .section.active {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      animation: fadeIn 0.4s ease-in-out;
    }
    .card-form {

      max-width: 50%;

      border-radius: 0.5rem;
      background-color: #fff;
    }
    footer {

      width: 100%;
      color: #6c757d;
      display: flex;
      justify-content: center;


    }

    .tab-btn {
      border: none;
      background-color: transparent;
      border-bottom: 2px solid transparent;
      color: #212529;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .tab-btn:hover, .dropdown .btn:hover {
      border-bottom: 2px solid #167716;
      color: #167716;
    }

    .tab-btn.active-tab {
      border-bottom: 3px solid #167716;
      color: #167716;
      font-weight: bold;
    }
    .btn dropdown-toggle{
      border:1px #167716
    }
    #submit-btn{
      background-color: #167716;
      color: #ffffff;
      border: none;
      border-radius: 0.5rem;
      font-size: 1.5rem;
      width: 100%;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="container ">
    <!-- Navbar -->
    <div class="navbar d-flex justify-content-between align-items-center flex-wrap">
      <div class="brand" onclick="showSection('home')">INSKEDLATOR</div>
      <div class="nav-buttons d-flex flex-wrap">
        <button class="btn tab-btn" onclick="showSection('home')">HOME</button>
        <button class="btn tab-btn" onclick="showSection('about')">ABOUT US</button>
        <button class="btn tab-btn" onclick="showSection('contact')">CONTACT</button>
        <div class="dropdown">
          <button class="btn dropdown-toggle" data-bs-toggle="dropdown">LOGIN</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" onclick="showSection('user-login')">User Login</a></li>
            <li><a class="dropdown-item" onclick="showSection('admin-login')">Admin Login</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Sections -->
    <div id="home" class="section active">
      <div class="row flex-1 justify-content-center align-items-center">
        <div class="col-md-6 ">
          <h2 class="fw-bold mb-3">Schedule your tasks</h2>
          <p>Inskedlator is a scheduling management system designed to simplify and streamline scheduling for students, teachers, and administrators.</p>
          <button class="btn-getStarted" onclick="showSection('user-login')">Get Started</button>
        </div>
        <div class="col-md-6 text-center">
          <img src="./Assets/Backgrounds/bg_1.png" alt="Landing" class="img-fluid" />
        </div>
      </div>
    </div>

    <div id="about" class="section">
      <h2 class="fw-bold">ABOUT US</h2>
      <p>We help schools and institutions stay organized. Our mission is to reduce administrative burdens with intuitive scheduling tools.</p>
    </div>

    <div id="contact" class="section">
      <h2 class="fw-bold">CONTACT</h2>
      <p>Email: <strong>support@inskedlator.com</strong> | Phone: <strong>(123) 456‑7890</strong></p>
    </div>

    <div id="user-login" class="section">
      <div class="card-form ">
        <h2>User Login</h2>
        <form action="login.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="userID" class="form-label">User ID</label>
            <input type="text" name="userID" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="user-password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button id="submit-btn" type="submit" class="btn">Login</button>
        </form>
      </div>
    </div>

    <div id="admin-login" class="section">
      <div class="card-form">
        <h2>Admin Login</h2>
        <form action="AdminLogin.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="adminID" class="form-label">Admin Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="admin-password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-danger w-100">Login</button>
        </form>
      </div>
    </div>

    <div id="user-register" class="section">
      <div class="card-form">
        <h2>User Registration</h2>
        <form action="signup.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3"><input type="text" name="lastname" class="form-control" placeholder="Last Name" required></div>
          <div class="mb-3"><input type="text" name="firstname" class="form-control" placeholder="First Name" required></div>
          <div class="mb-3"><input type="text" name="middlename" class="form-control" placeholder="Middle Name"></div>
          <div class="mb-3"><input type="text" name="userID" class="form-control" placeholder="User ID" required></div>
          <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
          <div class="mb-3"><input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required></div>
          <button type="submit" class="btn btn-success w-100">Register</button>
        </form>
      </div>
    </div>

    
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function showSection(id, el = null) {
      document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
      });
      document.getElementById(id).classList.add('active');

      if (el) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
          btn.classList.remove('active-tab');
        });
        el.classList.add('active-tab');
      }
    }

  </script>
</body>
  <footer>© 2025 INSKEDLATOR. All rights reserved.</footer>
</html>
