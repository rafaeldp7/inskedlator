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
  <meta name="description" content="Inskedlator — streamlined scheduling for students, teachers, and administrators." />
  <title>Inskedlator</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      /*background: #f8f9fa url("./Assets/Backgrounds/bg_1.jpg") center/cover no-repeat;*/
      background: #f8f9fa ;
      font-family: 'Poppins', sans-serif;
      overflow:auto;
    }

    /* .container-index {
      background: rgba(255, 255, 255, 0.9);
      padding: 40px;
      border-radius: 12px;
      text-align: center;
      width: 50%;
      max-width: 600px;
      height: 70%;
      max-height: 80vh;
      backdrop-filter: blur(10px);
      animation: fadeIn 0.5s ease-in-out;
    } */
    .container-index {
      background: rgba(255, 255, 255, 0.9);
      padding: 40px;
      border-radius: 12px;
      text-align: center;
      width: 100%;
      height: 100%;
      overflow-y: auto;
      backdrop-filter: blur(10px);
      animation: fadeIn 0.5s ease-in-out;
      box-sizing: border-box;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 5px;
      margin-bottom: 20px;
      flex-wrap: wrap;
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
      color: #fff;
      background: #224529;
    }

    .content-section {
      display: none;
      animation: fadeIn 0.5s ease-in-out;
    }
    /* .form-box {
      background: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      max-width: 500px;
      margin: 0 auto;
      animation: fadeIn 0.5s ease-in-out;
    } */
/* FORM BOX */
    .form-box {
      background: #ffffff;
      padding: 30px 20px;
      border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 100%;
      margin: 0 auto 20px auto;
      box-sizing: border-box;
      animation: fadeIn 0.5s ease-in-out;
      text-align: left;
    }

    #home {
      display: block;
      text-align: justify;
    }

    h1 {
      font-size: 2.5rem;
      font-weight: bold ;
      color: #2C3E50;
      cursor: pointer;
    }
    h2 {
      font-size: 3rem;
      font-weight: bold;
      color: #167618;
      flex-wrap: wrap;
    }
    p {
      font-size: 1.5rem;
      line-height: 1.6;
      color: #34495E;
      flex-wrap: wrap;

    }
    flex-1 {
      width: 50%;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .container-index {
        width: 90%;
        height: auto;
        max-height: 90vh;
        overflow-y: auto;
      }
    }
    @media (max-width: 480px) {
  .container-index {
    padding: 20px;
  }

  .nav a, .dropdown-toggle {
    font-size: 0.9rem;
    padding: 6px 12px;
  }

  /* p {
    font-size: 1rem;
    padding: 8px 12px;
  } */
}

  </style>
</head>
<body>
  <div class="container-index">
    <!-- Navigation -->
    <div class="nav">
      <h1 onclick="showContent('home')"  >INSKEDLATOR</h1>

      <div>


      <a onclick="showContent('home')" id="nav-home" class="active">HOME</a>
      <a onclick="showContent('about')" id="nav-about">ABOUT US</a>
      <a onclick="showContent('contact')" id="nav-contact">CONTACT</a>
      </div>
      <div class="dropdown">
        <a class="dropdown-toggle" href="#" id="loginDropdown" data-bs-toggle="dropdown">
          LOGIN
        </a>
        <ul class="dropdown-menu" aria-labelledby="loginDropdown">
          <li><a class="dropdown-item" onclick="showContent('user-login')">User Login</a></li>
          <!-- <li><a class="dropdown-item" onclick="showContent('user-register')">Register</a></li> -->
          <li><a class="dropdown-item" onclick="showContent('admin-login')">Admin Login</a></li>
        </ul>
      </div>
    </div>

    <!-- Error Message -->
    <?php if ($error): ?>
      <div class="alert alert-danger mt-3 mx-auto w-75">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
  <div class="alert alert-success mt-3 mx-auto w-75">
    <?= htmlspecialchars($success) ?>
  </div>
<?php endif; ?>


    <!-- Dynamic Content Sections -->
    <div id="content">
      <!-- Home -->
      <div id="home" class="content-section" style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
        
        <div class="flex-1">
          
        
        <h2>Schedule your tasks</h2>
        <p>Inskedlator is a scheduling management 
          system designed to simplify and streamline 
          the process of creating, managing, and viewing 
          schedules for students, teachers, and administrators.
        </p>
        <button class="btn btn-primary" onclick="showContent('user-register')">Get Started</button>
        </div>
        <div class="flex-1">
          <img src="./Assets/Backgrounds/bg_1.png" alt="Landing Image" class="img-fluid" style="max-width: 100%; height: auto;">
        </div>
      </div>

      <!-- About -->
      <div id="about" class="content-section">
        <h2>ABOUT US</h2>
        <p>We’re dedicated to helping schools and institutions stay organized. Our mission is to reduce administrative burdens with intuitive scheduling tools and intelligent conflict detection.</p>
      </div>

      <!-- Contact -->
      <div id="contact" class="content-section">
        <h2>CONTACT</h2>
        <p>Email us at <strong>support@inskedlator.com</strong> or call <strong>(123) 456‑7890</strong>.</p>
      </div>

      <!-- User Login -->
      <div id="user-login" class="content-section form-box">
        <h2>User Login</h2>
        <form action="login.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="userID" class="form-label">User ID</label>
            <input type="text" name="userID" id="userID" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="user-password" class="form-label">Password</label>
            <input type="password" name="password" id="user-password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>

      <!-- Admin Login -->
      <div id="admin-login" class="content-section form-box">
        <h2>Admin Login</h2>
        <form action="AdminLogin.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label for="adminID" class="form-label">Admin ID</label>
            <input type="text" name="username" id="adminID" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="admin-password" class="form-label">Password</label>
            <input type="password" name="password" id="admin-password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-danger w-100">Login</button>
        </form>
      </div>


      <footer class="text-center p-2 text-muted" style="font-size: 12px; position: fixed; bottom: 0; width: 100%;">
        © 2025 INSKEDLATOR. All rights reserved.
      </footer>
      
      <!-- User Register -->
      <div id="user-register" class="content-section form-box">
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

  <script>
    function showContent(sectionId) {
      const dd = bootstrap.Dropdown.getInstance(document.getElementById('loginDropdown'));
      if (dd) dd.hide();

      $("#content").fadeOut(200, () => {
        $(".content-section").hide();
        $("#" + sectionId).fadeIn(300);
        $("#content").fadeIn(300);
      });

      $(".nav a").removeClass("active");
      $("#nav-" + sectionId).addClass("active");
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
