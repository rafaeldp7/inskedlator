<?php
session_start();
require_once '../../config.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT userID, firstname, middlename, lastname FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($userID, $firstname, $middlename, $lastname);
$stmt->fetch();
$stmt->close();

// Prepare data for form
$user = [
    'userID'     => $userID,
    'firstname'  => $firstname,
    'middlename' => $middlename,
    'lastname'   => $lastname,
];
?>


<html>
<body>
<div class="container py-5">
    <h3 class="text-center mb-4">Edit Profile</h3>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="updateProfileForm">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control" id="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" name="middlename" class="form-control" id="middlename" value="<?= htmlspecialchars($user['middlename']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control" id="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="userID" class="form-label">User ID</label>
                            <input type="text" name="userID" class="form-control" id="userID" value="<?= htmlspecialchars($user['userID']) ?>" required>
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

<!-- <script>
$(function() {
    $('#updateProfileForm').on('submit', function(e) {
        e.preventDefault();

        // Client-side password match check
        var newPass = $('#new_password').val();
        var confirmPass = $('#confirm_password').val();
        if ((newPass || confirmPass) && newPass !== confirmPass) {
            $('#responseMessage').html(`<div class="alert alert-danger">❌ New passwords do not match.</div>`);
            return;
        }

        $.ajax({
            url: './Process/process_settings.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    $('#responseMessage').html(`<div class="alert alert-danger">❌ ${response.error}</div>`);
                    return;
                }

                var msgs = response.message.join('<br>');
                $('#responseMessage').html(`<div class="alert alert-success">✅ ${msgs}</div>`);

                // Update fields
                $('#firstname').val(response.firstname);
                $('#middlename').val(response.middlename);
                $('#lastname').val(response.lastname);
                $('#userID').val(response.userID);

                // Clear password fields and redirect after a moment
                setTimeout(function() {
                    $('#current_password, #new_password, #confirm_password').val('');
                    window.location.href = 'home.php';
                }, 1500);
            },
            error: function() {
                $('#responseMessage').html(`<div class="alert alert-danger">❌ Error updating profile.</div>`);
            }
        });
    });
});
</script> -->


</body>
</html>
