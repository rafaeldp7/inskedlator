<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['userID'])) {
    die("Unauthorized access");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Profile Picture</title>
    <style>
        .profile-container {
            padding: 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        .profile-img-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .upload-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .btn-upload {
            background-color: #198754;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-upload:hover {
            background-color: #157347;
        }
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-img-container">
            <img src="<?php echo $currentPic . '?' . time(); ?>" class="profile-img" id="profilePreview">
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'failed') !== false || strpos($message, 'not found') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="profilePic">Select new profile picture:</label>
                <input type="file" class="form-control" name="profile_pic" id="profilePic" accept="image/jpeg, image/png" required>
            </div>
            <button type="submit" class="btn-upload">Upload Picture</button>
        </form>
    </div>

    <script>
        // Show preview when file selected
        document.getElementById('profilePic').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Clear message after 5 seconds
        setTimeout(function() {
            var messageEl = document.querySelector('.message');
            if (messageEl) {
                messageEl.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>