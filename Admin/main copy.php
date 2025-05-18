<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../User/Models/ScheduleModel.php';
require_once '../User/Models/UserModel.php';
require_once 'AdminModel.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$model = new ScheduleModel($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = (int) $_POST['schedule_id'];
    if (isset($_POST['approve'])) {
        $model->updateScheduleStatus($schedule_id, 'Approved');
    } elseif (isset($_POST['reject'])) {
        $model->updateScheduleStatus($schedule_id, 'Rejected');
    } elseif (isset($_POST['delete'])) {
        $model->deleteSchedule($schedule_id);
    }

    $redirectParams = array_filter([
        'sort_by' => $_GET['sort_by'] ?? null,
        'order' => $_GET['order'] ?? null,
        'status' => $_GET['status'] ?? null,
        'page' => $_GET['page'] ?? null
    ]);
    header("Location: main.php?" . http_build_query($redirectParams));
    exit();
}

$page = $_GET['page'] ?? 'schedule';
$status = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort_by'] ?? 'shift';
$order = $_GET['order'] ?? 'asc';

$schedules = $model->getSchedules($sort_by, $order, $status);
$columns = [
    'shift' => 'Shift',
    'day' => 'Day',
    'time' => 'Time',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Inskedlator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>


    <div class="d-flex" id="main-wrapper">
        <div id="sidebar" class="position-fixed">
        <?php include 'sidebar.php'; ?>
        </div>

        <div id="right" class="main-content-area">


            <?php if ($page === 'schedule'): ?>
                <form method="get" class="mb-3">
                    <input type="hidden" name="page" value="schedule">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="sort_by" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($columns as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $sort_by === $key ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="order" class="form-select" onchange="this.form.submit()">
                                <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Ascending</option>
                                <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Descending</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="schedule-container">
                    <h2>Schedules</h2>
                    <table class="table table-striped table-hover">
                        <thead class=" ">
                            <tr>
                                <th>Name</th>
                                <?php foreach ($columns as $col => $label): ?>
                                    <?php
                                    $newOrder = ($sort_by === $col && $order === 'asc') ? 'desc' : 'asc';
                                    $link = "main.php?page=schedule&sort_by={$col}&order={$newOrder}&status=" . urlencode($status);
                                    ?>
                                    <th>
                                        <a href="<?= htmlspecialchars($link) ?>">
                                            <?= htmlspecialchars($label) ?>
                                            <?php if ($sort_by === $col): ?>
                                                <?= $order === 'asc' ? '↑' : '↓' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                <?php endforeach; ?>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="<?= count($columns) + 3 ?>" class="text-center">No schedules found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $sch): ?>
                                    <tr data-status="<?= htmlspecialchars($sch['status']) ?>">
                                        <td><?= htmlspecialchars($sch['user_name']) ?></td>
                                        <td><?= htmlspecialchars($sch['shift']) ?></td>
                                        <td><?= htmlspecialchars($sch['day']) ?></td>
                                        <td><?= htmlspecialchars($sch['time']) ?></td>
                                        <td><?= htmlspecialchars($sch['status']) ?></td>
                                        <td>
                                            <?php if ($sch['status'] !== 'Approved'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                                    <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                                <button type="submit" name="reject" class="btn btn-warning btn-sm">Reject</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div id="main-content">
                    <?php
                    $validPages = [
                        'announcement' => './announcement.php',
                        'chat' => './chat.php',
                        'calendar' => './calendar.php',
                        'addUser' => './addUser.php',
                        'profile' => './profile.php',
                        'users' => './users.php'
                    ];

                    if (array_key_exists($page, $validPages)) {
                        include $validPages[$page];
                    } else {
                        echo "<p class='text-danger'>Invalid page specified.</p>";
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>