<?php
//ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../User/Models/UserModel.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}

define('DEBUG', true);

function debugLog($message)
{
    if (DEBUG) {
        error_log("[DEBUG] " . $message);
    }
}

$message = '';
$userModel = new User($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename'] ?? '');
    $birthday = trim($_POST['birthday']);
    $defaultPassword = date('mdY', strtotime($birthday));
    $password = password_hash($defaultPassword, PASSWORD_BCRYPT);

    debugLog("POST data received: lastname=$lastname, firstname=$firstname, middlename=$middlename, birthday=$birthday");

    if (!empty($lastname) && !empty($firstname) && !empty($birthday)) {
        try {
            $stmt = $conn->prepare("INSERT INTO users (lastname, firstname, middlename, birthday, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$lastname, $firstname, $middlename, $birthday, $password]);

            $lastId = $conn->insert_id; // MySQLi version of insert_id

            debugLog("Inserted user with internal ID: $lastId");

            $yearPrefix = date('y');
            $generatedUserID = $yearPrefix . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);

            $updateStmt = $conn->prepare("UPDATE users SET userID = ? WHERE id = ?");
            $updateStmt->execute([$generatedUserID, $lastId]);

            debugLog("Updated userID to: $generatedUserID");

            $_SESSION['message'] = "<div class='alert alert-success'>User successfully added with User ID: <strong>" . htmlspecialchars($generatedUserID) . "</strong>. Default password is their birthday: <strong>" . htmlspecialchars($defaultPassword) . "</strong>.</div>";
            echo "<script>window.location.href='" . "./main.php?page=addUser" . "';</script>";
            exit();
            
        } catch (PDOException $e) {
            $errorMsg = "Database error: " . $e->getMessage();
            error_log("[ERROR] " . $errorMsg);
            if (DEBUG)
                debugLog("SQL failed: " . ($stmt->queryString ?? 'N/A'));
            $message = "<div class='alert alert-danger'>" . htmlspecialchars($errorMsg) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all required fields.</div>";
        debugLog("Form validation failed. Required fields missing.");
    }
}
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="add-user-container">
        <h2>Add New User</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        } else {
            echo '<div class="alert alert-info" role="alert">
                <strong>Note:</strong> Default password is the user\'s birthday in mmddyyyy format.
              </div>';
        }
        ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="lastname" id="lastname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="firstname" id="firstname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="middlename" class="form-label">Middle Name</label>
                <input type="text" name="middlename" id="middlename" class="form-control">
            </div>

            <div class="mb-3">
                <label for="birthday" class="form-label">Birthday <span class="text-danger">*</span></label>
                <input type="date" name="birthday" id="birthday" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Add User</button>
        </form>
    </div>
</body>

<?php 
//ob_end_flush();
//if (headers_sent($file, $line)) {
//    echo "Headers already sent in $file on line $line";
//}
?>

</html>
<style>
body {
    background-color: #f5f5f5;
    font-family: 'Poppins', sans-serif;
}

.container.add-user-container {
    max-width: 800px;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #ddd;
}

h2 {
    font-weight: 600;
    margin-bottom: 25px;
    color: #333;
}

.alert {
    font-size: 14px;
    border-radius: 8px;
}

.form-label {
    font-weight: 500;
    margin-bottom: 5px;
}

.form-control {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    box-shadow: none;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

button[type="submit"] {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background: #0056b3;
}
</style>

