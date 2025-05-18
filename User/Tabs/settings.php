<?php
session_start();
require_once '../../config.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare('SELECT userID, firstname, middlename, lastname FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($userID, $firstname, $middlename, $lastname);
$stmt->fetch();
$stmt->close();

// Get profile picture path if exists
$profile_pic_path = 'Assets/Profile/default.png'; // default
$pic_stmt = $conn->prepare("SELECT file_path FROM profile_pictures WHERE user_id = ?");
$pic_stmt->bind_param("i", $user_id);
$pic_stmt->execute();
$pic_stmt->bind_result($file_path);
if ($pic_stmt->fetch() && !empty($file_path)) {
    $profile_pic_path = $file_path;
}
$pic_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .profile-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
}

    </style>
</head>
<body>
    <div class="">
        <h2 class="text-center mb-4">Profile</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="updateProfileForm" enctype="multipart/form-data" method="POST">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Profile Picture</label><br>
                                <img id="profilePreview" src="<?= htmlspecialchars("../" . $profile_pic_path) ?>" alt="Profile Picture"
                                    class="profile-img mb-2">
                                <input type="file" name="profile_picture" class="form-control" id="profile_picture"
                                    accept="image/*">
                            </div>


                            <div class="mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" name="firstname" class="form-control" id="firstname"
                                       value="<?= htmlspecialchars($firstname) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="middlename" class="form-label">Middle Name</label>
                                <input type="text" name="middlename" class="form-control" id="middlename"
                                       value="<?= htmlspecialchars($middlename) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" name="lastname" class="form-control" id="lastname"
                                       value="<?= htmlspecialchars($lastname) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="userID" class="form-label">User ID</label>
                                <input type="text" name="userID" class="form-control" id="userID"
                                       value="<?= htmlspecialchars($userID) ?>" readonly>
                            </div>

                            <h5 class="mt-4">Change Password</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" id="current_password">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" id="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        </form>

                        <div id="responseMessage" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('profile_picture').addEventListener('change', function (e) {
    const [file] = e.target.files;
    if (file) {
        document.getElementById('profilePreview').src = URL.createObjectURL(file);
    }
});

$(function () {
    $('#updateProfileForm').on('submit', function (e) {
        e.preventDefault();

        const newPass = $('#new_password').val();
        const confirmPass = $('#confirm_password').val();
        if ((newPass || confirmPass) && newPass !== confirmPass) {
            $('#responseMessage').html(`<div class="alert alert-danger">❌ New passwords do not match.</div>`);
            return;
        }

        const formData = new FormData(this);

        $.ajax({
            url: './Process/process_settings.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.error) {
                    $('#responseMessage').html(`<div class="alert alert-danger">❌ ${response.error}</div>`);
                    return;
                }

                const msgs = response.message.join('<br>');
                $('#responseMessage').html(`<div class="alert alert-success">✅ ${msgs}</div>`);

                $('#firstname').val(response.firstname);
                $('#middlename').val(response.middlename);
                $('#lastname').val(response.lastname);
                $('#userID').val(response.userID);

                $('#current_password, #new_password, #confirm_password').val('');
                setTimeout(() => window.location.href = 'home.php', 1500);
            },
            error: function () {
                $('#responseMessage').html(`<div class="alert alert-danger">❌ Error updating profile.</div>`);
            }
        });
    });
});
</script>
</body>
</html>
