<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/INSKEDLATOR/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/INSKEDLATOR/User/Models/UserModel.php';

$userModel = new User($conn); // $conn is from config.php
$users = $userModel->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="users-container">
        <h2>All Registered Users</h2>

        <div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search by any field...">
</div>
        <?php if (count($users) === 0): ?>
            <div class="alert alert-info">No users found.</div>
        <?php else: ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Birthday</th>
                        <th>User ID</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['lastname']) ?></td>
                            <td><?= htmlspecialchars($user['firstname']) ?></td>
                            <td><?= htmlspecialchars($user['middlename']) ?></td>
                            <td><?= htmlspecialchars($user['birthday']) ?></td>
                            <td><?= htmlspecialchars($user['userID']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</html>
<style>
body {
    background-color: #f5f5f5;
    font-family: 'Poppins', sans-serif;
}

.container.users-container {
    max-width: 1000px;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 10px;
    padding: 30px 40px;
    box-shadow: 0 5px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #ddd;
}

h2 {
    font-weight: 600;
    margin-bottom: 25px;
}

.table {
    font-size: 14px;
}

.table thead {
    background-color: #f8f9fa;
}

.table th,
.table td {
    vertical-align: middle;
    text-align: center;
    padding: 10px;
}

.alert {
    font-size: 14px;
    border-radius: 8px;
}
</style>
