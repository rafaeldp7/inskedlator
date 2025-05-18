<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';
require_once '../Models/ScheduleModel.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$model = new ScheduleModel($conn);
$schedules = $model->getSchedulesUser($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Schedule Requests</title>
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center mb-4">Your Schedule Requests</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Shift</th>
                        <th>Day</th>

                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= htmlspecialchars($schedule['shift']) ?></td>
                                <td><?= htmlspecialchars($schedule['day']) ?></td>

                                <td>
                                    <span class="badge bg-<?= $schedule['status'] === 'Approved' ? 'success' : ($schedule['status'] === 'Rejected' ? 'danger' : 'warning') ?>">
                                        <?= htmlspecialchars($schedule['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($schedule['status'] === 'Pending'): ?>
                                        <form method="POST" action="./Process/cancel_schedule.php" class="d-inline">
                                            <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule['id']) ?>">
                                            <button type="submit" name="cancel" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>No Action</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No schedules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

<style>
    .container {
        /* max-height: 90vh; */
        overflow-y: auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .table thead {
            font-size: 12px;
        }
        .table td, .table th {
            padding: 6px;
            font-size: 12px;
        }
    }
</style>
</html>
