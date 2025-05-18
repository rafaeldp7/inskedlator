<?php
require_once '../config.php';
require_once '../User/Models/ScheduleModel.php';


$sort_by = $_GET['sort_by'] ?? 'subject';
$order = $_GET['order'] ?? 'asc';
$status = $_GET['status'] ?? 'all';

$model = new ScheduleModel($conn);
$schedules = $model->getSchedules($sort_by, $order, $status);


$columns = [
    'subject' => 'Subject',
    'section' => 'Section',
    'day' => 'Day',
    'time' => 'Time',
];
?>

<head>
    <link rel="stylesheet" href="style.css">
</head>
<?php include './header.php'; ?>

<div class="d-flex" id="main-wrapper">
    <?php include './sidebar.php'; ?>
    <div class="schedule-container">
        <h2>Schedules</h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>

                    <?php foreach ($columns as $col => $label): ?>
                        <?php
                        $newOrder = ($sort_by === $col && $order === 'asc') ? 'desc' : 'asc';
                        $link = "?sort_by={$col}&order={$newOrder}&status=" . urlencode($status);
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
                        <td colspan="<?= count($columns) + 3 ?>" class="text-center">
                            No schedules found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $sch): ?>
                        <tr data-status="<?= htmlspecialchars($sch['status']) ?>">
                            <td><?= htmlspecialchars($sch['user_name']) ?></td>
                            <td><?= htmlspecialchars($sch['subject']) ?></td>
                            <td><?= htmlspecialchars($sch['section']) ?></td>
                            <td><?= htmlspecialchars($sch['day']) ?></td>
                            <td><?= htmlspecialchars($sch['time']) ?></td>
                            <td><?= htmlspecialchars($sch['status']) ?></td>
                            <td>
                                <?php if ($sch['status'] !== 'Approved'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                        <button type="submit" name="approve" class="approve-btn btn btn-success btn-sm">
                                            Approve
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                    <button type="submit" name="reject" class="reject-btn btn btn-warning btn-sm">
                                        Reject
                                    </button>
                                </form>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="schedule_id" value="<?= (int) $sch['id'] ?>">
                                    <button type="submit" name="delete" class="delete-btn btn btn-danger btn-sm"
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
</div>