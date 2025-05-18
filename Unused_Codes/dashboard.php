<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';  // Adjust path if needed
require_once '../Models/scheduleModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['full_name'] ?? 'Guest';
$loggedInUserID = $_SESSION['user_id'] ?? null;
$model = new ScheduleModel($conn);

$schedules = [];
try {
    $schedules = $model->getSchedulesUser($loggedInUserID);
} catch (Exception $e) {
    die("Error fetching schedules: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
body {
    background-color: #fff;

    width: 100%;
    /* max-width: 1200px; */
    /* margin: 0 auto; */
    /* overflow-y: scroll; */
    /* height: 1000vh; */
}

.content {
    width: 100%;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

h1, h2 {
    text-align: left;
    color: #333;
    font-size: 2rem; /* Increase heading size */
}

.table {
    width: 100%;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.table th, .table td {
    padding: 10px;
    border: 1px solid #dee2e6;
    text-align: center;
    font-size: 1rem; /* Adjust text size for better readability */
}

.table thead {
    background-color: #007bff;
    color: white;
}

.table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: #e9ecef;
    transition: background-color 0.3s ease;
}

    </style>
</head>

<body>

    <div class="content">
        <h1>Welcome, <?= htmlspecialchars($loggedInUser) ?></h1>
        <h2>Schedule List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?= htmlspecialchars($schedule['subject']) ?></td>
                        <td><?= htmlspecialchars($schedule['section']) ?></td>
                        <td><?= htmlspecialchars($schedule['day']) ?></td>
                        <td><?= htmlspecialchars($schedule['time']) ?></td>
                        <td><?= htmlspecialchars($schedule['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
